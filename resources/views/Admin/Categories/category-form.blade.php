@include('partials.theme-head')
@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
    body { font-family: 'Inter', sans-serif; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #EE6D3C; border-radius: 10px; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
</style>

<title>FastBite | Create Category</title>

<div class="bg-[var(--admin-bg-primary)] min-h-screen text-[var(--admin-text-primary)]">
    <div class="flex flex-col md:flex-row md:h-screen md:p-4 md:gap-6 md:overflow-hidden relative">
        @include('components.asidebar')
        <main class="flex-1 overflow-y-auto px-3 pb-4 md:px-0 md:pr-2 custom-scrollbar">
            <div class="bg-[var(--admin-card-bg)] rounded-lg shadow-sm border border-[var(--admin-border)] p-6 md:p-8 mt-3 md:mt-0 mb-8">

                @include('components.alerts')

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
                                d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6"/>
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 8v8m4-4H8"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-[var(--admin-text-primary)]">
                        {{ isset($category) ? 'Edit Category' : 'Create New Category' }}
                    </h2>
                    <span class="text-xs font-bold bg-[var(--admin-bg-primary)] text-[var(--admin-accent)] px-3 py-1 rounded-full border border-[var(--admin-accent)]/20">
                        {{ isset($category) ? 'Editing' : 'New' }}
                    </span>
                </div>
                <form action="{{ isset($category) ? route('category.update', $category->id) : route('addcategory.store') }}"
                      method="POST"
                      enctype="multipart/form-data"
                      x-data="{
                          previewUrl: '{{ isset($category) && $category->image ? asset('storage/'.$category->image) : '' }}',
                          handleFile(e) {
                              const file = e.target.files[0];
                              if (file) this.previewUrl = URL.createObjectURL(file);
                          }
                      }">
                    @csrf
                    @if(isset($category)) @method('PUT') @endif

                    <div class="flex flex-col xl:flex-row gap-8">
                        <div class="flex-1 flex flex-col gap-6">
                            <div class="flex flex-col gap-2">
                                <label class="font-bold text-[var(--admin-text-secondary)] text-sm uppercase tracking-wide">Category Name *</label>
                                <input type="text" name="name"
                                    value="{{ old('name', $category->name ?? '') }}"
                                    required
                                    placeholder="Enter category name"
                                    class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]
                                        @error('name') border-red-400 @enderror">
                                @error('name')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="font-bold text-[var(--admin-text-secondary)] text-sm uppercase tracking-wide">Description</label>
                                <textarea name="description" rows="6"
                                    placeholder="Enter description here..."
                                    class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm resize-none transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]">{{ old('description', $category->description ?? '') }}</textarea>
                            </div>
                        </div>

                        
                        <div class="w-full xl:w-80 flex-shrink-0">
                            <label class="font-bold text-[var(--admin-text-secondary)] text-sm uppercase tracking-wide block mb-3">Category Image</label>
                            <div class="relative group rounded-xl border-2 border-dashed border-[var(--admin-border)] hover:border-[#EE6D3C] transition overflow-hidden bg-[var(--admin-bg-primary)] aspect-square flex items-center justify-center cursor-pointer">
                                <input type="file" name="image" accept="image/*"
                                    @change="handleFile($event)"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <template x-if="previewUrl">
                                    <img :src="previewUrl" class="w-full h-full object-cover absolute inset-0">
                                </template>
                                <template x-if="!previewUrl">
                                    <div class="text-center p-6 pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-[var(--admin-text-secondary)] group-hover:text-[#EE6D3C] transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="mt-3 text-sm text-[var(--admin-text-secondary)] group-hover:text-[#EE6D3C] font-medium transition">Click to upload image</p>
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

                    <div class="flex flex-col sm:flex-row justify-end gap-3 mt-8 pt-6 border-t border-[var(--admin-border)]">
                        <a href="{{ route('allcategory.index') }}"
                            class="px-6 py-3 border border-[var(--admin-border)] text-[var(--admin-text-secondary)] font-bold rounded-xl hover:bg-orange-50/10 transition text-sm text-center">
                            Cancel
                        </a>
                        <button type="submit"
                            class="flex items-center gap-2 px-6 py-3 bg-[#EE6D3C] hover:bg-orange-700 text-white font-bold rounded-xl transition text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ isset($category) ? 'Update Category' : 'Save Category' }}
                        </button>
                    </div>

                </form>
            </div>
        </main>
    </div>
</div>