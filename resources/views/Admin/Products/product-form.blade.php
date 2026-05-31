@include('partials.theme-head')
@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
    body { font-family: 'Inter', sans-serif; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: var(--admin-accent); border-radius: 10px; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
</style>

<title>FastBite | Create Product </title>

<div class="bg-[var(--admin-bg-primary)] min-h-screen text-[var(--admin-text-primary)]">
    <div class="flex flex-col md:flex-row md:h-screen md:p-4 md:gap-6 md:overflow-hidden relative">

        @include('components.asidebar')

        <main class="flex-1 overflow-y-auto px-3 pb-4 md:px-0 md:pr-2 custom-scrollbar">
            <div class="bg-[var(--admin-card-bg)] rounded-lg shadow-sm border border-[var(--admin-border)] p-6 md:p-8 mt-3 md:mt-0 mb-8">

                @include('components.alerts')

                <!-- Header -->
                <div class="flex items-center gap-3 mb-8">
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
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 8v6m3-3H9"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-[var(--admin-text-primary)]">
                        {{ isset($product) ? 'Edit Product' : 'Create New Product' }}
                    </h2>
                    <span class="text-xs font-bold bg-[var(--admin-bg-primary)] text-[var(--admin-accent)] px-3 py-1 rounded-full border border-[var(--admin-accent)]/20">
                        {{ isset($product) ? 'Editing' : 'New' }}
                    </span>
                </div>

                <!-- Form -->
                <form action="{{ isset($product) ? route('product.update', $product->id) : route('products-form.store') }}"
                      method="POST"
                      enctype="multipart/form-data"
                      x-data="{
                          previewUrl: '{{ isset($product) && $product->images && is_array($product->images) ? asset('storage/' . $product->images[0]) : '' }}',
                          handleFile(e) {
                              const file = e.target.files[0];
                              if (file) this.previewUrl = URL.createObjectURL(file);
                          }
                      }">
                    @csrf
                    @if(isset($product)) @method('PUT') @endif

                    <div class="flex flex-col xl:flex-row gap-8">

                        {{-- Left: Fields --}}
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-5">

                            <div class="flex flex-col gap-2">
                                <label class="font-bold text-[var(--admin-text-secondary)] text-sm uppercase tracking-wide">Name *</label>
                                <input type="text" name="name" required
                                    value="{{ old('name', $product->name ?? '') }}"
                                    placeholder="Enter product name"
                                    class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)] @error('name') border-red-400 @enderror">
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="font-bold text-[var(--admin-text-secondary)] text-sm uppercase tracking-wide">Qty *</label>
                                <input type="number" name="qty" required
                                    value="{{ old('qty', $product->qty ?? '') }}"
                                    placeholder="0"
                                    class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)] @error('qty') border-red-400 @enderror">
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="font-bold text-[var(--admin-text-secondary)] text-sm uppercase tracking-wide">Price *</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[var(--admin-text-secondary)] font-bold text-sm">$</span>
                                    <input type="text" name="price" required
                                        value="{{ old('price', $product->price ?? '') }}"
                                        placeholder="0.00"
                                        class="w-full pl-8 pr-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)] @error('price') border-red-400 @enderror">
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="font-bold text-[var(--admin-text-secondary)] text-sm uppercase tracking-wide">Cost *</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[var(--admin-text-secondary)] font-bold text-sm">$</span>
                                    <input type="text" name="cost" required
                                        value="{{ old('cost', $product->cost ?? '') }}"
                                        placeholder="0.00"
                                        class="w-full pl-8 pr-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)] @error('cost') border-red-400 @enderror">
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="font-bold text-[var(--admin-text-secondary)] text-sm uppercase tracking-wide">Category *</label>
                                <select name="category_id" required
                                    class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)] transition @error('category_id') border-red-400 @enderror">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="font-bold text-[var(--admin-text-secondary)] text-sm uppercase tracking-wide">Count *</label>
                                <input type="number" name="count" required
                                    value="{{ old('count', $product->count ?? 0) }}"
                                    placeholder="0"
                                    class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)] @error('count') border-red-400 @enderror">
                            </div>

                            <div class="sm:col-span-2 flex flex-col gap-2">
                                <label class="font-bold text-gray-700 text-sm uppercase tracking-wide">Description</label>
                                <textarea name="description" rows="5"
                                    placeholder="Enter product description..."
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm resize-none transition">{{ old('description', $product->description ?? '') }}</textarea>
                            </div>

                        </div>

                        <!-- Right: Image Upload -->
                        <div class="w-full xl:w-80 flex-shrink-0">
                            <label class="font-bold text-[var(--admin-text-secondary)] text-sm uppercase tracking-wide block mb-3">Product Image</label>
                            <div class="relative group rounded-xl border-2 border-dashed border-[var(--admin-border)] hover:border-[var(--admin-accent)] transition overflow-hidden bg-[var(--admin-bg-primary)] aspect-square flex items-center justify-center cursor-pointer">
                                <input type="file" name="images" accept="image/*"
                                    @change="handleFile($event)"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <template x-if="previewUrl">
                                    <img :src="previewUrl" class="w-full h-full object-cover absolute inset-0">
                                </template>
                                <template x-if="!previewUrl">
                                    <div class="text-center p-6 pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-[var(--admin-text-secondary)] group-hover:text-[var(--admin-accent)] transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="mt-3 text-sm text-[var(--admin-text-secondary)] group-hover:text-[var(--admin-accent)] font-medium transition">Click to upload image</p>
                                        <p class="text-xs text-[var(--admin-text-secondary)] opacity-50 mt-1">PNG, JPG, WEBP</p>
                                    </div>
                                </template>
                                <template x-if="previewUrl">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center z-0 pointer-events-none">
                                        <p class="text-white text-sm font-bold">Change Image</p>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row justify-end gap-3 mt-8 pt-6 border-t border-[var(--admin-border)]">
                        <a href="{{ route('allproduct.index') }}"
                            class="px-6 py-3 border border-[var(--admin-border)] text-[var(--admin-text-secondary)] font-bold rounded-xl hover:bg-orange-50/10 transition text-sm text-center">
                            Cancel
                        </a>
                        <button type="submit"
                            class="flex items-center gap-2 px-6 py-3 bg-[var(--admin-accent)] hover:bg-orange-700 text-white font-bold rounded-xl transition text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ isset($product) ? 'Update Product' : 'Save Product' }}
                        </button>
                    </div>

                </form>
            </div>
        </main>
    </div>
</div>