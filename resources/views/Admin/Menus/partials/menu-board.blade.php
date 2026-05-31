{{-- resources/views/Admin/Menus/partials/menu-board.blade.php --}}

<div class="bg-[var(--admin-card-bg)] rounded-lg shadow-sm border border-[var(--admin-border)] p-6 md:p-8 mt-3 md:mt-0 mb-6">
    <h2 class="text-2xl md:text-3xl text-[var(--admin-text-primary)] font-bold mb-6 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[var(--admin-accent)]/10 flex items-center justify-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5 text-[var(--admin-accent)]"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor"
                 stroke-width="2">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M4 6h16M4 12h16M4 18h10"/>
            </svg>
        </div>
        <span>Menu</span>
        <span class="text-xs font-bold bg-[var(--admin-bg-primary)] text-[var(--admin-accent)] px-3 py-1 rounded-full border border-[var(--admin-accent)]/20">Menus</span>
    </h2>

    {{-- Category tabs --}}
    <div class="flex overflow-x-auto gap-3 pb-4 mb-8 no-scrollbar">
        <button @click="setCategory(null)"
            :class="activeCategory === null ? 'bg-[#EE6D3C] text-white' : 'bg-[var(--admin-card-bg)] border border-[var(--admin-border)] text-[var(--admin-text-primary)] hover:bg-orange-50/10'"
            class="flex-shrink-0 min-w-[120px] p-4 rounded-lg transition text-left focus:outline-none">
            <div class="font-bold text-lg">All</div>
            <div class="text-xs opacity-80" x-text="(products ? products.length : 0) + ' items'"></div>
        </button>
        <template x-for="cat in categories" :key="cat.name">
            <button @click="setCategory(cat.name)"
                :class="activeCategory === cat.name ? 'bg-[#EE6D3C] text-white' : 'bg-[var(--admin-card-bg)] border border-[var(--admin-border)] text-[var(--admin-text-primary)] hover:bg-orange-50/10'"
                class="flex-shrink-0 min-w-[120px] p-4 rounded-lg transition text-left focus:outline-none">
                <div class="font-bold text-lg" x-text="cat.name"></div>
                <div class="text-xs opacity-80" x-text="cat.count + ' items'"></div>
            </button>
        </template>
    </div>

    <div class="flex flex-col xl:flex-row gap-8">
        {{-- Product grid --}}
        <div class="flex-1">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-if="!filteredProducts || filteredProducts.length === 0">
                    <div class="col-span-1 sm:col-span-2 lg:col-span-3 text-center text-[var(--admin-text-secondary)] py-20">
                        <p class="text-xl">No products found.</p>
                    </div>
                </template>
                
                <template x-for="product in paginatedProducts" :key="product.id">
                    <div class="bg-[var(--admin-card-bg)] rounded-3xl border border-[var(--admin-border)] shadow-sm overflow-hidden group relative flex flex-col justify-between">
                        
                        {{-- Stock Status Badges --}}
                        <div class="absolute top-3 left-3 z-10 flex flex-col gap-1">
                            <template x-if="product.qty <= 2 && product.qty > 0">
                                <div class="bg-amber-500 text-white text-xs font-bold px-2 py-1 rounded-full flex items-center gap-1 shadow-sm">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Low Stock
                                </div>
                            </template>
                            <template x-if="product.qty <= 0">
                                <div class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full flex items-center gap-1 shadow-sm">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728"/></svg> Out of Stock
                                </div>
                            </template>
                        </div>

                        {{-- Product Image --}}
                        <div class="p-3">
                            <div class="w-full h-40 md:h-48 overflow-hidden rounded-2xl bg-[var(--admin-bg-primary)]">
                                <img :src="getProductImage(product.image)" class="w-full h-full object-cover group-hover:scale-105 transition duration-300 cursor-pointer" :alt="product.name">
                            </div>
                        </div>

                        {{-- Product Details --}}
                        <div class="p-5 pt-0 flex-1 flex flex-col justify-between">
                            <div>
                                <div class="text-[var(--admin-text-primary)] font-bold text-sm line-clamp-2" x-text="product.name"></div>
                                <div class="text-xs text-[var(--admin-text-secondary)] mt-0.5" x-text="'Stock: ' + product.qty + ' units'"></div>
                            </div>
                            
                            <div class="flex justify-between items-center mt-4">
                                <span class="text-[#EE6D3C] font-bold text-xl" x-text="'$' + (Number(product.price) || 0).toFixed(2)"></span>
                                <div class="flex items-center gap-2">
                                    <button @click="removeFromCart(product.id)" 
                                            :disabled="!(cart.find(i => i.id === product.id)?.qty > 0)"
                                            class="w-8 h-8 rounded-lg bg-[var(--admin-bg-primary)] border border-[var(--admin-border)] hover:bg-orange-50/10 transition font-bold text-[var(--admin-text-primary)] disabled:opacity-30 disabled:cursor-not-allowed">-</button>
                                    <span class="w-6 text-center font-bold text-[var(--admin-text-primary)] text-sm" x-text="cart.find(i => i.id === product.id)?.qty || 0"></span>
                                    <button @click="addToCart(product.id, product.name, product.price, product.image)"
                                        :disabled="product.qty <= 0 || (cart.find(i => i.id === product.id)?.qty >= product.qty)"
                                        class="w-8 h-8 rounded-lg bg-[#EE6D3C] hover:bg-orange-600 text-white transition font-bold disabled:opacity-30 disabled:cursor-not-allowed">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Pagination --}}
            <div x-show="totalPages > 1" x-cloak class="flex justify-center items-center gap-2 mt-8 flex-wrap">
                <button @click="currentPage--" :disabled="currentPage === 1"
                    class="w-9 h-9 rounded-lg bg-[var(--admin-card-bg)] border border-[var(--admin-border)] hover:bg-orange-50/10 disabled:opacity-40 disabled:cursor-not-allowed transition font-bold text-[var(--admin-text-primary)] flex items-center justify-center text-lg">‹</button>
                <template x-for="page in pageNumbers" :key="page">
                    <button @click="currentPage = page"
                        :class="currentPage === page ? 'bg-[#EE6D3C] text-white border-[#EE6D3C] shadow-md' : 'bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)] border-[var(--admin-border)] hover:bg-orange-50/10'"
                        class="w-9 h-9 rounded-lg border font-bold transition text-sm"><span x-text="page"></span></button>
                </template>
                <button @click="currentPage++" :disabled="currentPage === totalPages"
                    class="w-9 h-9 rounded-lg bg-[var(--admin-card-bg)] border border-[var(--admin-border)] hover:bg-orange-50/10 disabled:opacity-40 disabled:cursor-not-allowed transition font-bold text-[var(--admin-text-primary)] flex items-center justify-center text-lg">›</button>
            </div>
        </div>

        {{-- Checkout panel --}}
        <div class="w-full xl:w-[380px] flex-shrink-0">
            <div class="border border-[var(--admin-border)] rounded-xl p-6 sticky top-4 bg-[var(--admin-card-bg)] shadow-sm">
                <h3 class="text-2xl font-bold text-center mb-6 border-b border-[var(--admin-border)] pb-2 text-[var(--admin-text-primary)]">Checkout</h3>
                <div class="space-y-4 mb-6 max-h-[300px] overflow-y-auto custom-scrollbar">
                    <template x-if="cart.length === 0">
                        <p class="text-center text-[var(--admin-text-secondary)] py-10">Your cart is empty</p>
                    </template>
                    <template x-for="item in cart" :key="item.id">
                        <div class="flex justify-between items-center bg-[var(--admin-bg-primary)] p-2 rounded-lg border border-[var(--admin-border)]">
                            <span x-text="item.name" class="font-medium text-[var(--admin-text-primary)] text-sm truncate max-w-[200px]"></span>
                            <div class="flex items-center gap-3">
                                <span x-text="'x' + item.qty" class="text-[#EE6D3C] font-bold text-sm"></span>
                                <span x-text="'$' + (item.price * item.qty).toFixed(2)" class="text-[var(--admin-text-secondary)] text-xs font-semibold"></span>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="border-t border-[var(--admin-border)] pt-4">
                    <div class="flex justify-between font-bold text-xl mb-6 text-[var(--admin-text-primary)]">
                        <span>Total:</span>
                        <span x-text="'$' + (Number(subtotal) || 0).toFixed(2)"></span>
                    </div>
                    <button @click="showOrderModal = true" :disabled="cart.length === 0"
                        class="w-full bg-[#EE6D3C] text-white py-4 rounded-2xl font-bold text-xl hover:bg-orange-600 transition disabled:opacity-50 disabled:cursor-not-allowed shadow-md">
                        Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>