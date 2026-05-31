@include('partials.theme-head')
@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
body { font-family: 'Inter', sans-serif; }
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #EE6D3C; border-radius: 10px; }
.no-scrollbar::-webkit-scrollbar { display: none; }
[x-cloak] { display: none !important; }

.report-picker-input {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: textfield;
}

.report-picker-input::-webkit-calendar-picker-indicator,
.report-picker-input::-webkit-inner-spin-button,
.report-picker-input::-webkit-clear-button {
    opacity: 0;
    display: none;
}

#toast {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 9999;
    transform: translateY(120%);
    opacity: 0;
    transition: transform 0.35s cubic-bezier(.4,0,.2,1), opacity 0.35s;
    pointer-events: none;
}
#toast.show {
    transform: translateY(0);
    opacity: 1;
    pointer-events: auto;
}

#export-modal {
    position: fixed;
    inset: 0;
    z-index: 9998;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,0.45);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.25s;
}
#export-modal.open {
    opacity: 1;
    pointer-events: auto;
}
#export-modal .modal-box {
    background: var(--admin-card-bg);
    border: 1px solid var(--admin-border);
    border-radius: 1.25rem;
    padding: 2rem 2.25rem;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.18);
    transform: scale(0.93);
    transition: transform 0.25s;
}
#export-modal.open .modal-box {
    transform: scale(1);
}

@media print {
    body * { visibility: hidden; -webkit-print-color-adjust: exact; }
    #printable-invoice, #printable-invoice * { visibility: visible; }
    #printable-invoice { position: absolute; left: 0; top: 0; width: 100%; padding: 20px; display: block !important; }
    .no-print { display: none !important; }
}
</style>

<title>FastBite | Reports</title>

@php
$reservationsJson = $reservations->map(fn($r) => [
    'id'    => $r->id,
    'name'  => $r->full_name,
    'date'  => $r->date,
    'time'  => $r->time,
    'phone' => $r->phone_number,
    'table' => $r->table_id,
]);

$ordersJson = $orders->map(fn($o) => [
    'id'        => $o->id,
    'status'    => $o->status,
    'total'     => (float)($o->total_amount ?? 0),
    'date'      => \Carbon\Carbon::parse($o->created_at)->format('d M Y'),
    'time'      => \Carbon\Carbon::parse($o->created_at)->format('h:i A'),
    'items'     => $o->items && $o->items->count()
                    ? $o->items->map(fn($i) => ($i->name ?? $i->product?->name ?? 'Unknown') . ' ×' . $i->quantity)->join(', ')
                    : '',
    'itemCount' => $o->items ? $o->items->count() : 0,
]);

$productsJson = $products->map(fn($p) => [
    'id'          => $p->id,
    'name'        => $p->name,
    'description' => $p->description ?? '',
    'category'    => $p->category?->name ?? 'Uncategorized',
    'price'       => (float)$p->price,
    'cost'        => (float)($p->cost ?? 0),
    'qty'         => (int)($p->qty ?? 0),
]);

$categoriesJson = $categories->map(fn($c) => [
    'id'         => $c->id,
    'name'       => $c->name,
    'count'      => $c->products_count,
    'created_at' => \Carbon\Carbon::parse($c->created_at)->format('d M Y'),
]);

$salesJson = $completedOrders->sortByDesc('created_at')->map(fn($s) => [
    'id'      => $s->id,
    'date'    => \Carbon\Carbon::parse($s->created_at)->format('d M Y'),
    'time'    => \Carbon\Carbon::parse($s->created_at)->format('h:i A'),
    'dateKey' => \Carbon\Carbon::parse($s->created_at)->toDateString(),
    'total'   => (float)($s->total_amount ?? 0),
])->values();
@endphp

<div class="bg-[var(--admin-bg-primary)] min-h-screen text-[var(--admin-text-primary)]" x-data="{
    activeTab: '{{ $activeTab }}',
    reportMode: '{{ $filter['mode'] }}',

    /* ── Data ── */
    reservations: {{ Js::from($reservationsJson) }},
    allOrders:    {{ Js::from($ordersJson) }},
    allProducts:  {{ Js::from($productsJson) }},
    allCategories:{{ Js::from($categoriesJson) }},
    allSales:     {{ Js::from($salesJson) }},

    /* ── Reservations ── */
    resSearch: '', selectedTable: 'All',
    resPage: 1, resPerPage: 10,
    get filteredReservations() {
        return this.reservations.filter(r => {
            let s = !this.resSearch || (r.name + ' ' + r.phone + ' Table ' + r.table).toLowerCase().includes(this.resSearch.toLowerCase());
            let t = this.selectedTable === 'All' || this.selectedTable == r.table;
            return s && t;
        });
    },
    get pagedReservations() {
        const s = (this.resPage - 1) * this.resPerPage;
        return this.filteredReservations.slice(s, s + this.resPerPage);
    },
    get resTotalPages() { return Math.max(1, Math.ceil(this.filteredReservations.length / this.resPerPage)); },
    get resPageNums()   { return Array.from({ length: this.resTotalPages }, (_, i) => i + 1); },

    /* ── Orders ── */
    orderSearch: '', selectedStatus: 'All',
    orderPage: 1, orderPerPage: 10,
    get filteredOrders() {
        return this.allOrders.filter(o => {
            let s = !this.orderSearch || ('#' + o.id + ' ' + o.status + ' ' + o.items).toLowerCase().includes(this.orderSearch.toLowerCase());
            let st = this.selectedStatus === 'All' || this.selectedStatus === o.status;
            return s && st;
        });
    },
    get pagedOrders() {
        const s = (this.orderPage - 1) * this.orderPerPage;
        return this.filteredOrders.slice(s, s + this.orderPerPage);
    },
    get orderTotalPages() { return Math.max(1, Math.ceil(this.filteredOrders.length / this.orderPerPage)); },
    get orderPageNums()   { return Array.from({ length: this.orderTotalPages }, (_, i) => i + 1); },

    /* ── Products ── */
    productSearch: '', selectedCategory: 'All',
    productPage: 1, productPerPage: 10,
    get filteredProducts() {
        return this.allProducts.filter(p => {
            let s = !this.productSearch || (p.name + ' ' + p.category).toLowerCase().includes(this.productSearch.toLowerCase());
            let c = this.selectedCategory === 'All' || this.selectedCategory === p.category;
            return s && c;
        });
    },
    get pagedProducts() {
        const s = (this.productPage - 1) * this.productPerPage;
        return this.filteredProducts.slice(s, s + this.productPerPage);
    },
    get productTotalPages() { return Math.max(1, Math.ceil(this.filteredProducts.length / this.productPerPage)); },
    get productPageNums()   { return Array.from({ length: this.productTotalPages }, (_, i) => i + 1); },

    /* ── Categories ── */
    categorySearch: '',
    catPage: 1, catPerPage: 10,
    get filteredCategories() {
        return this.allCategories.filter(c => !this.categorySearch || c.name.toLowerCase().includes(this.categorySearch.toLowerCase()));
    },
    get pagedCategories() {
        const s = (this.catPage - 1) * this.catPerPage;
        return this.filteredCategories.slice(s, s + this.catPerPage);
    },
    get catTotalPages() { return Math.max(1, Math.ceil(this.filteredCategories.length / this.catPerPage)); },
    get catPageNums()   { return Array.from({ length: this.catTotalPages }, (_, i) => i + 1); },

    /* ── Sales ── */
    saleSearch: '', selectedSaleDate: 'All',
    salePage: 1, salePerPage: 10,
    get filteredSales() {
        return this.allSales.filter(s => {
            let m = !this.saleSearch || ('#' + s.id).toLowerCase().includes(this.saleSearch.toLowerCase());
            let d = this.selectedSaleDate === 'All' || this.selectedSaleDate === s.dateKey;
            return m && d;
        });
    },
    get pagedSales() {
        const s = (this.salePage - 1) * this.salePerPage;
        return this.filteredSales.slice(s, s + this.salePerPage);
    },
    get saleTotalPages() { return Math.max(1, Math.ceil(this.filteredSales.length / this.salePerPage)); },
    get salePageNums()   { return Array.from({ length: this.saleTotalPages }, (_, i) => i + 1); },

    /* ── Export / Toast ── */
    exportModal: false, exportFormat: '', exportLabel: '', exportColorClass: '',
    openExport(format) {
        const map = {
            excel: { label: 'Excel (.xlsx)', color: 'bg-emerald-600' },
            pdf:   { label: 'PDF document',  color: 'bg-red-600'     },
            print: { label: 'Print preview', color: 'bg-gray-900'    },
        };
        this.exportFormat     = format;
        this.exportLabel      = map[format].label;
        this.exportColorClass = map[format].color;
        this.exportModal      = true;
    },
    confirmExport() {
        this.exportModal = false;
        if (this.exportFormat === 'print') {
            this.$nextTick(() => window.print());
        } else {
            const params = new URLSearchParams({
                type: this.activeTab,
                tab: this.activeTab,
                report_mode: this.reportMode,
            });

            if (this.reportMode === 'day') {
                params.set('report_date', '{{ $filter['report_date'] }}');
            } else {
                params.set('report_month', '{{ $filter['report_month'] }}');
            }

            window.location.href = '{{ url('/reports/export') }}/' + this.exportFormat + '?' + params.toString();
        }
        this.showToast(this.exportLabel);
    },
    toastMsg: '',
    showToast(label) {
        this.toastMsg = label + ' export started';
        const el = document.getElementById('toast');
        el.classList.add('show');
        setTimeout(() => el.classList.remove('show'), 3200);
    },

    /* ── Reset pages on filter change ── */
    init() {
        this.$watch('resSearch',        () => this.resPage      = 1);
        this.$watch('selectedTable',    () => this.resPage      = 1);
        this.$watch('orderSearch',      () => this.orderPage    = 1);
        this.$watch('selectedStatus',   () => this.orderPage    = 1);
        this.$watch('productSearch',    () => this.productPage  = 1);
        this.$watch('selectedCategory', () => this.productPage  = 1);
        this.$watch('categorySearch',   () => this.catPage      = 1);
        this.$watch('saleSearch',       () => this.salePage     = 1);
        this.$watch('selectedSaleDate', () => this.salePage     = 1);
    }
}">

    <div class="flex flex-col md:flex-row md:h-screen md:p-4 md:gap-6 md:overflow-hidden relative">

        @include('components.asidebar')

        <main class="flex-1 overflow-y-auto px-3 pb-4 md:px-0 md:pr-2 custom-scrollbar">
            <div class="bg-[var(--admin-card-bg)] rounded-lg shadow-md border border-[var(--admin-border)] p-6 md:p-8 mt-3 md:mt-0 mb-8">

                <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-8">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <div class="w-9 h-9 rounded-xl bg-[var(--admin-bg-primary)] flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#EE6D3C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-[var(--admin-text-primary)]">Reports</h2>
                            <span class="text-xs font-bold bg-[var(--admin-bg-primary)] text-[#EE6D3C] px-3 py-1 rounded-full border border-[#EE6D3C]/20"
                                x-text="activeTab.charAt(0).toUpperCase() + activeTab.slice(1)"></span>
                        </div>
                        <p class="text-sm text-[var(--admin-text-secondary)] ml-12">View and export your restaurant data</p>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap no-print">
                        <button @click="openExport('excel')"
                            class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition-all duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Excel
                        </button>

                        <!-- for create or export pdf file -->
                        <button @click="openExport('pdf')"
                            class="flex items-center gap-2 bg-red-600 hover:bg-red-700 active:scale-95 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition-all duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            PDF
                        </button>

                        
                        <button @click="openExport('print')"
                            class="flex items-center gap-2 bg-gray-900 hover:bg-gray-700 active:scale-95 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition-all duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Print
                        </button>
                    </div>
                </div>

                <div class="mb-8 flex flex-col xl:flex-row xl:items-center justify-between rounded-xl border border-orange-200 dark:border-orange-500/20 bg-orange-50/30 dark:bg-orange-500/5 px-8 py-7 shadow-sm dark:shadow-none">
                    
                    <div>
                        <p class="mb-1 text-[11px] font-black uppercase tracking-[0.2em] text-[#EE6D3C]">Report Period</p>
                        <h3 class="text-3xl font-black text-[var(--admin-text-primary)] leading-tight">{{ $filter['label'] ?? '24 April 2026' }}</h3>
                        <p class="mt-1 text-sm font-medium text-[var(--admin-text-secondary)]">{{ $filter['description'] ?? 'Showing all records for 24 April 2026.' }}</p>
                    </div>

                    <form method="GET" action="{{ route('report.index') }}" class="mt-8 xl:mt-0 flex flex-wrap items-end gap-3">
                        <input type="hidden" name="tab" :value="activeTab">
                        <input type="hidden" name="report_mode" :value="reportMode">

                        <div class="flex items-center gap-4 mr-2">
                            <span class="text-[10px] font-black uppercase tracking-[0.15em] text-[var(--admin-text-secondary)]">Date Mode</span>
                            <div class="flex rounded-[1.25rem] border border-orange-200 dark:border-orange-500/20 bg-[var(--admin-bg-primary)] dark:bg-[var(--admin-bg-primary)] p-1">
                                <button type="button" @click="reportMode = 'day'"
                                    :class="reportMode === 'day' ? 'bg-[#DE7351] text-white shadow-sm' : 'text-[var(--admin-text-secondary)] hover:text-[#DE7351]'"
                                    class="rounded-xl px-6 py-2.5 text-sm font-bold transition-all">
                                    Day
                                </button>
                                <button type="button" @click="reportMode = 'month'"
                                    :class="reportMode === 'month' ? 'bg-[#DE7351] text-white shadow-sm' : 'text-[var(--admin-text-secondary)] hover:text-[#DE7351]'"
                                    class="rounded-xl px-6 py-2.5 text-sm font-bold transition-all">
                                    Month
                                </button>
                            </div>
                        </div>

                        <div class="relative flex flex-col items-center ml-1" x-show="reportMode === 'day'" x-cloak>
                            <label for="report_date_input" class="absolute -top-6 left-1 text-[10px] font-black uppercase tracking-[0.15em] text-[var(--admin-text-secondary)] cursor-pointer">Pick Day</label>
                            <div class="relative flex h-[52px] w-[100px] items-center justify-center rounded-[1.25rem] border border-orange-200 dark:border-orange-500/20 bg-[var(--admin-bg-primary)] transition-all hover:border-[#DE7351] hover:shadow-sm cursor-pointer"
                                 @click="($refs.dayPicker.showPicker ? $refs.dayPicker.showPicker() : $refs.dayPicker.click())">
                                <input id="report_date_input" x-ref="dayPicker" type="date" name="report_date" 
                                    value="{{ $filter['report_date'] ?? '' }}" 
                                    @change="$el.form.submit()"
                                    class="absolute inset-0 z-0 h-full w-full opacity-0 pointer-events-none">
                                <button type="button"
                                    class="flex h-10 w-[80px] items-center justify-center rounded-[0.85rem] bg-[#DE7351] text-white shadow-sm pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="relative flex flex-col items-center ml-1" x-show="reportMode === 'month'" x-cloak>
                            <label for="report_month_input" class="absolute -top-6 left-1 text-[10px] font-black uppercase tracking-[0.15em] text-[var(--admin-text-secondary)] cursor-pointer">Pick Month</label>
                            <div class="relative flex h-[52px] w-[100px] items-center justify-center rounded-[1.25rem] border border-orange-200 dark:border-orange-500/20 bg-[var(--admin-bg-primary)] transition-all hover:border-[#DE7351] hover:shadow-sm cursor-pointer"
                                 @click="($refs.monthPicker.showPicker ? $refs.monthPicker.showPicker() : $refs.monthPicker.click())">
                                <input id="report_month_input" x-ref="monthPicker" type="month" name="report_month" 
                                    value="{{ $filter['report_month'] ?? '' }}" 
                                    @change="$el.form.submit()"
                                    class="absolute inset-0 z-0 h-full w-full opacity-0 pointer-events-none">
                                <button type="button"
                                    class="flex h-10 w-[80px] items-center justify-center rounded-[0.85rem] bg-[#DE7351] text-white shadow-sm pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="flex h-[52px] items-center justify-center gap-2 rounded-[1.25rem] bg-[#DE7351] px-6 text-sm font-bold text-white transition hover:bg-[#c96645] shadow-sm ml-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Submit Filter
                        </button>

                        <a href="{{ route('report.index') }}" class="flex h-[52px] items-center justify-center rounded-[1.25rem] border border-[var(--admin-border)] bg-[var(--admin-bg-primary)] px-6 text-sm font-bold text-[var(--admin-text-secondary)] transition hover:bg-[var(--admin-card-bg)] hover:text-[var(--admin-text-primary)] shadow-sm ml-1">
                            Reset
                        </a>
                    </form>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-3 mb-8">
                    <div class="bg-orange-50 dark:bg-orange-500/10 rounded-2xl p-4 border border-orange-200 dark:border-orange-500/20 shadow-sm transition-all hover:shadow-md">
                        <p class="text-xs font-bold text-orange-600 dark:text-orange-400 uppercase tracking-wide mb-1">Reservations</p>
                        <p class="text-2xl font-black text-orange-600 dark:text-orange-500">{{ $reservations->count() }}</p>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-500/10 rounded-2xl p-4 border border-blue-200 dark:border-blue-500/20 shadow-sm transition-all hover:shadow-md">
                        <p class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-1">Orders</p>
                        <p class="text-2xl font-black text-blue-600 dark:text-blue-500">{{ $orders->count() }}</p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-500/10 rounded-2xl p-4 border border-green-200 dark:border-green-500/20 shadow-sm transition-all hover:shadow-md">
                        <p class="text-xs font-bold text-green-600 dark:text-green-400 uppercase tracking-wide mb-1">Products</p>
                        <p class="text-2xl font-black text-green-600 dark:text-green-500">{{ $products->count() }}</p>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-500/10 rounded-2xl p-4 border border-purple-200 dark:border-purple-500/20 shadow-sm transition-all hover:shadow-md">
                        <p class="text-xs font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wide mb-1">Categories</p>
                        <p class="text-2xl font-black text-purple-600 dark:text-purple-500">{{ $categories->count() }}</p>
                    </div>
                    <div class="bg-amber-50 dark:bg-amber-500/10 rounded-2xl p-4 border border-amber-200 dark:border-amber-500/20 cursor-pointer shadow-sm transition-all hover:shadow-md hover:border-amber-400"
                         @click="activeTab = 'sales'">
                        <p class="text-xs font-bold text-amber-600 dark:text-amber-500 uppercase tracking-wide mb-1">Today Sales</p>
                        <p class="text-2xl font-black text-amber-600 dark:text-amber-500">${{ number_format($dailySalesTotal, 2) }}</p>
                        <p class="text-[10px] text-amber-500/70 dark:text-amber-400 mt-1">{{ now()->format('d M Y') }}</p>
                    </div>
                    <div class="bg-rose-50 dark:bg-rose-500/10 rounded-2xl p-4 border border-rose-200 dark:border-rose-500/20 cursor-pointer shadow-sm transition-all hover:shadow-md hover:border-rose-400"
                         @click="activeTab = 'sales'">
                        <p class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wide mb-1">Month Sales</p>
                        <p class="text-2xl font-black text-rose-600 dark:text-rose-500">${{ number_format($monthlySalesTotal, 2) }}</p>
                        <p class="text-[10px] text-rose-500/70 dark:text-rose-400 mt-1">{{ now()->format('M Y') }}</p>
                    </div>
                </div>

                <div class="flex gap-1 bg-[var(--admin-bg-primary)] border border-[var(--admin-border)] rounded-xl p-1 mb-6 no-print overflow-x-auto no-scrollbar">
                    @foreach(['reservations','orders','products','categories','sales'] as $tab)
                        <button @click="activeTab = '{{ $tab }}'"
                            :class="activeTab === '{{ $tab }}' ? 'bg-[var(--admin-card-bg)] text-[#EE6D3C] shadow font-bold' : 'text-[var(--admin-text-secondary)] hover:text-[#EE6D3C]'"
                            class="flex-1 min-w-[110px] py-2 px-4 rounded-lg text-sm transition capitalize whitespace-nowrap flex items-center justify-center gap-1.5">
                            @if($tab === 'sales')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            @endif
                            {{ ucfirst($tab) }}
                        </button>
                    @endforeach
                </div>

                <div x-show="activeTab === 'reservations'" x-transition>
                    <div class="flex flex-wrap gap-3 mb-4">
                        <div class="relative w-full md:w-72">
                            <input type="text" placeholder="Search reservation..." x-model="resSearch"
                                class="w-full pl-4 pr-10 py-2.5 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm bg-[var(--admin-bg-primary)] text-[var(--admin-text-primary)] placeholder-[var(--admin-text-secondary)]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute right-3 top-3 text-[var(--admin-text-secondary)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <select x-model="selectedTable" class="py-2.5 px-4 border border-[var(--admin-border)] rounded-xl text-sm bg-[var(--admin-bg-primary)] text-[var(--admin-text-primary)] outline-none focus:ring-2 focus:ring-orange-200">
                            <option value="All">All Tables</option>
                            @foreach($reservations->pluck('table_id')->unique()->sort() as $tid)
                                <option value="{{ $tid }}">Table {{ $tid }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full overflow-x-auto rounded-xl border border-[var(--admin-border)]">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-[var(--admin-bg-primary)]">
                                <tr class="border-b border-[var(--admin-border)] text-[var(--admin-text-secondary)] uppercase text-xs">
                                    <th class="px-4 py-3">ID</th>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3 hidden md:table-cell">Date & Time</th>
                                    <th class="px-4 py-3 hidden md:table-cell">Phone</th>
                                    <th class="px-4 py-3 hidden lg:table-cell">Table</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-if="filteredReservations.length === 0">
                                    <tr><td colspan="5" class="p-10 text-center text-gray-400 italic text-sm">No reservation data found</td></tr>
                                </template>
                                <template x-for="res in pagedReservations" :key="res.id">
                                    <tr class="hover:bg-orange-50 dark:hover:bg-orange-50/10 transition">
                                        <td class="px-4 py-3 text-[var(--admin-text-secondary)] text-xs font-mono" x-text="'#' + res.id"></td>
                                        <td class="px-4 py-3 font-medium text-[var(--admin-text-primary)]" x-text="res.name"></td>
                                        <td class="px-4 py-3 hidden md:table-cell">
                                            <span class="text-[var(--admin-text-primary)]" x-text="res.date"></span>
                                            <span class="ml-1 text-xs text-[var(--admin-text-secondary)]" x-text="res.time"></span>
                                        </td>
                                        <td class="px-4 py-3 hidden md:table-cell text-[var(--admin-text-secondary)] text-sm" x-text="res.phone"></td>
                                        <td class="px-4 py-3 hidden lg:table-cell">
                                            <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-bold" x-text="'Table ' + res.table"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between mt-4 flex-wrap gap-2">
                        <p class="text-xs text-gray-400"
                            x-text="filteredReservations.length
                                ? 'Showing ' + ((resPage-1)*resPerPage+1) + '–' + Math.min(resPage*resPerPage, filteredReservations.length) + ' of ' + filteredReservations.length + ' entries'
                                : ''"></p>
                        <div x-show="resTotalPages > 1" class="flex items-center gap-1.5 flex-wrap">
                            <button @click="resPage--" :disabled="resPage === 1"
                                class="w-9 h-9 rounded-lg bg-[var(--admin-card-bg)] border border-[var(--admin-border)] hover:bg-orange-50 dark:hover:bg-orange-50/10 transition font-bold text-[var(--admin-text-primary)] flex items-center justify-center">‹</button>
                            <template x-for="p in resPageNums" :key="p">
                                <button @click="resPage = p"
                                    :class="resPage === p ? 'bg-[#EE6D3C] text-white border-[#EE6D3C] shadow-md' : 'bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)] border-[var(--admin-border)] hover:bg-orange-50/10'"
                                    class="w-9 h-9 rounded-lg border font-bold transition text-sm"><span x-text="p"></span></button>
                            </template>
                            <button @click="resPage++" :disabled="resPage === resTotalPages"
                                class="w-9 h-9 rounded-lg bg-[var(--admin-card-bg)] border border-[var(--admin-border)] hover:bg-orange-50 dark:hover:bg-orange-50/10 transition font-bold text-[var(--admin-text-primary)] flex items-center justify-center">›</button>
                        </div>
                    </div>
                </div>

                <div x-show="activeTab === 'orders'" x-transition>
                    <div class="flex flex-wrap gap-3 mb-4">
                        <div class="relative w-full md:w-72">
                            <input type="text" placeholder="Search order..." x-model="orderSearch"
                                class="w-full pl-4 pr-10 py-2.5 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm bg-[var(--admin-bg-primary)] text-[var(--admin-text-primary)] placeholder-[var(--admin-text-secondary)]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute right-3 top-3 text-[var(--admin-text-secondary)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <select x-model="selectedStatus" class="py-2.5 px-4 border border-[var(--admin-border)] rounded-xl text-sm bg-[var(--admin-bg-primary)] text-[var(--admin-text-primary)] outline-none focus:ring-2 focus:ring-orange-200">
                            <option value="All">All Statuses</option>
                            @foreach($orders->pluck('status')->unique() as $status)
                                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full overflow-x-auto rounded-xl border border-[var(--admin-border)]">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-[var(--admin-bg-primary)]">
                                <tr class="border-b border-[var(--admin-border)] text-[var(--admin-text-secondary)] uppercase text-xs">
                                    <th class="px-4 py-3">ID</th>
                                    <th class="px-4 py-3">Items</th>
                                    <th class="px-4 py-3 hidden md:table-cell">Date</th>
                                    <th class="px-4 py-3 hidden md:table-cell">Total</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-if="filteredOrders.length === 0">
                                    <tr><td colspan="5" class="p-10 text-center text-gray-400 italic text-sm">No order data found</td></tr>
                                </template>
                                <template x-for="order in pagedOrders" :key="order.id">
                                    <tr class="hover:bg-orange-50 dark:hover:bg-orange-50/40 transition">
                                        <td class="px-4 py-3 text-[var(--admin-text-secondary)] text-xs font-mono" x-text="'#' + order.id"></td>
                                        <td class="px-4 py-3">
                                            <template x-if="order.items">
                                                <div>
                                                    <div class="text-sm font-medium text-[var(--admin-text-primary)]" x-text="order.items"></div>
                                                    <div class="text-xs text-[var(--admin-text-secondary)] mt-0.5" x-text="order.itemCount + (order.itemCount === 1 ? ' item' : ' items')"></div>
                                                </div>
                                            </template>
                                            <template x-if="!order.items">
                                                <span class="text-[var(--admin-text-secondary)] text-xs italic">No items</span>
                                            </template>
                                        </td>
                                        <td class="px-4 py-3 hidden md:table-cell text-[var(--admin-text-secondary)] text-sm">
                                            <span class="text-[var(--admin-text-primary)]" x-text="order.date"></span>
                                            <div class="text-xs text-[var(--admin-text-secondary)]" x-text="order.time"></div>
                                        </td>
                                        <td class="px-4 py-3 hidden md:table-cell font-semibold text-[var(--admin-text-primary)]"
                                            x-text="'$' + order.total.toFixed(2)"></td>
                                        <td class="px-4 py-3">
                                            <span class="px-3 py-1 rounded-full text-xs font-bold"
                                                :class="{
                                                    'bg-yellow-100 text-yellow-700': order.status === 'pending',
                                                    'bg-blue-100 text-blue-700':    order.status === 'confirmed',
                                                    'bg-green-100 text-green-700':  order.status === 'completed',
                                                    'bg-red-100 text-red-700':      order.status === 'cancelled',
                                                    'bg-[var(--admin-bg-primary)] text-[var(--admin-text-secondary)]': !['pending','confirmed','completed','cancelled'].includes(order.status)
                                                }"
                                                x-text="order.status.charAt(0).toUpperCase() + order.status.slice(1)"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between mt-4 flex-wrap gap-2">
                        <p class="text-xs text-gray-400"
                            x-text="filteredOrders.length
                                ? 'Showing ' + ((orderPage-1)*orderPerPage+1) + '–' + Math.min(orderPage*orderPerPage, filteredOrders.length) + ' of ' + filteredOrders.length + ' entries'
                                : ''"></p>
                        <div x-show="orderTotalPages > 1" class="flex items-center gap-1.5 flex-wrap">
                            <button @click="orderPage--" :disabled="orderPage === 1"
                                class="w-9 h-9 rounded-lg bg-[var(--admin-card-bg)] border border-[var(--admin-border)] hover:bg-orange-50 dark:hover:bg-orange-50/10 transition font-bold text-[var(--admin-text-primary)] flex items-center justify-center">‹</button>
                            <template x-for="p in orderPageNums" :key="p">
                                <button @click="orderPage = p"
                                    :class="orderPage === p ? 'bg-[#EE6D3C] text-white border-[#EE6D3C] shadow-md' : 'bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)] border border-[var(--admin-border)] hover:bg-orange-50/10'"
                                    class="w-9 h-9 rounded-lg border font-bold transition text-sm"><span x-text="p"></span></button>
                            </template>
                            <button @click="orderPage++" :disabled="orderPage === orderTotalPages"
                                class="w-9 h-9 rounded-lg bg-[var(--admin-card-bg)] border border-[var(--admin-border)] hover:bg-orange-50 dark:hover:bg-orange-50/10 transition font-bold text-[var(--admin-text-primary)] flex items-center justify-center">›</button>
                        </div>
                    </div>
                </div>

                <div x-show="activeTab === 'products'" x-transition>
                    <div class="flex flex-wrap gap-3 mb-4">
                        <div class="relative w-full md:w-72">
                            <input type="text" placeholder="Search product..." x-model="productSearch"
                                class="w-full pl-4 pr-10 py-2.5 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm bg-[var(--admin-bg-primary)] text-[var(--admin-text-primary)] placeholder-[var(--admin-text-secondary)]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute right-3 top-3 text-[var(--admin-text-secondary)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <select x-model="selectedCategory" class="py-2.5 px-4 border border-[var(--admin-border)] rounded-xl text-sm bg-[var(--admin-bg-primary)] text-[var(--admin-text-primary)] outline-none focus:ring-2 focus:ring-orange-200">
                            <option value="All">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full overflow-x-auto rounded-xl border border-[var(--admin-border)]">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-[var(--admin-bg-primary)]">
                                <tr class="border-b border-[var(--admin-border)] text-[var(--admin-text-secondary)] uppercase text-xs">
                                    <th class="px-4 py-3">ID</th>
                                    <th class="px-4 py-3">Product</th>
                                    <th class="px-4 py-3 hidden md:table-cell">Category</th>
                                    <th class="px-4 py-3 hidden md:table-cell">Price</th>
                                    <th class="px-4 py-3 hidden lg:table-cell">Cost</th>
                                    <th class="px-4 py-3 hidden lg:table-cell">Qty</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-if="filteredProducts.length === 0">
                                    <tr><td colspan="6" class="p-10 text-center text-gray-400 italic text-sm">No product data found</td></tr>
                                </template>
                                <template x-for="product in pagedProducts" :key="product.id">
                                    <tr class="hover:bg-orange-50 dark:hover:bg-orange-50/40 transition">
                                        <td class="px-4 py-3 text-[var(--admin-text-secondary)] text-xs font-mono" x-text="'#' + product.id"></td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-[var(--admin-text-primary)]" x-text="product.name"></div>
                                            <div x-show="product.description" class="text-xs text-[var(--admin-text-secondary)] mt-0.5 line-clamp-1" x-text="product.description"></div>
                                        </td>
                                        <td class="px-4 py-3 hidden md:table-cell">
                                            <span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs font-bold" x-text="product.category"></span>
                                        </td>
                                        <td class="px-4 py-3 hidden md:table-cell font-semibold text-[var(--admin-text-primary)]" x-text="'$' + product.price.toFixed(2)"></td>
                                        <td class="px-4 py-3 hidden lg:table-cell text-[var(--admin-text-secondary)]" x-text="'$' + product.cost.toFixed(2)"></td>
                                        <td class="px-4 py-3 hidden lg:table-cell">
                                            <span class="px-2 py-0.5 rounded-lg text-xs font-bold"
                                                :class="product.qty > 10 ? 'bg-green-100 text-green-700' : (product.qty > 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700')"
                                                x-text="product.qty"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between mt-4 flex-wrap gap-2">
                        <p class="text-xs text-gray-400"
                            x-text="filteredProducts.length
                                ? 'Showing ' + ((productPage-1)*productPerPage+1) + '–' + Math.min(productPage*productPerPage, filteredProducts.length) + ' of ' + filteredProducts.length + ' entries'
                                : ''"></p>
                        <div x-show="productTotalPages > 1" class="flex items-center gap-1.5 flex-wrap">
                            <button @click="productPage--" :disabled="productPage === 1"
                                class="w-9 h-9 rounded-lg bg-[var(--admin-card-bg)] border border-[var(--admin-border)] hover:bg-orange-50 dark:hover:bg-orange-50/10 transition font-bold text-[var(--admin-text-primary)] flex items-center justify-center">‹</button>
                            <template x-for="p in productPageNums" :key="p">
                                <button @click="productPage = p"
                                    :class="productPage === p ? 'bg-[#EE6D3C] text-white border-[#EE6D3C] shadow-md' : 'bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)] border border-[var(--admin-border)] hover:bg-orange-50/10'"
                                    class="w-9 h-9 rounded-lg border font-bold transition text-sm"><span x-text="p"></span></button>
                            </template>
                            <button @click="productPage++" :disabled="productPage === productTotalPages"
                                class="w-9 h-9 rounded-lg bg-[var(--admin-card-bg)] border border-[var(--admin-border)] hover:bg-orange-50 dark:hover:bg-orange-50/10 transition font-bold text-[var(--admin-text-primary)] flex items-center justify-center">›</button>
                        </div>
                    </div>
                </div>

                <div x-show="activeTab === 'categories'" x-transition>
                    <div class="flex flex-wrap gap-3 mb-4">
                        <div class="relative w-full md:w-72">
                            <input type="text" placeholder="Search category..." x-model="categorySearch"
                                class="w-full pl-4 pr-10 py-2.5 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm bg-[var(--admin-bg-primary)] text-[var(--admin-text-primary)] placeholder-[var(--admin-text-secondary)]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute right-3 top-3 text-[var(--admin-text-secondary)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="w-full overflow-x-auto rounded-xl border border-[var(--admin-border)]">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-[var(--admin-bg-primary)]">
                                <tr class="border-b border-[var(--admin-border)] text-[var(--admin-text-secondary)] uppercase text-xs">
                                    <th class="px-4 py-3">ID</th>
                                    <th class="px-4 py-3">Category Name</th>
                                    <th class="px-4 py-3 hidden md:table-cell">Total Products</th>
                                    <th class="px-4 py-3 hidden lg:table-cell">Created At</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-if="filteredCategories.length === 0">
                                    <tr><td colspan="4" class="p-10 text-center text-gray-400 italic text-sm">No category data found</td></tr>
                                </template>
                                <template x-for="cat in pagedCategories" :key="cat.id">
                                    <tr class="hover:bg-orange-50 dark:hover:bg-orange-50/10 transition">
                                         <td class="px-4 py-3 text-[var(--admin-text-secondary)] text-xs font-mono" x-text="'#' + cat.id"></td>
                                         <td class="px-4 py-3 font-medium text-[var(--admin-text-primary)]" x-text="cat.name"></td>
                                         <td class="px-4 py-3 hidden md:table-cell">
                                             <span class="bg-orange-100 dark:bg-orange-500/10 text-orange-600 dark:text-[#EE6D3C] px-3 py-1 rounded-full text-xs font-bold border border-orange-200 dark:border-[#EE6D3C]/20" x-text="cat.count + ' items'"></span>
                                         </td>
                                        <td class="px-4 py-3 hidden lg:table-cell text-gray-400 text-xs" x-text="cat.created_at"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between mt-4 flex-wrap gap-2">
                        <p class="text-xs text-gray-400"
                            x-text="filteredCategories.length
                                ? 'Showing ' + ((catPage-1)*catPerPage+1) + '–' + Math.min(catPage*catPerPage, filteredCategories.length) + ' of ' + filteredCategories.length + ' entries'
                                : ''"></p>
                        <div x-show="catTotalPages > 1" class="flex items-center gap-1.5 flex-wrap">
                            <button @click="catPage--" :disabled="catPage === 1"
                                class="w-9 h-9 rounded-lg bg-[var(--admin-card-bg)] border border-[var(--admin-border)] hover:bg-orange-50 dark:hover:bg-orange-50/10 transition font-bold text-[var(--admin-text-primary)] flex items-center justify-center">‹</button>
                            <template x-for="p in catPageNums" :key="p">
                                <button @click="catPage = p"
                                    :class="catPage === p ? 'bg-[#EE6D3C] text-white border-[#EE6D3C] shadow-md' : 'bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)] border border-[var(--admin-border)] hover:bg-orange-50/10'"
                                    class="w-9 h-9 rounded-lg border font-bold transition text-sm"><span x-text="p"></span></button>
                            </template>
                            <button @click="catPage++" :disabled="catPage === catTotalPages"
                                class="w-9 h-9 rounded-lg bg-[var(--admin-card-bg)] border border-[var(--admin-border)] hover:bg-orange-50 dark:hover:bg-orange-50/10 transition font-bold text-[var(--admin-text-primary)] flex items-center justify-center">›</button>
                        </div>
                    </div>
                </div>

                <div x-show="activeTab === 'sales'" x-transition>

                    <div class="grid grid-cols-3 gap-3 mb-6">
                        <div class="relative overflow-hidden rounded-2xl bg-orange-50 dark:bg-orange-500/10 border border-orange-200 dark:border-orange-500/20 p-4">
                            <div class="absolute -right-3 -top-3 w-20 h-20 rounded-full bg-[#EE6D3C]/10"></div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-orange-600 dark:text-[#EE6D3C] mb-1">Today</p>
                            <p class="text-2xl font-black text-[var(--admin-text-primary)]">${{ number_format($todayTotal, 2) }}</p>
                            <div class="flex items-center gap-1.5 mt-2">
                                <span class="inline-flex items-center gap-1 bg-orange-100 dark:bg-[#EE6D3C]/10 text-orange-600 dark:text-[#EE6D3C] text-[10px] font-bold px-2 py-0.5 rounded-full">
                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                    {{ $todayCount }} orders
                                </span>
                                <span class="text-[10px] text-[var(--admin-text-secondary)]">{{ now()->format('d M') }}</span>
                            </div>
                        </div>
                        <div class="relative overflow-hidden rounded-2xl bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 p-4">
                            <div class="absolute -right-3 -top-3 w-20 h-20 rounded-full bg-blue-500/10"></div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-blue-600 dark:text-blue-500 mb-1">{{ now()->format('M Y') }}</p>
                            <p class="text-2xl font-black text-[var(--admin-text-primary)]">${{ number_format($monthlySalesTotal, 2) }}</p>
                            <div class="flex items-center gap-1.5 mt-2">
                                <span class="inline-flex items-center gap-1 bg-blue-100 dark:bg-blue-500/10 text-blue-600 dark:text-blue-500 text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $monthlyCount }} orders</span>
                            </div>
                        </div>
                        <div class="relative overflow-hidden rounded-2xl bg-[var(--admin-text-primary)] border border-[var(--admin-border)] p-4 shadow-sm">
                            <div class="absolute -right-3 -top-3 w-20 h-20 rounded-full bg-white/5"></div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-[var(--admin-text-secondary)] mb-1">Total Sales</p>
                            <p class="text-2xl font-black text-[#EE6D3C]">${{ number_format($grandTotal, 2) }}</p>
                            <div class="flex items-center gap-1.5 mt-2">
                                <span class="inline-flex items-center gap-1 bg-white/10 text-[var(--admin-text-secondary)] text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $completedOrders->count() }} completed</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 mb-5">
                        <div class="relative flex-1 min-w-[180px] max-w-xs">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-[var(--admin-text-secondary)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" placeholder="Search order ID…" x-model="saleSearch"
                                class="w-full pl-9 pr-4 py-2 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-bg-primary)] text-[var(--admin-text-primary)] text-sm outline-none focus:ring-2 focus:ring-[#EE6D3C]/30 focus:border-[#EE6D3C]/50 transition placeholder-[var(--admin-text-secondary)]">
                        </div>
                        <select x-model="selectedSaleDate"
                            class="py-2 px-3 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-bg-primary)] text-[var(--admin-text-primary)] text-sm outline-none focus:ring-2 focus:ring-[#EE6D3C]/30 focus:border-[#EE6D3C]/50 transition">
                            <option value="All">All Dates</option>
                            @foreach($salesByDate->keys() as $dk)
                                <option value="{{ $dk }}">{{ \Carbon\Carbon::parse($dk)->format('d M Y') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full overflow-x-auto rounded-2xl border border-[var(--admin-border)] shadow-sm">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-[var(--admin-bg-primary)]">
                                <tr class="border-b border-[var(--admin-border)] text-[var(--admin-text-secondary)] uppercase text-xs">
                                    <th class="px-4 py-3">Order ID</th>
                                    <th class="px-4 py-3 hidden md:table-cell">Date & Time</th>
                                    <th class="px-4 py-3 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-if="filteredSales.length === 0">
                                    <tr><td colspan="3" class="p-10 text-center text-gray-400 italic text-sm">No completed sales yet</td></tr>
                                </template>
                                <template x-for="sale in pagedSales" :key="sale.id">
                                    <tr class="hover:bg-orange-50 dark:hover:bg-orange-50/10 transition">
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-orange-100/10 border border-orange-500/20 text-[10px] font-black text-[#EE6D3C]"
                                                x-text="'#' + sale.id"></span>
                                        </td>
                                        <td class="px-4 py-3 hidden md:table-cell">
                                            <p class="text-[var(--admin-text-primary)] text-sm font-medium" x-text="sale.date"></p>
                                            <p class="text-xs text-[var(--admin-text-secondary)]" x-text="sale.time"></p>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <p class="text-sm font-black text-[var(--admin-text-primary)]" x-text="'$' + sale.total.toFixed(2)"></p>
                                            <span class="text-[10px] font-bold text-green-500 bg-green-500/10 px-1.5 py-0.5 rounded-full border border-green-500/20">Paid</span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            @if($completedOrders->count())
                            <tfoot>
                                <tr class="bg-[#FFF8F5] border-t-2 border-[#EE6D3C]/20">
                                    <td colspan="2" class="px-4 py-3 text-xs font-bold uppercase tracking-widest text-gray-400">
                                        Grand Total · {{ $completedOrders->count() }} orders
                                    </td>
                                    <td class="px-4 py-3 text-right text-base font-black text-[#EE6D3C]">
                                        ${{ number_format($grandTotal, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>

                    <div class="flex items-center justify-between mt-4 flex-wrap gap-2">
                        <p class="text-xs text-gray-400"
                            x-text="filteredSales.length
                                ? 'Showing ' + ((salePage-1)*salePerPage+1) + '–' + Math.min(salePage*salePerPage, filteredSales.length) + ' of ' + filteredSales.length + ' entries'
                                : ''"></p>
                        <div x-show="saleTotalPages > 1" class="flex items-center gap-1.5 flex-wrap">
                            <button @click="salePage--" :disabled="salePage === 1"
                                class="w-9 h-9 rounded-lg bg-[var(--admin-card-bg)] border border-[var(--admin-border)] hover:bg-orange-50 dark:hover:bg-orange-50/10 transition font-bold text-[var(--admin-text-primary)] flex items-center justify-center">‹</button>
                            <template x-for="p in salePageNums" :key="p">
                                <button @click="salePage = p"
                                    :class="salePage === p ? 'bg-[#EE6D3C] text-white border-[#EE6D3C] shadow-md' : 'bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)] border border-[var(--admin-border)] hover:bg-orange-50/10'"
                                    class="w-9 h-9 rounded-lg border font-bold transition text-sm"><span x-text="p"></span></button>
                            </template>
                            <button @click="salePage++" :disabled="salePage === saleTotalPages"
                                class="w-9 h-9 rounded-lg bg-[var(--admin-card-bg)] border border-[var(--admin-border)] hover:bg-orange-50 dark:hover:bg-orange-50/10 transition font-bold text-[var(--admin-text-primary)] flex items-center justify-center">›</button>
                        </div>
                    </div>

                </div></div>
        </main>

        <div id="export-modal" :class="exportModal ? 'open' : ''" @click.self="exportModal = false">
            <div class="modal-box">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-[#FFE4DB] flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#EE6D3C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-[var(--admin-text-primary)] text-base">Confirm Export</h3>
                        <p class="text-xs text-[var(--admin-text-secondary)]">This will download your report</p>
                    </div>
                </div>
                <div class="bg-[var(--admin-bg-primary)] rounded-xl p-4 mb-5 border border-[var(--admin-border)]">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-[var(--admin-text-secondary)]">Format</span>
                        <span class="font-semibold text-[var(--admin-text-primary)]" x-text="exportLabel"></span>
                    </div>
                    <div class="flex items-center justify-between text-sm mt-2">
                        <span class="text-[var(--admin-text-secondary)]">Section</span>
                        <span class="font-semibold text-[#EE6D3C] capitalize" x-text="activeTab"></span>
                    </div>
                    <div class="flex items-center justify-between text-sm mt-2">
                        <span class="text-[var(--admin-text-secondary)]">Generated</span>
                        <span class="font-semibold text-[var(--admin-text-primary)]">{{ now()->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button @click="exportModal = false"
                        class="flex-1 py-2.5 rounded-xl border border-[var(--admin-border)] text-sm font-semibold text-[var(--admin-text-secondary)] hover:bg-[var(--admin-bg-primary)] transition">Cancel</button>
                    <button @click="confirmExport()"
                        class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition active:scale-95"
                        :class="exportColorClass">Export now</button>
                </div>
            </div>
        </div>

        <div id="toast" class="no-print">
            <div class="flex items-center gap-3 bg-gray-900 text-white px-5 py-3.5 rounded-2xl shadow-xl text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span x-text="toastMsg"></span>
            </div>
        </div>

        <div id="printable-invoice" class="hidden">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold uppercase underline">FastBite — Full Report</h1>
                <p class="text-sm mt-2">Generated: {{ now()->format('d M Y, h:i A') }}</p>
            </div>

            <h2 class="text-xl font-bold mb-2 mt-6">Reservations</h2>
            <table class="w-full text-left border-collapse mb-8">
                <thead>
                    <tr class="border-b-2 border-black">
                        <th class="py-2">Name</th><th class="py-2">Phone</th>
                        <th class="py-2">Date</th><th class="py-2">Table</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservations as $res)
                        <tr class="border-b">
                            <td class="py-2">{{ $res->full_name }}</td>
                            <td class="py-2">{{ $res->phone_number }}</td>
                            <td class="py-2">{{ $res->date }} {{ $res->time }}</td>
                            <td class="py-2">Table {{ $res->table_id }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h2 class="text-xl font-bold mb-2 mt-6">Orders</h2>
            <table class="w-full text-left border-collapse mb-8">
                <thead>
                    <tr class="border-b-2 border-black">
                        <th class="py-2">ID</th><th class="py-2">Items</th>
                        <th class="py-2">Status</th><th class="py-2">Total</th><th class="py-2">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $o)
                        <tr class="border-b">
                            <td class="py-2">#{{ $o->id }}</td>
                            <td class="py-2">
                                @if($o->items && $o->items->count())
                                    {{ $o->items->map(fn($i) => ($i->name ?? $i->product?->name ?? 'Unknown') . ' ×' . $i->quantity)->join(', ') }}
                                @else —
                                @endif
                            </td>
                            <td class="py-2">{{ ucfirst($o->status) }}</td>
                            <td class="py-2">${{ number_format($o->total_amount ?? 0, 2) }}</td>
                            <td class="py-2">{{ \Carbon\Carbon::parse($o->created_at)->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h2 class="text-xl font-bold mb-2 mt-6">Products</h2>
            <table class="w-full text-left border-collapse mb-8">
                <thead>
                    <tr class="border-b-2 border-black">
                        <th class="py-2">Name</th><th class="py-2">Category</th>
                        <th class="py-2">Price</th><th class="py-2">Cost</th><th class="py-2">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $p)
                        <tr class="border-b">
                            <td class="py-2">{{ $p->name }}</td>
                            <td class="py-2">{{ $p->category?->name ?? 'N/A' }}</td>
                            <td class="py-2">${{ number_format($p->price, 2) }}</td>
                            <td class="py-2">${{ number_format($p->cost ?? 0, 2) }}</td>
                            <td class="py-2">{{ $p->qty ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h2 class="text-xl font-bold mb-2 mt-6">Categories</h2>
            <table class="w-full text-left border-collapse mb-8">
                <thead>
                    <tr class="border-b-2 border-black">
                        <th class="py-2">ID</th><th class="py-2">Name</th>
                        <th class="py-2">Code</th><th class="py-2">Total Products</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $c)
                        <tr class="border-b">
                            <td class="py-2">#{{ $c->id }}</td>
                            <td class="py-2">{{ $c->name }}</td>
                            <td class="py-2">{{ $c->code ?? '—' }}</td>
                            <td class="py-2">{{ $c->products_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h2 class="text-xl font-bold mb-2 mt-6">Daily Sales Report</h2>
            <table class="w-full text-left border-collapse mb-4">
                <thead>
                    <tr class="border-b-2 border-black">
                        <th class="py-2 text-sm">Order ID</th>
                        <th class="py-2 text-sm">Date & Time</th>
                        <th class="py-2 text-sm text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($completedOrders->sortByDesc('created_at') as $sale)
                        <tr class="border-b border-gray-200">
                            <td class="py-1.5 text-sm">#{{ $sale->id }}</td>
                            <td class="py-1.5 text-sm">{{ \Carbon\Carbon::parse($sale->created_at)->format('d M Y, h:i A') }}</td>
                            <td class="py-1.5 text-sm text-right font-semibold">${{ number_format($sale->total_amount ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="border-t-2 border-black">
                        <td colspan="2" class="py-1.5 text-sm font-bold text-right">Grand Total:</td>
                        <td class="py-1.5 text-sm font-bold text-right">${{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-8 rounded-2xl overflow-hidden">
                <div style="background:#EE6D3C;" class="flex items-center justify-between px-6 py-5">
                    <div>
                        <p style="font-size:10px;font-weight:800;letter-spacing:0.12em;text-transform:uppercase;color:rgba(255,255,255,0.75);margin-bottom:2px;">Grand Total · All Sales</p>
                        <p style="font-size:12px;color:rgba(255,255,255,0.6);">{{ $completedOrders->count() }} completed orders</p>
                    </div>
                    <p style="font-size:2rem;font-weight:900;color:#fff;">${{ number_format($grandTotal, 2) }}</p>
                </div>
            </div>
        </div>

    </div>
</div>