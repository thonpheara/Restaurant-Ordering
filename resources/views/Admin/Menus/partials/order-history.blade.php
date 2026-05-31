<!-- resources/views/Admin/Menus/partials/order-history.blade.php -->

<div class="bg-[var(--admin-card-bg)] rounded-lg shadow-sm border border-[var(--admin-border)] p-6 md:p-8 mb-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <h2 class="text-xl font-bold text-[var(--admin-text-primary)]">Order History</h2>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold bg-[var(--admin-bg-primary)] text-[#EE6D3C] px-3 py-1 rounded-full border border-[#EE6D3C]/20"
                    x-text="orders.length + ' total'"></span>
                <template x-if="orderSearchQuery">
                    <span class="text-xs font-bold bg-[#EE6D3C]/10 text-[#EE6D3C] px-3 py-1 rounded-full transition-all"
                        x-text="filteredOrders.length + ' found'"></span>
                </template>
            </div>
        </div>
        <div class="relative w-full md:w-72 group">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-[var(--admin-text-secondary)] group-focus-within:text-[#EE6D3C] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" 
                x-model.debounce.300ms="orderSearchQuery"
                placeholder="Search order ID, table, reservation..."
                class="block w-full pl-11 pr-4 py-2.5 border border-[var(--admin-border)] rounded-xl leading-5 bg-[var(--admin-bg-primary)] text-[var(--admin-text-primary)] placeholder-[var(--admin-text-secondary)] focus:outline-none focus:ring-4 focus:ring-[#EE6D3C]/10 focus:border-[#EE6D3C] transition-all sm:text-sm shadow-sm hover:border-[#EE6D3C]/30"
            >
            <template x-if="orderSearchQuery">
                <button @click="orderSearchQuery = ''" class="absolute inset-y-0 right-0 pr-3 flex items-center text-[var(--admin-text-secondary)] hover:text-red-500 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </template>
        </div>
    </div>
    <div class="w-full overflow-x-auto rounded-xl border border-[var(--admin-border)]">
        <table class="w-full text-left text-sm">
            <thead class="bg-[var(--admin-bg-primary)]">
                <tr class="border-b text-[var(--admin-text-secondary)] uppercase text-xs">
                    <th class="p-4">ID</th>
                    <th class="p-4">Guest / Table</th>
                    <th class="p-4">Items</th>
                    <th class="p-4">Total</th>
                    <th class="p-4">Tracking</th>
                    <th class="p-4">Date</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <template x-if="filteredOrders.length === 0">
                    <tr>
                        <td colspan="7" class="p-12 text-center">
                            <div class="flex flex-col items-center justify-center gap-3 text-[var(--admin-text-secondary)]">
                                <svg class="w-12 h-12 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-base font-medium" x-text="orderSearchQuery ? 'No results found for \'' + orderSearchQuery + '\'' : 'No orders found.'"></span>
                                <template x-if="orderSearchQuery">
                                    <button @click="orderSearchQuery = ''" class="text-sm text-[#EE6D3C] hover:underline font-bold">Clear search</button>
                                </template>
                            </div>
                        </td>
                    </tr>
                </template>
                <template x-for="order in paginatedOrders" :key="order.id">
                    <tr class="hover:bg-orange-50/10 transition align-top">
                        <td class="p-4 font-medium text-[var(--admin-text-primary)] whitespace-nowrap" x-text=" + order.id"></td>
                        <td class="p-4 whitespace-nowrap">
                            <div class="font-bold text-[#EE6D3C]" x-text="formatOrderTarget(order)"></div>
                            <div class="text-xs text-[var(--admin-text-secondary)]" x-text="order.reservation_name ? ('Table ' + order.table_number) : 'Walk-in order'"></div>
                        </td>
                        <td class="p-4">
                            <div class="flex flex-col gap-1">
                                <template x-for="(item, idx) in (order.items || [])" :key="(order.id + '-' + idx)">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center w-5 h-5 bg-[#EE6D3C]/20 text-[#EE6D3C] text-[10px] font-bold rounded-full leading-none border border-[#EE6D3C]/30"
                                            x-text="item.quantity"></span>
                                        <span class="text-[var(--admin-text-primary)] font-medium" x-text="item.name"></span>
                                    </div>
                                </template>
                            </div>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span class="text-[#EE6D3C] font-bold text-base"
                                x-text="'$' + parseFloat(order.total_amount).toFixed(2)"></span>
                        </td>
                        <td class="p-4 min-w-[180px]">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold mb-2"
                                :class="{
                                    'text-yellow-700 border border-yellow-700': autoStatus(order) === 'pending',
                                    'text-blue-700 border border-blue-700':    autoStatus(order) === 'confirmed',
                                    'text-purple-700 border border-purple-700':autoStatus(order) === 'processing',
                                    'text-green-700 border border-green-700':  autoStatus(order) === 'completed',
                                    'text-red-700 border border-red-700':      autoStatus(order) === 'cancelled',
                                    'text-[var(--admin-text-secondary)]':    !['pending','confirmed','processing','completed','cancelled'].includes(order.status)
                                }">
                                <span x-text="autoStatus(order).charAt(0).toUpperCase() + autoStatus(order).slice(1)"></span>
                            </span>
                            <div class="text-xs font-semibold text-[var(--admin-text-primary)]" x-text="trackingSummary(order)"></div>
                            <div class="text-xs text-[var(--admin-text-secondary)]" x-text="order.tracking_window ? ('Kitchen target: ' + order.tracking_window) : 'Kitchen target not set'"></div>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <span class="block font-medium text-[var(--admin-text-primary)] text-xs" x-text="order.created_at"></span>
                            <span class="text-[var(--admin-text-secondary)] text-xs" x-text="order.time"></span>
                        </td>
                        <td class="p-4 whitespace-nowrap">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="openViewModal(order)"
                                    class="flex items-center gap-1 px-2.5 py-2 rounded-lg border border-[var(--admin-border)] hover:border-[#EE6D3C]/50 hover:bg-orange-500/5 text-[var(--admin-text-primary)] hover:text-[#EE6D3C] transition text-xs font-bold" title="View Order">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View
                                </button>
                                <button @click="openStatusModal(order)"
                                    class="p-2 rounded-lg border border-[var(--admin-border)] hover:border-blue-400/50 hover:bg-blue-500/5 text-[var(--admin-text-primary)] hover:text-blue-400 transition" title="Change Status">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button @click="openInvoiceAndPrint(order)"
                                    class="flex items-center gap-1 px-2.5 py-2 rounded-lg border border-[var(--admin-border)] hover:border-purple-400/50 hover:bg-purple-500/5 text-[var(--admin-text-primary)] hover:text-purple-400 transition text-xs font-bold" title="Print Invoice">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                    Invoice
                                </button>
                                <button @click="deleteOrder(order.id)"
                                    class="p-2 rounded-lg border border-[var(--admin-border)] hover:border-red-400/50 hover:bg-red-500/5 text-[var(--admin-text-primary)] hover:text-red-400 transition" title="Delete Order">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    {{-- Order History Pagination --}}
    <div x-show="orderTotalPages > 1" class="flex justify-center items-center gap-2 mt-6 flex-wrap">
        <button @click="orderPage--" :disabled="orderPage === 1"
            class="w-9 h-9 rounded-lg bg-[var(--admin-card-bg)] border border-[var(--admin-border)] hover:bg-[var(--admin-bg-primary)] disabled:opacity-40 disabled:cursor-not-allowed transition font-bold text-[var(--admin-text-primary)] flex items-center justify-center text-lg">‹</button>
        <div class="flex items-center gap-2 px-2">
            <template x-for="p in orderPageNumbers" :key="p">
                <button @click="orderPage = p"
                    :class="orderPage === p ? 'bg-[var(--admin-accent)] text-white shadow-lg shadow-[var(--admin-accent)]/20' : 'bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)] border border-[var(--admin-border)] hover:bg-[var(--admin-bg-primary)]'"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold transition-all"
                    x-text="p"></button>
            </template>
        </div>
        <button @click="orderPage++" :disabled="orderPage === orderTotalPages"
            class="w-9 h-9 rounded-lg bg-[var(--admin-card-bg)] border border-[var(--admin-border)] hover:bg-[var(--admin-bg-primary)] disabled:opacity-40 disabled:cursor-not-allowed transition font-bold text-[var(--admin-text-primary)] flex items-center justify-center text-lg">›</button>
    </div>
</div>
