@include('partials.theme-head')
@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
    body { font-family: 'Inter', sans-serif; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #EE6D3C; border-radius: 10px; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
</style>
<title>FastBite | {{ isset($user) ? 'Edit User' : 'Add User' }}</title>

<div class="bg-[var(--admin-bg-primary)] min-h-screen text-[var(--admin-text-primary)]" x-data="{ mobileMenuOpen: false }">
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
                                d="M15 14c2.761 0 5 2.239 5 5v1H4v-1c0-2.761 2.239-5 5-5h6z"/>
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 12a4 4 0 100-8 4 4 0 000 8z"/>
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M19 8v6m3-3h-6"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-[var(--admin-text-primary)]">
                        {{ isset($user) ? 'Edit User' : 'Create New User' }}
                    </h2>
                    <span class="text-xs font-bold bg-[var(--admin-bg-primary)] text-[var(--admin-accent)] px-3 py-1 rounded-full border border-[var(--admin-accent)]/20">
                        {{ isset($user) ? 'Editing' : 'New' }}
                    </span>
                </div>

                <!-- Form -->
                <form action="{{ isset($user) ? route('user.update', $user->id) : route('user.store') }}"
                      method="POST"
                      x-data="{ showPass: false, showConfirm: false }">
                    @csrf
                    @if(isset($user)) @method('PUT') @endif

                    <div class="max-w-lg flex flex-col gap-5">

                        <!-- Avatar preview -->
                        <div class="flex items-center gap-4 p-4 bg-[var(--admin-bg-primary)] rounded-xl border border-[var(--admin-border)]">
                            <div class="w-14 h-14 rounded-2xl bg-[#FFE4DB] text-[#EE6D3C] font-black text-2xl flex items-center justify-center flex-shrink-0">
                                {{ isset($user) ? strtoupper(substr($user->username, 0, 1)) : '?' }}
                            </div>
                            <div>
                                <p class="font-bold text-[var(--admin-text-primary)] text-sm">{{ isset($user) ? $user->username : 'New User' }}</p>
                                <p class="text-xs text-[var(--admin-text-secondary)]">{{ isset($user) ? ($user->role ?? 'User') : 'Role not set' }}</p>
                            </div>
                        </div>

                        <!-- Username -->
                        <div class="flex flex-col gap-2">
                            <label class="font-bold text-[var(--admin-text-secondary)] text-sm uppercase tracking-wide">Username *</label>
                            <input type="text" name="username" required
                                value="{{ old('username', $user->username ?? '') }}"
                                placeholder="Enter username"
                                class="w-full px-4 py-3 border border-[var(--admin-border)] bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition
                                    @error('username') border-red-400 @enderror">
                        </div>

                        <!-- Role -->
                        <div class="flex flex-col gap-2">
                            <label class="font-bold text-[var(--admin-text-secondary)] text-sm uppercase tracking-wide">Role *</label>
                            <select name="role" required
                                class="w-full px-4 py-3 border border-[var(--admin-border)] bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition
                                    @error('role') border-red-400 @enderror">
                                <option value="">Select role</option>
                                <option value="Admin" {{ old('role', $user->role ?? '') === 'Admin' ? 'selected' : '' }}>Admin</option>
                                <option value="User"  {{ old('role', $user->role ?? '') === 'User'  ? 'selected' : '' }}>User</option>
                            </select>
                        </div>

                        <!-- Password -->
                        <div class="flex flex-col gap-2">
                            <label class="font-bold text-gray-700 text-sm uppercase tracking-wide">
                                Password {{ isset($user) ? '(leave blank to keep current)' : '*' }}
                            </label>
                            <div class="relative">
                                <input :type="showPass ? 'text' : 'password'" name="password"
                                    {{ isset($user) ? '' : 'required' }}
                                    placeholder="{{ isset($user) ? 'Enter new password to change' : 'Create a password' }}"
                                    class="w-full px-4 py-3 pr-11 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition
                                        @error('password') border-red-400 @enderror">
                                <button type="button" @click="showPass = !showPass"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#EE6D3C] transition">
                                    <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="flex flex-col gap-2">
                            <label class="font-bold text-gray-700 text-sm uppercase tracking-wide">
                                Confirm Password {{ isset($user) ? '' : '*' }}
                            </label>
                            <div class="relative">
                                <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation"
                                    {{ isset($user) ? '' : 'required' }}
                                    placeholder="Repeat the password"
                                    class="w-full px-4 py-3 pr-11 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition">
                                <button type="button" @click="showConfirm = !showConfirm"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#EE6D3C] transition">
                                    <svg x-show="!showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- Footer Actions -->
                    <div class="flex flex-col sm:flex-row justify-end gap-3 mt-8 pt-6 border-t border-[var(--admin-border)]">
                        <a href="{{ route('user.index') }}"
                            class="px-6 py-3 border border-[var(--admin-border)] text-[var(--admin-text-secondary)] font-bold rounded-xl hover:bg-orange-50/10 transition text-sm text-center">
                            Cancel
                        </a>
                        <button type="submit"
                            class="flex items-center gap-2 px-6 py-3 bg-[#EE6D3C] hover:bg-orange-700 text-white font-bold rounded-xl transition text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ isset($user) ? 'Update User' : 'Save User' }}
                        </button>
                    </div>

                </form>
            </div>
        </main>
    </div>
</div>