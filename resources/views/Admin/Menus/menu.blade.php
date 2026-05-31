@include('partials.theme-head')
@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
    body { font-family: 'Inter', sans-serif; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #EE6D3C; border-radius: 10px; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    [x-cloak] { display: none !important; }
    @media print {
        body * { visibility: hidden; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        #printable-invoice, #printable-invoice * { visibility: visible !important; }
        #printable-invoice { position: absolute; left: 0; top: 0; width: 100%; padding: 20px; display: block !important; }
        #printable-invoice img#print-qr-img { display: inline-block !important; }
        .no-print { display: none !important; }
    }
</style>



<title>FastBite | Menu</title>

<div class="bg-[var(--admin-bg-primary)] min-h-screen text-[var(--admin-text-primary)]"
    x-data="{
        categories: {{ $categories }},
        products: {{ $products }},
        orders: {{ $ordersJson }},

        activeCategory: null,
        cart: [],
        showOrderModal: false,
        showStatusModal: false,
        showInvoiceModal: false,
        showViewModal: false,
        mobileMenuOpen: false,
        editOrderId: null,
        editOrderStatus: 'pending',
        editPrepTimeMin: 10,
        editPrepTimeMax: 20,
        invoiceOrder: null,
        viewOrder: null,
        stockAlerts: [],
        toast: null,
        currentPage: 1,
        perPage: 6,
        reservationName: '',
        prepTimeMin: 10,
        prepTimeMax: 20,
        clockTick: Date.now(),
        allReservations: {{ $reservations }},

        /* ── Order History Pagination ── */
        orderPage: 1,
        orderPerPage: 5,
        orderSearchQuery: '',

        /* ── Theme state ── */
        // ... (existing state)

        toKhr(usd) {
            return (parseFloat(usd) * 4100).toLocaleString('en-US', { maximumFractionDigits: 0 });
        },

        showToast(msg, type) {
            this.toast = { msg, type: type || 'success' };
            setTimeout(() => { this.toast = null; }, 3500);
        },

        get filteredProducts() {
            if (!this.activeCategory) return this.products;
            return this.products.filter(p => p.category === this.activeCategory);
        },
        get paginatedProducts() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredProducts.slice(start, start + this.perPage);
        },
        get totalPages()  { return Math.ceil(this.filteredProducts.length / this.perPage); },
        get pageNumbers() { return Array.from({ length: this.totalPages }, (_, i) => i + 1); },
        setCategory(cat) { this.activeCategory = cat; this.currentPage = 1; },

        get filteredOrders() {
            if (!this.orderSearchQuery) return this.orders;
            const q = this.orderSearchQuery.toLowerCase();
            return this.orders.filter(o =>
                [
                    o.id,
                    o.table_number,
                    o.reservation_name,
                    o.tracking_target,
                ]
                .filter(Boolean)
                .join(' ')
                .toLowerCase()
                .includes(q)
            );
        },

        get paginatedOrders() {
            const start = (this.orderPage - 1) * this.orderPerPage;
            return this.filteredOrders.slice(start, start + this.orderPerPage);
        },
        get orderTotalPages()  { return Math.ceil(this.filteredOrders.length / this.orderPerPage); },
        get orderPageNumbers() { return Array.from({ length: this.orderTotalPages }, (_, i) => i + 1); },

        get subtotal() { return this.cart.reduce((sum, item) => sum + item.price * item.qty, 0); },

        getProductImage(image) {
            if (image) return '/storage/' + image;
            return 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=200&q=80';
        },
        formatOrderTarget(order) {
            if (!order) return 'Walk-in Order';
            if (order.reservation_name) return order.reservation_name;
            if (order.table_number) return 'Table ' + order.table_number;
            return 'Walk-in Order';
        },
        selectedReservation() {
            if (!this.reservationName) return null;
            const name = this.reservationName.trim().toLowerCase();
            return this.allReservations.find(res => String(res.name).trim().toLowerCase() === name) || null;
        },
        resolvedTableNumber() {
            return this.selectedReservation()?.table ? String(this.selectedReservation().table) : null;
        },
        remainingMinutes(order) {
            this.clockTick;
            if (!order || !order.estimated_ready_at) return null;
            const diff = Math.ceil((new Date(order.estimated_ready_at).getTime() - Date.now()) / 60000);
            return diff;
        },
        autoStatus(order) {
            this.clockTick;
            if (!order) return 'pending';
            if (['cancelled', 'completed'].includes(order.status)) return order.status;
            if (!order.created_at_iso || !order.estimated_ready_at) return order.status || 'pending';

            const startedAt = new Date(order.created_at_iso).getTime();
            const readyAt = new Date(order.estimated_ready_at).getTime();
            const now = Date.now();
            const totalMinutes = Math.max(1, Math.ceil((readyAt - startedAt) / 60000));

            if (now >= readyAt) return 'completed';

            const elapsedMinutes = Math.max(0, Math.floor((now - startedAt) / 60000));

            if (elapsedMinutes >= Math.max(1, Math.ceil(totalMinutes * 0.35))) return 'processing';
            if (elapsedMinutes >= 1) return 'confirmed';

            return 'pending';
        },
        trackingSummary(order) {
            if (!order) return 'No tracking available';
            const status = this.autoStatus(order);
            if (status === 'completed') return 'Ready to serve';
            if (status === 'cancelled') return 'Order cancelled';

            const minutes = this.remainingMinutes(order);
            if (minutes === null) {
                return order.tracking_window ? 'Estimated in ' + order.tracking_window : 'Tracking pending';
            }
            if (minutes <= 0) return 'Ready any time now';
            return minutes + ' min remaining';
        },
        addToCart(id, name, price, image) {
            const ex = this.cart.find(i => i.id === id);
            if (ex) { ex.qty++; } else { this.cart.push({ id, name, price, image, qty: 1 }); }
        },
        removeFromCart(id) {
            const ex = this.cart.find(i => i.id === id);
            if (ex) { ex.qty--; if (ex.qty === 0) this.cart = this.cart.filter(i => i.id !== id); }
        },

        saveOrder() {
            if (!this.reservationName) {
                this.showToast('Please enter reservation or customer name', 'error');
                return;
            }
            if (this.prepTimeMax < this.prepTimeMin) {
                this.showToast('Max minutes must be greater than min minutes', 'error');
                return;
            }
            fetch('/order/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    cart: this.cart,
                    total: this.subtotal,
                    table_number: this.resolvedTableNumber(),
                    reservation_name: this.reservationName,
                    prep_time_min: this.prepTimeMin,
                    prep_time_max: this.prepTimeMax
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.message) {
                    if (data.stock_alerts && data.stock_alerts.length > 0) this.stockAlerts = data.stock_alerts;
                    const now    = new Date();
                    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    const pad    = n => String(n).padStart(2,'0');
                    const h      = now.getHours();
                    const h12    = h > 12 ? h - 12 : (h === 0 ? 12 : h);
                    const ampm   = h >= 12 ? 'PM' : 'AM';
                    const resolvedTable = this.resolvedTableNumber();
                    this.orders.unshift({
                        id:           data.order_id,
                        table_number: resolvedTable,
                        reservation_name: this.reservationName,
                        tracking_target: this.reservationName || resolvedTable,
                        status:       'pending',
                        total_amount: this.subtotal,
                        prep_time_min: data.prep_time_min,
                        prep_time_max: data.prep_time_max,
                        tracking_window: data.tracking_window,
                        estimated_ready_at: data.estimated_ready_at,
                        created_at_iso: now.toISOString(),
                        created_at:   pad(now.getDate()) + ' ' + months[now.getMonth()] + ' ' + now.getFullYear(),
                        time:         pad(h12) + ':' + pad(now.getMinutes()) + ' ' + ampm,
                        items:        this.cart.map((i, idx) => ({ uid: idx, name: i.name, quantity: i.qty })),
                    });
                    this.cart           = [];
                    this.reservationName = '';
                    this.prepTimeMin    = 10;
                    this.prepTimeMax    = 20;
                    this.showOrderModal = false;
                    this.orderPage      = 1;
                    this.showToast('Order #' + data.order_id + ' saved!');
                } else {
                    this.showToast(data.error || 'Order failed', 'error');
                }
            })
            .catch(() => this.showToast('Network error. Please try again.', 'error'));
        },

        openStatusModal(order) {
            this.editOrderId      = order.id;
            this.editOrderStatus  = this.autoStatus(order).toLowerCase();
            this.editPrepTimeMin  = order.prep_time_min || 10;
            this.editPrepTimeMax  = order.prep_time_max || 20;
            this.showStatusModal = true;
        },

        updateStatus() {
            if (this.editPrepTimeMax < this.editPrepTimeMin) {
                this.showToast('Max minutes must be greater than min minutes', 'error');
                return;
            }
            fetch('/order/' + this.editOrderId + '/status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    status: this.editOrderStatus,
                    prep_time_min: this.editPrepTimeMin,
                    prep_time_max: this.editPrepTimeMax
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.message) {
                    const order = this.orders.find(o => o.id === this.editOrderId);
                    if (order) {
                        order.status = data.status;
                        order.prep_time_min = data.prep_time_min;
                        order.prep_time_max = data.prep_time_max;
                        order.tracking_window = data.tracking_window;
                        order.estimated_ready_at = data.estimated_ready_at;
                    }
                    this.showStatusModal = false;
                    this.showToast('Order #' + this.editOrderId + ' → ' + this.editOrderStatus.charAt(0).toUpperCase() + this.editOrderStatus.slice(1));
                } else {
                    this.showToast('Update failed', 'error');
                }
            })
            .catch(() => this.showToast('Network error', 'error'));
        },

        deleteOrder(id) {
            if (!confirm('Send a deletion request for order #' + id + '?')) return;
            fetch('/order/' + id, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.message) {
                    this.showToast(data.message);
                } else {
                    this.showToast(data.error || 'Delete request failed', 'error');
                }
            })
            .catch(() => this.showToast('Network error', 'error'));
        },

        openInvoiceAndPrint(order) {
            this.invoiceOrder = order;
            this.$nextTick(() => {
                window.print();
            });
        },

        openViewModal(order) {
            this.viewOrder      = order;
            this.showViewModal  = true;
        },

        printCurrentCart() {
            if (!this.reservationName) {
                this.showToast('Please enter reservation or customer name first', 'error');
                return;
            }
            if (this.prepTimeMax < this.prepTimeMin) {
                this.showToast('Max minutes must be greater than min minutes', 'error');
                return;
            }
            // Create a mock order object for the printable invoice
            const now    = new Date();
            const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            const pad    = n => String(n).padStart(2,'0');
            const h      = now.getHours();
            const h12    = h > 12 ? h - 12 : (h === 0 ? 12 : h);
            const ampm   = h >= 12 ? 'PM' : 'AM';

            this.invoiceOrder = {
                id:           'DRAFT',
                table_number: this.resolvedTableNumber(),
                reservation_name: this.reservationName,
                status:       'pending',
                total_amount: this.subtotal,
                prep_time_min: this.prepTimeMin,
                prep_time_max: this.prepTimeMax,
                tracking_window: this.prepTimeMin + '-' + this.prepTimeMax + ' min',
                created_at:   pad(now.getDate()) + ' ' + months[now.getMonth()] + ' ' + now.getFullYear(),
                time:         pad(h12) + ':' + pad(now.getMinutes()) + ' ' + ampm,
                items:        this.cart.map((i, idx) => ({ uid: idx, name: i.name, quantity: i.qty })),
            };

            this.$nextTick(() => {
                window.print();
            });
        },

        init() {
            this.$watch('orderSearchQuery', () => {
                this.orderPage = 1;
            });
            setInterval(() => {
                this.clockTick = Date.now();
            }, 60000);
        }


    }">

    <!-- ===== ALERTS & TOASTS ===== -->
    @include('Admin.Menus.partials.alerts')

    <!-- ===== MAIN LAYOUT =====  -->
    <div class="flex flex-col md:flex-row md:h-screen md:p-4 md:gap-6 md:overflow-hidden relative no-print">

        @include('components.asidebar')

        <main class="flex-1 overflow-y-auto px-3 pb-4 md:px-0 md:pr-2 custom-scrollbar">
            <!-- ===== MENU BOARD =====  -->
            @include('Admin.Menus.partials.menu-board')

            <!-- ===== ORDER HISTORY =====  -->
            @include('Admin.Menus.partials.order-history')
        </main>
    </div>

    <!-- ===== MODALS & PRINT LAYOUT =====  -->
    @include('Admin.Menus.partials.modals')

</div>
