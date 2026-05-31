@include('partials.theme-head')
@vite(['resources/css/app.css', 'resources/js/app.js'])

<title>FastBite | Login</title>

<style>
    /* Force html & body to fill screen — critical for blade partials inside layouts */
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        background: var(--admin-bg-primary);
        color: var(--admin-text-primary);
    }

    /* Fixed overlay guarantees full-screen coverage regardless of parent layout */
    .login-overlay {
        position: fixed;
        inset: 0;
        background: var(--admin-bg-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        z-index: 1;
    }

    /* On mobile: the card fills the screen edge-to-edge, feels native */
    @media (max-width: 480px) {
        .login-overlay {
            padding: 0;
            align-items: stretch;
        }
        .login-shell {
            display: flex;
            flex-direction: column;
            min-height: 100%;
            width: 100%;
        }
        .login-card-wrap {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2rem 1.25rem 1.5rem;
            background: var(--admin-bg-primary);
        }
        .login-card {
            border-radius: 1.5rem !important;
            box-shadow: 0 4px 24px rgba(0,0,0,0.2) !important;
        }
        .login-footer {
            padding-bottom: 2rem;
        }
    }
</style>

<div class="login-overlay">
    <div class="login-shell w-full max-w-sm">
        <div class="login-card-wrap">

            <!-- Card -->
            <div class="login-card bg-[var(--admin-card-bg)] rounded-2xl shadow-xl border border-[var(--admin-border)] overflow-hidden">

                <!-- Gradient top bar -->
                <div class="h-1.5 w-full bg-gradient-to-r from-[#380d0d] to-[#f16b3f]"></div>

                <div class="px-6 py-8">

                    <!-- Logo & heading -->
                    <div class="text-center mb-8">
                        <div class="flex justify-center mb-4">
                            <img
                                src="{{ Vite::asset('resources/images/FASTBITE_LOGO.png') }}"
                                alt="FastBite Logo"
                                class="h-16 w-auto object-contain dark:brightness-110"
                            >
                        </div>
                        <p class="text-xs text-[var(--admin-text-secondary)] tracking-wide font-medium">Welcome back! Please sign in to continue.</p>
                    </div>

                    <!-- Success Flash -->
                    @if(session('success'))
                    <div class="flex items-start gap-2.5 mb-5 p-3.5 bg-green-50/10 border border-green-500/30 text-green-500 text-sm rounded-xl">
                        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    @endif

                    <!-- Error Flash -->
                    @if($errors->has('login_error'))
                    <div class="flex items-start gap-2.5 mb-5 p-3.5 bg-red-50/10 border border-red-500/30 text-red-500 text-sm rounded-xl">
                        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ $errors->first('login_error') }}</span>
                    </div>
                    @endif

                    <!-- Form -->
                    <form method="POST" action="/login" class="space-y-5">
                        @csrf

                        <!-- Username -->
                        <div class="flex flex-col gap-2">
                            <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wider">Username</label>
                            <input
                                type="text"
                                name="username"
                                required
                                autocomplete="username"
                                autocapitalize="none"
                                autocorrect="off"
                                spellcheck="false"
                                value="{{ old('username') }}"
                                placeholder="Enter your username"
                                style="min-height: 50px; font-size: 16px;"
                                class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none transition-all
                                    focus:ring-2 focus:ring-orange-500/20 focus:border-[#F4521E]
                                    bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]
                                    {{ $errors->has('username') ? 'border-red-400' : '' }}"
                            >
                            @error('username')
                                <span class="text-red-500 text-xs flex items-center gap-1 font-medium">
                                    <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="flex flex-col gap-2" x-data="{ show: false }">
                            <label class="font-bold text-[var(--admin-text-secondary)] text-xs uppercase tracking-wider">Password</label>
                            <div class="relative">
                                <input
                                    :type="show ? 'text' : 'password'"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Enter your password"
                                    style="min-height: 50px; font-size: 16px;"
                                    class="w-full px-4 py-3 pr-12 border border-[var(--admin-border)] rounded-xl outline-none transition-all
                                        focus:ring-2 focus:ring-orange-500/20 focus:border-[#F4521E]
                                        bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]
                                        {{ $errors->has('password') ? 'border-red-400' : '' }}"
                                >
                                <!-- Full-height tap zone — easy to hit on touch screens -->
                                <button
                                    type="button"
                                    @click="show = !show"
                                    aria-label="Toggle password visibility"
                                    class="absolute right-0 top-0 h-full w-12 flex items-center justify-center text-[var(--admin-text-secondary)] hover:text-[#F4521E] transition-colors"
                                >
                                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <span class="text-red-500 text-xs flex items-center gap-1 font-medium">
                                    <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Action Buttons Stack -->
                        <div class="flex flex-col gap-3 pt-1">
                            <!-- Submit -->
                            <button
                                type="submit"
                                class="w-full flex items-center justify-center gap-2 text-white font-bold rounded-xl transition-all text-sm active:scale-[0.98] hover:opacity-90 shadow-lg shadow-black/10 cursor-pointer"
                                style="background: linear-gradient(135deg, #F4521E, #3D2B1F); min-height: 52px;"
                            >
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                Sign In
                            </button>

                            <!-- Back Button -->
                            <a
                                href="javascript:history.back()"
                                onclick="if(document.referrer === '') { window.location.href='/'; return false; }"
                                class="w-full h-[50px] flex items-center justify-center gap-2 font-medium rounded-xl border border-[var(--admin-border)] text-[var(--admin-text-secondary)] hover:text-[#F4521E] hover:border-orange-500/30 transition-all text-sm active:scale-[0.98] bg-[var(--admin-card-bg)]"
                            >
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Back to Main
                            </a>
                        </div>
                        
                    </form>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <p class="login-footer text-center text-[10px] text-[var(--admin-text-secondary)] mt-5 pb-4 uppercase tracking-widest select-none opacity-60">
            © 2026 FastBite Restaurant System
        </p>

    </div>
</div>