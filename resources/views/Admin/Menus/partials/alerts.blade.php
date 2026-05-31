{{-- resources/views/Admin/Menus/partials/alerts.blade.php --}}

{{-- ===== TOAST ===== --}}
<div x-show="toast" x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[60] no-print pointer-events-none">
    <div class="flex items-center gap-3 px-6 py-3.5 rounded-2xl shadow-2xl text-sm font-semibold whitespace-nowrap"
        :class="toast && toast.type === 'error' ? 'bg-red-600 text-white' : 'bg-gray-900 text-white'">
        <template x-if="toast && toast.type !== 'error'">
            <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
            </svg>
        </template>
        <template x-if="toast && toast.type === 'error'">
            <svg class="w-4 h-4 text-red-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </template>
        <span x-text="toast ? toast.msg : ''"></span>
    </div>
</div>

{{-- ===== STOCK ALERT BANNERS ===== --}}
@if($outOfStockProducts->count() || $lowStockProducts->count())
<div class="no-print">
    @if($outOfStockProducts->count())
    <div class="flex items-start gap-3 bg-red-50 border-b border-red-200 px-6 py-3 text-sm text-red-700">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div><strong><svg class="w-4 h-4 inline-block -mt-0.5 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728"/></svg> Out of Stock:</strong> {{ $outOfStockProducts->pluck('name')->join(', ') }} — Please restock immediately.</div>
    </div>
    @endif
    @if($lowStockProducts->count())
    <div class="flex items-start gap-3 bg-amber-50 border-b border-amber-200 px-6 py-3 text-sm text-amber-700">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <strong><svg class="w-4 h-4 inline-block -mt-0.5 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Low Stock (≤2 units):</strong>
            @foreach($lowStockProducts as $p)
                <span class="font-semibold">{{ $p->name }}</span> ({{ $p->qty }} left)@if(!$loop->last), @endif
            @endforeach
        </div>
    </div>
    @endif
</div>
@endif

{{-- ===== POST-ORDER STOCK TOASTS ===== --}}
<div x-show="stockAlerts.length > 0" x-cloak class="fixed top-4 right-4 z-50 space-y-2 no-print" style="max-width:340px">
    <template x-for="(alert, i) in stockAlerts" :key="i">
        <div class="flex items-start gap-3 p-4 rounded-xl shadow-lg border"
            :class="alert.qty <= 0 ? 'bg-red-50 border-red-200 text-red-700' : 'bg-amber-50 border-amber-200 text-amber-700'">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="font-bold text-sm flex items-center gap-1"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" :d="alert.qty <= 0 ? 'M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728' : 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'"/></svg> <span x-text="alert.qty <= 0 ? 'Out of Stock' : 'Low Stock'"></span></p>
                <p class="text-xs mt-0.5" x-text="alert.name + ' — ' + alert.qty + ' units left'"></p>
            </div>
            <button @click="stockAlerts.splice(i,1)" class="ml-auto text-current opacity-50 hover:opacity-100"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
    </template>
</div>
