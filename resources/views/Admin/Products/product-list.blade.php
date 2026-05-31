@include('partials.theme-head')
@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
    body { font-family: 'Inter', sans-serif; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #EE6D3C; border-radius: 10px; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
</style>

<title>FastBite | Product List</title>

<div class="bg-[var(--admin-bg-primary)] min-h-screen text-[var(--admin-text-primary)]">
    <div class="flex flex-col md:flex-row md:h-screen md:p-4 md:gap-6 md:overflow-hidden relative">

        @include('components.asidebar')

        <main class="flex-1 overflow-y-auto px-3 pb-4 md:px-0 md:pr-2 custom-scrollbar">
            <div class="bg-[var(--admin-card-bg)] rounded-lg shadow-sm border border-[var(--admin-border)] p-6 md:p-8 mt-3 md:mt-0 mb-8">

                @include('components.alerts')

                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[var(--admin-accent)]/10 flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 text-[var(--admin-accent)]"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M20 7L12 3 4 7m16 0v10l-8 4m8-14l-8 4m0 10L4 17V7m8 14V11M4 7l8 4"/>
                            </svg>
                        </div>
                     <h2 class="text-2xl font-bold text-[var(--admin-text-primary)]">Product List</h2>
                        <span class="text-xs font-bold bg-[var(--admin-bg-primary)] text-[var(--admin-accent)] px-3 py-1 rounded-full border border-[var(--admin-accent)]/20">Products</span>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <form method="GET" action="{{ route('allproduct.index') }}" class="relative w-full md:w-72">
                            <input type="text" name="search"
                                value="{{ request('search') }}"
                                placeholder="Search product..."
                                class="w-full pl-4 pr-10 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]">
                            <button type="submit" class="absolute right-3 top-3.5 text-[var(--admin-text-secondary)] hover:text-[#EE6D3C] transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </form>
                        <a href="{{ route('products-form.index') }}"
                            class="flex items-center gap-2 bg-gray-900 hover:bg-gray-700 text-white text-sm font-bold px-4 py-3 rounded-xl transition whitespace-nowrap">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create New
                        </a>
                    </div>
                </div>

                <!-- Table -->
                <div class="w-full overflow-x-auto rounded-xl border border-[var(--admin-border)]">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[var(--admin-bg-primary)]">
                            <tr class="border-b border-[var(--admin-border)] text-[var(--admin-text-secondary)] uppercase text-xs">
                                <!-- <th class="p-4 w-10"><input type="checkbox" class="rounded border-[var(--admin-border)] bg-[var(--admin-card-bg)]"></th> -->
                                <th class="p-4">Image</th>
                                <th class="p-4">Name</th>
                                <th class="p-4">Category</th>
                                <th class="p-4">Qty</th>
                                <th class="p-4">Price</th>
                                <th class="p-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($products as $product)
                            <tr class="hover:bg-orange-50/50 transition">
                                <!-- <td class="p-4"><input type="checkbox" class="rounded border-gray-300"></td> -->
                                <td class="p-4">
                                    @php
                                        $imagePath = 'https://via.placeholder.com/80';
                                        if ($product->images) {
                                            if (is_array($product->images) && count($product->images) > 0) {
                                                $imagePath = asset('storage/' . $product->images[0]);
                                            } elseif (is_string($product->images)) {
                                                $imagePath = asset('storage/' . $product->images);
                                            }
                                        }
                                    @endphp
                                    <img src="{{ $imagePath }}" alt="{{ $product->name }}"
                                        class="w-12 h-12 rounded-xl object-cover border border-gray-100 shadow-sm">
                                </td>
                                <td class="p-4 font-semibold text-[var(--admin-text-primary)]">{{ $product->name }}</td>
                                <td class="p-4">
                                    <span class="bg-orange-100/10 text-[#EE6D3C] px-3 py-1 rounded-full text-xs font-bold border border-[#EE6D3C]/20">
                                        {{ $product->category->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="p-4 text-[var(--admin-text-secondary)]">{{ $product->qty }}</td>
                                <td class="p-4 font-bold text-[#EE6D3C]">${{ number_format($product->price, 2) }}</td>
                                <td class="p-4">
                                    <div class="flex justify-center items-center gap-2">
                                        <a href="{{ route('product.edit', $product->id) }}"
                                            class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-800 hover:text-white hover:border-gray-800 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('product.destroy', $product->id) }}" method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this product?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="flex items-center justify-center w-8 h-8 rounded-lg border border-red-300 text-red-500 hover:bg-red-500 hover:text-white hover:border-red-500 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="p-10 text-center text-gray-400 italic">No products found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $products->links() }}</div>

            </div>
        </main>
    </div>
</div>