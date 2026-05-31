{{-- ===== ORDER CONFIRMATION MODAL ===== --}}
<div x-show="showOrderModal" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4 md:p-6 no-print">
    <div class="bg-[var(--admin-card-bg)] w-full max-w-2xl max-h-[90vh] flex flex-col rounded-3xl p-6 md:p-10 shadow-2xl relative border border-[var(--admin-border)]">
        <button @click="showOrderModal = false"
            class="absolute top-6 left-6 bg-[#EE6D3C] text-white p-2 rounded-xl hover:scale-105 transition z-10">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </button>
        
        <h2 class="text-2xl md:text-3xl font-bold text-center mb-6 md:mb-8 text-[var(--admin-text-primary)] shrink-0">Food Order</h2>
        
        <div class="bg-[var(--admin-bg-primary)] rounded-3xl p-4 md:p-6 shadow-inner border border-[var(--admin-border)] overflow-y-auto custom-scrollbar flex-1">
            <div class="grid grid-cols-3 text-[var(--admin-text-secondary)] font-bold mb-4 px-2 text-sm md:text-base">
                <span>Item</span><span class="text-center">QTY</span><span class="text-right">Price</span>
            </div>
            
            <div class="space-y-4 mb-8 max-h-[180px] md:max-h-[250px] overflow-y-auto pr-2 custom-scrollbar">
                <template x-for="item in cart" :key="item.id">
                    <div class="grid grid-cols-3 items-center border-b border-[var(--admin-border)] pb-4">
                        <div class="flex items-center gap-3">
                            <img :src="getProductImage(item.image)" :alt="item.name" class="w-12 h-12 rounded-xl object-cover border border-[var(--admin-border)] shrink-0"/>
                            <span class="font-bold text-[var(--admin-text-primary)] text-xs md:text-sm line-clamp-2" x-text="item.name"></span>
                        </div>
                        <div class="flex justify-center items-center gap-2 md:gap-3">
                            <button @click="removeFromCart(item.id)" class="w-6 h-6 bg-[var(--admin-bg-primary)] rounded text-[var(--admin-text-primary)] hover:bg-orange-50/10 transition font-bold flex items-center justify-center">-</button>
                            <span class="font-bold text-[var(--admin-text-primary)] text-sm md:text-base" x-text="item.qty"></span>
                            <button @click="addToCart(item.id, item.name, item.price, item.image)" class="w-6 h-6 bg-[#EE6D3C] text-white rounded hover:bg-orange-600 transition font-bold flex items-center justify-center">+</button>
                        </div>
                        <div class="text-right text-[#EE6D3C] font-bold text-sm md:text-base" x-text="'$' + (item.price * item.qty).toFixed(2)"></div>
                    </div>  
                </template>
            </div>
            
            <div class="mb-6">
                <label class="block text-[var(--admin-text-secondary)] text-xs md:text-sm font-bold mb-2 uppercase tracking-wide">Reservation / Customer Name</label>
                <input type="text" x-model="reservationName" list="reservation-list" placeholder="Select reservation name or walk-in guest..."
                    class="w-full px-4 py-3 rounded-xl border border-[var(--admin-border)] focus:border-[#EE6D3C] focus:ring-1 focus:ring-[#EE6D3C] outline-none transition font-bold text-[var(--admin-text-primary)] bg-[var(--admin-card-bg)] text-sm md:text-base">
                <datalist id="reservation-list">
                    <template x-for="res in allReservations" :key="res.name + res.table">
                        <option :value="res.name"></option>
                    </template>
                </datalist>
            </div>
            
            <div class="mb-6 rounded-2xl bg-[var(--admin-card-bg)] border border-[var(--admin-border)] px-4 py-3 text-xs md:text-sm">
                <span class="font-bold text-[var(--admin-text-primary)]">Assigned table:</span>
                <span class="text-[#EE6D3C]" x-text="resolvedTableNumber() ? ('Table ' + resolvedTableNumber()) : 'Walk-in / no reservation table'"></span>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-[var(--admin-text-secondary)] text-xs md:text-sm font-bold mb-2 uppercase tracking-wide">Min Prep</label>
                    <input type="number" min="1" max="240" x-model.number="prepTimeMin"
                        class="w-full px-4 py-3 rounded-xl border border-[var(--admin-border)] focus:border-[#EE6D3C] focus:ring-1 focus:ring-[#EE6D3C] outline-none transition font-bold text-[var(--admin-text-primary)] bg-[var(--admin-card-bg)] text-sm md:text-base">
                </div>
                <div>
                    <label class="block text-[var(--admin-text-secondary)] text-xs md:text-sm font-bold mb-2 uppercase tracking-wide">Max Prep</label>
                    <input type="number" min="1" max="240" x-model.number="prepTimeMax"
                        class="w-full px-4 py-3 rounded-xl border border-[var(--admin-border)] focus:border-[#EE6D3C] focus:ring-1 focus:ring-[#EE6D3C] outline-none transition font-bold text-[var(--admin-text-primary)] bg-[var(--admin-card-bg)] text-sm md:text-base">
                </div>
            </div>
            
            <div class="mb-6 rounded-2xl bg-[#EE6D3C]/8 border border-[#EE6D3C]/20 px-4 py-3 text-xs md:text-sm">
                <span class="font-bold text-[#EE6D3C]">Tracking window:</span>
                <span class="text-[var(--admin-text-primary)]" x-text="prepTimeMin + ' to ' + prepTimeMax + ' minutes'"></span>
            </div>
            
            <div class="bg-[var(--admin-card-bg)] p-6 rounded-2xl border-2 border-dashed border-[var(--admin-border)]">
                <div class="flex justify-between text-[var(--admin-text-primary)] font-black text-lg md:text-xl">
                    <span>Total price($):</span>
                    <span x-text="'$' + subtotal.toFixed(2)"></span>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4 mt-8">
                <button @click="saveOrder()"
                    class="flex-1 bg-[#EE6D3C] text-white py-4 rounded-2xl font-bold text-base md:text-lg shadow-md hover:scale-[1.02] hover:bg-orange-600 transition">Save Order</button>
                <button @click="printCurrentCart()"
                    class="flex-1 border-2 border-[#EE6D3C] text-[#EE6D3C] py-4 rounded-2xl font-bold text-base md:text-lg hover:bg-orange-50/10 transition">Print Invoice</button>
            </div>
        </div>
    </div>
</div>

{{-- ===== STATUS EDIT MODAL ===== --}}
<div x-show="showStatusModal" x-cloak x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4 no-print">
    <div class="bg-[var(--admin-card-bg)] w-full max-w-sm max-h-[90vh] flex flex-col rounded-2xl p-6 md:p-8 shadow-2xl border border-[var(--admin-border)]">
        <div class="overflow-y-auto pr-1 custom-scrollbar flex-1">
            <h3 class="text-xl font-bold text-[var(--admin-text-primary)] mb-2">Update Order Status</h3>
            <p class="text-sm text-[var(--admin-text-secondary)] mb-5">Order <strong x-text="'#' + editOrderId"></strong></p>
            
            <div class="grid grid-cols-1 gap-2 mb-6">
                <template x-for="s in ['pending','confirmed','processing','completed','cancelled']" :key="s">
                    <button @click="editOrderStatus = s"
                        :class="editOrderStatus?.toLowerCase() === s.toLowerCase() ? 'border-[#EE6D3C] bg-[#EE6D3C]/10 text-[#EE6D3C] font-bold' : 'border-[var(--admin-border)] text-[var(--admin-text-secondary)] hover:border-[#EE6D3C]/30'"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm transition text-left group">
                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                            :class="{'bg-yellow-400':s==='pending','bg-blue-400':s==='confirmed','bg-purple-400':s==='processing','bg-green-400':s==='completed','bg-red-400':s==='cancelled'}"></span>
                        <span x-text="s.charAt(0).toUpperCase() + s.slice(1)"></span>
                        <svg x-show="editOrderStatus?.toLowerCase() === s.toLowerCase()" class="w-4 h-4 ml-auto text-[#EE6D3C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </template>
            </div>
            
            <div class="grid grid-cols-2 gap-3 mb-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-[var(--admin-text-secondary)] mb-2">Min Prep</label>
                    <input type="number" min="1" max="240" x-model.number="editPrepTimeMin"
                        class="w-full px-4 py-3 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-bg-primary)] text-[var(--admin-text-primary)] focus:border-[#EE6D3C] focus:ring-1 focus:ring-[#EE6D3C] outline-none transition text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-[var(--admin-text-secondary)] mb-2">Max Prep</label>
                    <input type="number" min="1" max="240" x-model.number="editPrepTimeMax"
                        class="w-full px-4 py-3 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-bg-primary)] text-[var(--admin-text-primary)] focus:border-[#EE6D3C] focus:ring-1 focus:ring-[#EE6D3C] outline-none transition text-sm">
                </div>
            </div>
            
            <div class="mb-6 rounded-xl bg-[var(--admin-bg-primary)] border border-[var(--admin-border)] px-4 py-3 text-sm text-[var(--admin-text-primary)]">
                Kitchen estimate:
                <span class="font-bold text-[#EE6D3C]" x-text="editPrepTimeMin + ' to ' + editPrepTimeMax + ' minutes'"></span>
            </div>
        </div>
        
        <div class="flex gap-3 pt-2 shrink-0">
            <button @click="showStatusModal = false"
                class="flex-1 px-4 py-3 border border-[var(--admin-border)] text-[var(--admin-text-secondary)] font-bold rounded-xl hover:bg-orange-500/5 transition text-sm">Cancel</button>
            <button @click="updateStatus()"
                class="flex-1 px-4 py-3 bg-[#EE6D3C] hover:bg-orange-600 text-white font-bold rounded-xl transition text-sm shadow-lg shadow-orange-500/20">Update</button>
        </div>
    </div>
</div>

{{-- ===== VIEW ORDER MODAL ===== --}}
<div x-show="showViewModal" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 md:p-6 no-print">
    
    <div class="bg-[var(--admin-card-bg)] w-full max-w-4xl rounded-lg shadow-lg relative overflow-hidden flex flex-col lg:flex-row min-h-[400px] max-h-[90vh]">
        
        <div class="w-full lg:w-1/2 max-h-[250px] lg:max-h-none bg-[var(--admin-card-bg)] flex items-center justify-center p-4 relative border-b lg:border-b-0 lg:border-r border-[var(--admin-border)] shrink-0 lg:shrink">
            <template x-if="viewOrder && viewOrder.items && viewOrder.items[0]">
                <img :src="getProductImage(viewOrder.items[0].image)" class="max-w-full max-h-full object-contain rounded-lg">
            </template>
        </div>

        <div class="w-full lg:w-1/2 flex flex-col pt-8 pb-6 px-6 md:px-8 bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)] overflow-hidden">
            
            <div class="mb-4 shrink-0">
                <h2 class="text-2xl md:text-3xl font-bold text-[var(--admin-text-primary)] mb-2 truncate" 
                    x-text="formatOrderTarget(viewOrder)"></h2>
                <div class="text-sm text-[var(--admin-text-secondary)] flex flex-wrap items-center gap-1.5">
                    <span x-text="viewOrder ? ('Posted on ' + viewOrder.created_at) : ''"></span>
                    <span class="mx-1">&middot;</span>
                    <span x-text="viewOrder ? viewOrder.time : ''"></span>
                    <span class="ml-2 px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wide border inline-block"
                        :class="{
                            'bg-yellow-500/10 text-yellow-500 border-yellow-500/20': viewOrder && autoStatus(viewOrder) === 'pending',
                            'bg-blue-500/10 text-blue-500 border-blue-500/20': viewOrder && autoStatus(viewOrder) === 'confirmed',
                            'bg-purple-500/10 text-purple-500 border-purple-500/20': viewOrder && autoStatus(viewOrder) === 'processing',
                            'bg-green-500/10 text-green-500 border-green-500/20': viewOrder && autoStatus(viewOrder) === 'completed',
                            'bg-red-500/10 text-red-500 border-red-500/20': viewOrder && autoStatus(viewOrder) === 'cancelled'
                        }" x-text="viewOrder ? autoStatus(viewOrder) : ''"></span>
                </div>
            </div>

            <hr class="border-[var(--admin-border)] mb-6 opacity-50 shrink-0">

            <div class="flex-1 overflow-y-auto mb-6 pr-2 custom-scrollbar">
                <div class="space-y-3">
                    <template x-for="(item, idx) in (viewOrder ? viewOrder.items : [])" :key="idx">
                        <div class="flex justify-between items-center text-sm md:text-base text-[var(--admin-text-primary)] hover:bg-[var(--admin-bg-primary)] p-2 -mx-2 rounded transition">
                            <span class="font-medium truncate pr-4" x-text="item.name"></span>
                            <span class="flex-shrink-0 font-bold bg-[var(--admin-bg-primary)] px-2 py-1 rounded text-[var(--admin-text-primary)] border border-[var(--admin-border)]" x-text="'x' + item.quantity"></span>
                        </div>
                    </template>
                </div>
                
                <div class="mt-8">
                     <p class="text-[var(--admin-text-secondary)] text-xs font-bold uppercase tracking-wider mb-1">Order Tracking</p>
                     <p class="text-[var(--admin-text-primary)] text-sm font-bold" x-text="trackingSummary(viewOrder)"></p>
                     <p class="text-[var(--admin-text-secondary)] text-xs mt-1" x-text="viewOrder && viewOrder.tracking_window ? ('Kitchen estimate: ' + viewOrder.tracking_window) : 'Kitchen estimate not set'"></p>
                </div>

                <div class="mt-8">
                     <p class="text-[var(--admin-text-secondary)] text-xs font-bold uppercase tracking-wider mb-1">Total Amount</p>
                     <p class="text-[var(--admin-text-primary)] text-xl md:text-2xl font-bold font-mono">
                         <span x-text="'$' + parseFloat(viewOrder ? viewOrder.total_amount : 0).toFixed(2)"></span>
                         <span class="text-sm text-[var(--admin-text-secondary)] font-normal ml-2" x-text="'(៛' + toKhr(viewOrder ? viewOrder.total_amount : 0) + ')'"></span>
                     </p>
                </div>
            </div>

            <hr class="border-[var(--admin-border)] mb-4 opacity-50 shrink-0">

            <div class="flex justify-end gap-3 shrink-0">
                <button @click="showViewModal = false; openStatusModal(viewOrder)"
                    class="px-5 py-2.5 bg-[#EE6D3C] hover:bg-orange-600 text-white text-sm font-bold rounded-lg transition shadow-sm">
                    Update Status
                </button>
                <button @click="showViewModal = false"
                    class="px-5 py-2.5 bg-slate-600 hover:bg-slate-700 text-white text-sm font-bold rounded-lg transition shadow-sm">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ===== PRINTABLE INVOICE (Optimized for Thermal Printers - Matches First Screenshot Layout) ===== --}}
<div id="printable-invoice" style="display:none; width: 100%; max-width: 320px; margin: 0 auto; color: #000; background: #fff; font-family: 'Courier New', Courier, monospace;">
    <div style="padding: 10px 8px;">
        
        <!-- Store Header -->
        <div style="text-align: center; margin-bottom: 12px;">
            <div style="display: flex; justify-content: center; margin-bottom: 5px;">
                <img src="{{ Vite::asset('resources/images/FASTBITE_LOGO.png') }}" style="width: 55px; height: 55px; object-fit: contain; filter: grayscale(100%);">
            </div>
            <h1 style="font-size: 22px; font-weight: bold; margin: 0 0 3px 0; letter-spacing: 1px;">FASTBITE</h1>
            <p style="font-size: 10px; margin: 0 0 3px 0; font-weight: bold;">PREMIUM FAST FOOD & DINING</p>
            <p style="font-size: 9px; margin: 0; color: #333;">123 Monivong Blvd, SIEM REAP</p>
            <p style="font-size: 9px; margin: 0; color: #333;">Tel: +855 12 345 678</p>
        </div>

        <div style="border-top: 1px dashed #000; margin: 8px 0;"></div>

        <!-- Order Meta -->
        <div style="font-size: 10px; line-height: 1.4; margin-bottom: 8px;">
            <div style="display: flex; justify-content: space-between;">
                <span>INVOICE: <strong x-text="invoiceOrder ? '#' + invoiceOrder.id : ''"></strong></span>
                <span x-text="invoiceOrder ? invoiceOrder.time : ''"></span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>DATE: <strong x-text="invoiceOrder ? invoiceOrder.created_at : ''"></strong></span>
                <span>CASHIER</span>
            </div>
            <div style="margin-top: 4px;">
                <span>TABLE/GUEST: <strong x-text="formatOrderTarget(invoiceOrder)"></strong></span>
            </div>
        </div>

        <div style="border-top: 1px dashed #000; margin: 8px 0;"></div>

        <!-- Items Table - CRITICAL FIX: Each item displays its own line with correct amount -->
        <table style="width: 100%; font-size: 11px; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px dashed #000;">
                    <th style="padding-bottom: 5px; font-weight: bold; width: 50%;">ITEM</th>
                    <th style="padding-bottom: 5px; font-weight: bold; text-align: center; width: 20%;">QTY</th>
                    <th style="padding-bottom: 5px; font-weight: bold; text-align: right; width: 30%;">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(item, idx) in (invoiceOrder ? invoiceOrder.items : [])" :key="idx">
                    <tr>
                        <td style="padding: 6px 0; font-size: 11px; vertical-align: top; word-break: break-word;" x-text="item.name"></td>
                        <td style="padding: 6px 0; font-size: 11px; text-align: center; vertical-align: top;" x-text="item.quantity"></td>
                        <td style="padding: 6px 0; font-size: 11px; text-align: right; vertical-align: top;" 
                            x-text="'$' + (item.price ? (item.price * item.quantity).toFixed(2) : ((invoiceOrder.total_amount / (invoiceOrder.items ? invoiceOrder.items.reduce((sum, i) => sum + i.quantity, 0) : 1)) * item.quantity).toFixed(2))"></td>
                    </tr>
                </template>
            </tbody>
        </table>

        <div style="border-top: 1px dashed #000; margin: 8px 0;"></div>

        <!-- Financial Summary - CORRECTED SUBTOTAL CALCULATION -->
        <div style="font-size: 11px; line-height: 1.5; width: 100%;">
            <div style="display: flex; justify-content: space-between;">
                <span>Subtotal:</span>
                <span x-text="'$' + parseFloat(invoiceOrder ? invoiceOrder.total_amount : 0).toFixed(2)"></span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 10px; color: #444;">
                <span>VAT (0% Incl.):</span>
                <span>$0.00</span>
            </div>
            
            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; margin-top: 5px; padding-top: 5px; border-top: 1px dotted #000;">
                <span>TOTAL (USD):</span>
                <span x-text="'$' + parseFloat(invoiceOrder ? invoiceOrder.total_amount : 0).toFixed(2)"></span>
            </div>

            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 12px; margin-top: 3px;">
                <span>TOTAL (KHR):</span>
                <span x-text="'៛' + toKhr(invoiceOrder ? invoiceOrder.total_amount : 0)"></span>
            </div>
        </div>

        <div style="border-top: 1px dashed #000; margin: 12px 0 8px 0;"></div>

        <!-- Footer -->
        <div style="text-align: center; font-size: 9px; line-height: 1.3;">
            <p style="margin: 0 0 3px 0; font-weight: bold;">THANK YOU FOR YOUR VISIT!</p>
            <p style="margin: 0 0 5px 0;">Please check your change before leaving.</p>
            <p style="margin: 0; color: #555; font-size: 8px;">Powered by FastBite POS</p>
        </div>
        
    </div>
</div>