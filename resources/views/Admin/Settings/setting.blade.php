@include('partials.theme-head')
@vite(['resources/css/app.css', 'resources/js/app.js'])


<style>
    body { font-family: 'Inter', sans-serif; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #EE6D3C; border-radius: 10px; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
</style>
<title>FastBite | Settings</title>

<div class="bg-[var(--admin-bg-primary)] min-h-screen text-[var(--admin-text-primary)]"
     x-data="{
        mobileMenuOpen: false,
        theme: localStorage.getItem('theme') || 'light'
     }"
     @theme-changed.window="theme = $event.detail">
    <div class="flex flex-col md:flex-row md:h-screen md:p-4 md:gap-6 md:overflow-hidden relative">

        @include('components.asidebar')

        <main class="flex-1 overflow-y-auto px-3 pb-4 md:px-0 md:pr-2 custom-scrollbar">
            @include('components.alerts')

            <div class="bg-[var(--admin-bg-secondary)] rounded-lg shadow-sm border border-[var(--admin-border)] mt-3 md:mt-0 mb-8 overflow-hidden"
                 x-data="{ activeTab: '{{ $tab ?? 'general' }}' }">

                {{-- Header --}}
                <div class="p-6 md:p-8 border-b border-[var(--admin-border)]">
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
                                    d="M12 8a4 4 0 100 8 4 4 0 000-8zm8 4a8 8 0 01-.5 2.8l2.1 1.6-2 3.4-2.5-1a8.1 8.1 0 01-2.4 1.4l-.4 2.7h-4l-.4-2.7a8.1 8.1 0 01-2.4-1.4l-2.5 1-2-3.4 2.1-1.6A8 8 0 014 12c0-1 .2-1.9.5-2.8L2.4 7.6l2-3.4 2.5 1a8.1 8.1 0 012.4-1.4l.4-2.7h4l.4 2.7a8.1 8.1 0 012.4 1.4l2.5-1 2 3.4-2.1 1.6c.3.9.5 1.8.5 2.8z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-[var(--admin-text-primary)]">Settings</h2>
                        <span class="text-xs font-bold bg-[var(--admin-bg-primary)] text-[var(--admin-accent)] px-3 py-1 rounded-full border border-[var(--admin-accent)]/20">Configuration</span>
                    </div>
                    <p class="text-[var(--admin-text-secondary)] text-sm mt-1">Manage your restaurant system and account preferences.</p>
                </div>

                {{-- Tab Buttons --}}
                <div class="flex border-b border-[var(--admin-border)] bg-[var(--admin-bg-primary)]">
                    <button @click="activeTab = 'general'"
                        :class="activeTab === 'general' ? 'border-b-2 border-[var(--admin-accent)] text-[var(--admin-accent)] bg-[var(--admin-card-bg)] shadow-sm' : 'text-[var(--admin-text-secondary)] hover:text-[var(--admin-accent)]'"
                        class="flex items-center gap-2 px-6 py-4 text-sm font-bold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                        General
                    </button>
                    <button @click="activeTab = 'profile'"
                        :class="activeTab === 'profile' ? 'border-b-2 border-[var(--admin-accent)] text-[var(--admin-accent)] bg-[var(--admin-card-bg)]' : 'text-[var(--admin-text-secondary)] hover:text-[var(--admin-accent)]'"
                        class="flex items-center gap-2 px-6 py-4 text-sm font-bold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profile
                    </button>
                </div>

                {{-- TAB: GENERAL --}}
                <div x-show="activeTab === 'general'" x-transition>
                    <form action="{{ route('setting.save') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="p-6 md:p-8 space-y-10">

                            {{-- BRAND --}}
                            <div>
                                <div class="flex items-center gap-2 mb-5">
                                    <span class="w-1.5 h-5 bg-[#EE6D3C] rounded-full"></span>
                                    <h3 class="text-base font-bold text-[var(--admin-text-primary)] uppercase tracking-wide">Brand</h3>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="flex flex-col gap-2">
                                        <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wide">Restaurant Name</label>
                                        <input type="text" name="restaurant_name"
                                            value="{{ old('restaurant_name', $settings['restaurant_name']) }}"
                                            class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]">
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wide">Logo Text</label>
                                        <input type="text" name="logo_text"
                                            value="{{ old('logo_text', $settings['logo_text']) }}"
                                            class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]">
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wide">Tagline (Hero Title)</label>
                                        <input type="text" name="tagline"
                                            value="{{ old('tagline', $settings['tagline']) }}"
                                            class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]">
                                    </div>
                                    <div class="flex flex-col gap-2 md:col-span-2">
                                        <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wide">Hero Description</label>
                                        <textarea name="description" rows="3"
                                            class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm resize-none transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]">{{ old('description', $settings['description']) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- HERO IMAGE --}}
                            <div x-data="{
                                previewUrl: '{{ $settings['hero_image'] ? asset('storage/'.$settings['hero_image']) : '' }}',
                                handleFile(e) {
                                    const file = e.target.files[0];
                                    if (file) this.previewUrl = URL.createObjectURL(file);
                                }
                            }">
                                <div class="flex items-center gap-2 mb-5">
                                    <span class="w-1.5 h-5 bg-[#EE6D3C] rounded-full"></span>
                                    <h3 class="text-base font-bold text-[var(--admin-text-primary)] uppercase tracking-wide">Hero Image</h3>
                                </div>
                                <div class="flex flex-col md:flex-row gap-6 items-start">
                                    <div class="relative group w-full md:w-72 h-48 rounded-xl border-2 border-dashed border-[var(--admin-border)] hover:border-[#EE6D3C] transition overflow-hidden bg-[var(--admin-bg-primary)] flex items-center justify-center cursor-pointer flex-shrink-0">
                                        <input type="file" name="hero_image" accept="image/*"
                                            @change="handleFile($event)"
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                        <template x-if="previewUrl">
                                            <img :src="previewUrl" class="w-full h-full object-cover absolute inset-0">
                                        </template>
                                        <template x-if="!previewUrl">
                                            <div class="text-center p-4 pointer-events-none">
                                                <svg class="w-10 h-10 mx-auto text-[var(--admin-text-secondary)] group-hover:text-[#EE6D3C] transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <p class="mt-2 text-sm text-[var(--admin-text-secondary)] group-hover:text-[#EE6D3C] font-medium transition">Click to upload</p>
                                                <p class="text-xs text-[var(--admin-text-secondary)] opacity-50 mt-1">PNG, JPG, WEBP — max 2MB</p>
                                            </div>
                                        </template>
                                        <template x-if="previewUrl">
                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center z-0 pointer-events-none">
                                                <p class="text-white text-sm font-bold">Change Image</p>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="flex flex-col gap-3">
                                        <p class="text-sm text-gray-500">Recommended size: <strong>800×600px</strong>.</p>
                                        @if($settings['hero_image'])
                                        <button type="button"
                                            onclick="document.getElementById('delete-image-form').submit()"
                                            class="flex items-center gap-2 px-4 py-2 border border-red-300 text-red-500 hover:bg-red-50/10 rounded-xl text-sm font-semibold transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Remove Image
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- STATS --}}
                            <div>
                                <div class="flex items-center gap-2 mb-5">
                                    <span class="w-1.5 h-5 bg-[var(--admin-accent)] rounded-full"></span>
                                    <h3 class="text-base font-bold text-[var(--admin-text-primary)] uppercase tracking-wide">Stats (shown on homepage)</h3>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                                    <div class="flex flex-col gap-2">
                                        <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wide">Happy Customers</label>
                                        <input type="text" name="happy_customers" value="{{ old('happy_customers', $settings['happy_customers']) }}"
                                            class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]">
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wide">Total Dishes</label>
                                        <input type="text" name="total_dishes" value="{{ old('total_dishes', $settings['total_dishes']) }}"
                                            class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]">
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wide">Rating</label>
                                        <input type="text" name="rating" value="{{ old('rating', $settings['rating']) }}"
                                            class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]">
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wide">Delivery Time (min)</label>
                                        <input type="text" name="delivery_time" value="{{ old('delivery_time', $settings['delivery_time']) }}"
                                            class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]">
                                    </div>
                                </div>
                            </div>

                            {{-- CONTACT --}}
                            <div>
                                <div class="flex items-center gap-2 mb-5">
                                    <span class="w-1.5 h-5 bg-[#EE6D3C] rounded-full"></span>
                                    <h3 class="text-base font-bold text-[var(--admin-text-primary)] uppercase tracking-wide">Contact & Location</h3>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="flex flex-col gap-2">
                                        <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wide">Phone Number</label>
                                        <input type="text" name="phone" value="{{ old('phone', $settings['phone']) }}"
                                            class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]">
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wide">Email Address</label>
                                        <input type="email" name="email" value="{{ old('email', $settings['email']) }}"
                                            class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]">
                                    </div>
                                    <div class="flex flex-col gap-2 md:col-span-2">
                                        <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wide">Address</label>
                                        <textarea name="address" rows="2"
                                            class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm resize-none transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]">{{ old('address', $settings['address']) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- SYSTEM --}}
                            <div>
                                <div class="flex items-center gap-2 mb-5">
                                    <span class="w-1.5 h-5 bg-[#EE6D3C] rounded-full"></span>
                                    <h3 class="text-base font-bold text-[var(--admin-text-primary)] uppercase tracking-wide">System</h3>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="flex flex-col gap-2">
                                        <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wide">Currency</label>
                                        <select name="currency"
                                            class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)] transition">
                                            <option value="USD" {{ old('currency', $settings['currency']) === 'USD' ? 'selected' : '' }}>USD ($)</option>
                                            <option value="KHR" {{ old('currency', $settings['currency']) === 'KHR' ? 'selected' : '' }}>KHR (៛)</option>
                                        </select>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wide">Timezone</label>
                                        <select name="timezone"
                                            class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)] transition">
                                            <option value="Asia/Phnom_Penh" {{ old('timezone', $settings['timezone']) === 'Asia/Phnom_Penh' ? 'selected' : '' }}>Asia/Phnom_Penh (UTC+7)</option>
                                            <option value="Asia/Bangkok"     {{ old('timezone', $settings['timezone']) === 'Asia/Bangkok'    ? 'selected' : '' }}>Asia/Bangkok (UTC+7)</option>
                                            <option value="UTC"              {{ old('timezone', $settings['timezone']) === 'UTC'             ? 'selected' : '' }}>UTC</option>
                                        </select>
                                    </div>

                                    {{-- THEME TOGGLE --}}
                                    <div class="flex flex-col gap-2">
                                        <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wide">System Theme</label>
                                        <div class="flex items-center gap-4 mt-2">
                                            <button type="button" @click="window.toggleTheme()"
                                                    class="flex items-center gap-2 px-6 py-3 border border-[var(--admin-border)] rounded-xl text-sm font-bold hover:bg-orange-50/10 transition text-[var(--admin-text-primary)]">
                                                <template x-if="theme === 'dark'">
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                                        <span>Switch to Light Mode</span>
                                                    </div>
                                                </template>
                                                <template x-if="theme !== 'dark'">
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                                                        <span>Switch to Dark Mode</span>
                                                    </div>
                                                </template>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="p-5 bg-[var(--admin-bg-primary)] border-t border-[var(--admin-border)] flex justify-end gap-3">
                            <a href="{{ route('setting.index') }}"
                                class="px-6 py-3 border border-[var(--admin-border)] text-[var(--admin-text-secondary)] font-bold rounded-xl hover:bg-orange-50/10 transition text-sm">
                                Reset
                            </a>
                            <button type="submit"
                                class="flex items-center gap-2 bg-gray-900 hover:bg-gray-700 text-white text-sm font-bold px-4 py-3 rounded-xl transition whitespace-nowrap">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                {{-- TAB: PROFILE --}}
                <div x-show="activeTab === 'profile'" x-transition>

                    <div class="p-6 md:p-8 border-b border-[var(--admin-border)]">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-orange-500/10 border-4 border-[#EE6D3C] flex items-center justify-center flex-shrink-0">
                                <span class="text-xl font-bold text-[#EE6D3C]">
                                    {{ strtoupper(substr($user->username, 0, 2)) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-[var(--admin-text-primary)]">{{ $user->username }}</p>
                                <span class="inline-block text-xs font-bold px-3 py-1 rounded-full bg-orange-500/10 text-[#EE6D3C] mt-1">
                                    {{ $user->username }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('profile.updateInfo') }}" method="POST"
                          class="p-6 md:p-8 border-b border-[var(--admin-border)]">
                        @csrf
                        <div class="flex items-center gap-2 mb-5">
                            <span class="w-1.5 h-5 bg-[#EE6D3C] rounded-full"></span>
                            <h3 class="text-base font-bold text-[var(--admin-text-primary)] uppercase tracking-wide">Account Info</h3>
                        </div>
                        <div class="max-w-md flex flex-col gap-2">
                            <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wide">Username</label>
                            <input type="text" name="username"
                                value="{{ old('username', $user->username) }}"
                                class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]
                                    @error('username') border-red-400 @enderror">
                            @error('username')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex justify-end mt-6">
                            <button type="submit"
                                class="flex items-center gap-2 px-6 py-3 bg-gray-900 hover:bg-gray-700 text-white font-bold rounded-xl transition text-sm">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Update Username
                            </button>
                        </div>
                    </form>

                    <form action="{{ route('profile.updatePassword') }}" method="POST"
                          class="p-6 md:p-8 border-b border-gray-100"
                          x-data="{ strengthWidth: '0%', strengthColor: '#e5e7eb' }">
                        @csrf
                        <div class="flex items-center gap-2 mb-5">
                            <span class="w-1.5 h-5 bg-[#EE6D3C] rounded-full"></span>
                            <h3 class="text-base font-bold text-[var(--admin-text-primary)] uppercase tracking-wide">Change Password</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 max-w-2xl">
                            <div class="flex flex-col gap-2 md:col-span-2">
                                <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wide">Current Password</label>
                                <input type="password" name="current_password" placeholder="Enter current password"
                                    class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]
                                        @error('current_password') border-red-400 @enderror">
                                @error('current_password')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wide">New Password</label>
                                <input type="password" name="new_password" placeholder="Min. 8 characters"
                                    @input="
                                        const v = $event.target.value; let s = 0;
                                        if (v.length >= 8) s++;
                                        if (/[A-Z]/.test(v)) s++;
                                        if (/[0-9]/.test(v)) s++;
                                        if (/[^A-Za-z0-9]/.test(v)) s++;
                                        strengthWidth = ['0%','30%','55%','80%','100%'][s];
                                        strengthColor = ['#e5e7eb','#ef4444','#f97316','#eab308','#22c55e'][s];
                                    "
                                    class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]
                                        @error('new_password') border-red-400 @enderror">
                                <div class="h-1 bg-[var(--admin-bg-primary)] rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-300"
                                         :style="'width:' + strengthWidth + ';background:' + strengthColor"></div>
                                </div>
                                <p class="text-xs text-[var(--admin-text-secondary)] opacity-60">Uppercase, number, and symbol required.</p>
                                @error('new_password')
                                    <p class="text-red-500 text-xs">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wide">Confirm New Password</label>
                                <input type="password" name="new_password_confirmation" placeholder="Repeat new password"
                                    class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]">
                            </div>
                        </div>
                        <div class="flex justify-end mt-6">
                            <button type="submit"
                                class="flex items-center gap-2 px-6 py-3 bg-[#EE6D3C] hover:bg-orange-700 text-white font-bold rounded-xl transition text-sm">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Change Password
                            </button>
                        </div>
                    </form>

                    <div class="p-6 md:p-8" x-data="{ confirm: '' }">
                        <div class="flex items-center gap-2 mb-5">
                            <span class="w-1.5 h-5 bg-red-500 rounded-full"></span>
                            <h3 class="text-base font-bold text-[var(--admin-text-primary)] uppercase tracking-wide">Danger Zone</h3>
                        </div>
                        <div class="bg-red-50/10 border border-red-500/20 rounded-xl p-5">
                            <p class="font-bold text-red-500 text-sm">Request Account Deletion</p>
                            <p class="text-xs text-red-400 mt-1">Your request will be reviewed by an administrator. You will be notified once it's processed.</p>
                            <form action="{{ route('profile.destroy') }}" method="POST" class="mt-4">
                                @csrf
                                @method('DELETE')
                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-[var(--admin-text-secondary)] mb-1">Reason (optional)</label>
                                    <textarea name="reason" rows="3" placeholder="Please provide a reason for your deletion request..."
                                        class="w-full px-4 py-2 border border-red-500/30 rounded-xl text-sm outline-none focus:ring-2 focus:ring-red-200 bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]"></textarea>
                                </div>
                                <button type="submit"
                                    class="flex items-center gap-2 px-5 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Request Deletion
                                </button>
                            </form>
                        </div>
                    </div>

                </div>

            </div>
        </main>
    </div>
</div>

<form id="delete-image-form" action="{{ route('setting.deleteImage') }}" method="POST" class="hidden">
    @csrf
</form>