<script>
    window.toggleTheme = function() {
        const isDark = document.documentElement.classList.toggle('dark');
        const theme = isDark ? 'dark' : 'light';
        localStorage.setItem('theme', theme);
        window.dispatchEvent(new CustomEvent('theme-changed', { detail: theme }));
    };
</script>
@php
    use Illuminate\Support\Facades\Auth;

    $authUser = Auth::user();
    $isAdmin  = $authUser?->hasAdminAccess() ?? false;
    $notificationRoute = $authUser ? route('notifications.orders') : null;

    // ── Navigation items ──────────────────────────────────────────────
    $navItems = [

        //── Main ──────────────────────────────────────────────────────
        [
            'group' => 'Main',
            'label' => 'Dashboard',
            'route' => $isAdmin ? 'dashboard.index' : 'user.dashboard',
            'match' => 'dashboard.*',
            'icon'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10
                        a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011
                        1v4a1 1 0 001 1m-6 0h6',
        ],

        //── Catalog ───────────────────────────────────────────────────
        [
            'group' => 'Catalog',
            'label' => 'Products',
            'route' => 'allproduct.index',
            'match' => 'allproduct.*',
            'icon'  => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        ],
        [
            'group' => 'Catalog',
            'label' => 'Categories',
            'route' => 'allcategory.index',
            'match' => 'allcategory.*',
            'icon'  => 'M4 6h16M4 10h16M4 14h16M4 18h16',
        ],
        [
            'group' => 'Catalog',
            'label' => 'Menu',
            'route' => 'menu.index',
            'match' => 'menu.*',
            'icon'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0
                        00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2
                        2 0 012 2',
        ],

        // ── Operations ────────────────────────────────────────────────
        [
            'group' => 'Operations',
            'label' => 'Reservations',
            'route' => 'reservations.index',
            'match' => 'reservations.*',
            'icon'  => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9
                        0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1',
        ],
        [
            'group' => 'Operations',
            'label' => 'Reports',
            'route' => 'report.index',
            'match' => 'report.*',
            'icon'  => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586
                        a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        ],

        // ── Admin only ────────────────────────────────────────────────
        [
            'group'     => 'General',
            'label'     => 'Users',
            'route'     => 'user.index',
            'match'     => 'user.*',
            'adminOnly' => true,
            'icon'      => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0
                            0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        ],
        [
            'group'     => 'General',
            'label'     => 'Deletion Requests',
            'route'     => 'deletion-requests.index',
            'match'     => 'deletion-requests.*',
            'adminOnly' => true,
            'icon'      => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0
                            01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1
                            1 0 00-1 1v3M4 7h16',
        ],
        [
            'group' => 'General',
            'label' => 'Settings',
            'route' => 'setting.index',
            'match' => 'setting.*',
            'icon'  => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0
                        002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065
                        2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066
                        2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572
                        1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573
                        -1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065
                        -2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066
                        -2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07
                        2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        ],
    ];

    $grouped = collect($navItems)->groupBy('group');
@endphp

<style>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

[x-cloak] {
    display: none !important;
}
</style>

<div
    x-data="sidebarNotifications({
        initialNotifications: @js(($orderNotifications ?? collect())->values()),
        notificationUrl: @js($notificationRoute),
        userId: {{ $authUser?->id ?? 0 }}
    })"
    x-init="init()"
    class="relative overflow-visible"
>

    {{-- ── Mobile Header ───────────────────────────────── --}}
    <header class="md:hidden sticky top-0 z-30 bg-[var(--admin-card-bg)] border-b border-[var(--admin-border)] shadow-sm">
        <div class="flex items-center justify-between px-4 py-3">

            {{-- Hamburger --}}
            <button @click="open = true"
                class="p-2 rounded-xl text-gray-500 hover:bg-orange-50 hover:text-[#EE6D3C] transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Logo --}}
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/FASTBITE_LOGO.png') }}" alt="Logo" class="w-8 h-8 object-contain">
                <span class="text-base font-black text-gray-800 tracking-tight">
                    Fast<span class="text-[#EE6D3C]">Bite</span>
                </span>
            </div>

            <div class="flex items-center gap-2">
                {{-- Mobile Notification Bell --}}
                <button
                    type="button"
                    @click="toggleNotifications()"
                    class="relative p-2 rounded-xl text-[var(--admin-text-secondary)] hover:bg-orange-50/10 hover:text-[#EE6D3C] transition-all duration-200"
                    title="Order notifications"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span
                        x-show="unreadCount() > 0"
                        x-cloak
                        class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-[#EE6D3C] text-white text-[10px] font-black flex items-center justify-center"
                        x-text="unreadCount()"
                    ></span>
                </button>

                {{-- Avatar --}}
                <div class="w-8 h-8 rounded-full bg-[#FFE4DB] text-[#EE6D3C] font-black text-sm flex items-center justify-center flex-shrink-0">
                    {{ strtoupper(substr($authUser->username ?? 'A', 0, 1)) }}
                </div>
            </div>
        </div>
    </header>

    {{-- ── Centered Notification Modal (shared mobile + desktop) ──────── --}}
    <div
        x-show="notificationOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/40"
        @click.self="notificationOpen = false"
    >
        <div
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="w-full max-w-sm bg-[var(--admin-card-bg)] border border-[var(--admin-border)] rounded-2xl shadow-2xl overflow-hidden"
        >
            {{-- Header --}}
            <div class="px-4 py-3 border-b border-[var(--admin-border)] flex items-center justify-between">
                <div>
                    <p class="text-sm font-black text-[var(--admin-text-primary)]">Order Alerts</p>
                    <p class="text-[11px] text-[var(--admin-text-secondary)]">Same alerts sent to Telegram</p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="fetchNotifications(true)"
                        class="text-xs font-bold text-[#EE6D3C] hover:text-orange-600 transition"
                    >
                        Refresh
                    </button>
                    <button
                        type="button"
                        @click="notificationOpen = false"
                        class="p-1 rounded-lg text-[var(--admin-text-secondary)] hover:bg-[var(--admin-bg-primary)] hover:text-[var(--admin-text-primary)] transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- List --}}
            <div class="max-h-80 overflow-y-auto no-scrollbar">
                <template x-if="notifications.length === 0">
                    <div class="px-4 py-8 text-center text-sm text-gray-400">
                        No order alerts yet.
                    </div>
                </template>

                <template x-for="item in notifications" :key="item.id">
                    <div class="px-4 py-3 border-b border-[var(--admin-border)] last:border-b-0">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-orange-50/10 text-[#EE6D3C] flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-bold text-[var(--admin-text-primary)]" x-text="item.title"></p>
                                    <span class="text-[10px] text-[var(--admin-text-secondary)] whitespace-nowrap" x-text="item.time"></span>
                                </div>
                                <p class="text-xs text-[var(--admin-text-secondary)] mt-1" x-text="item.message"></p>
                                <div class="mt-2 inline-flex items-center gap-2 text-[10px] font-bold uppercase tracking-wide text-[#EE6D3C] bg-orange-50/10 px-2.5 py-1 rounded-full">
                                    <span x-text="item.status"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Footer --}}
            <div class="p-3 bg-[var(--admin-bg-primary)] border-t border-[var(--admin-border)]">
                <a
                    href="{{ route('menu.index') }}"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#EE6D3C] text-white text-sm font-bold hover:bg-orange-600 transition"
                >
                    Open Orders
                </a>
            </div>
        </div>
    </div>

    {{-- Overlay (mobile sidebar) --}}
    <div x-show="open" @click="open = false" x-transition.opacity class="fixed inset-0 bg-black/50 z-30 md:hidden"></div>

    {{-- Sidebar --}}
    <aside :class="open ? 'translate-x-0' : '-translate-x-full'"
        class="fixed md:static inset-y-0 left-0 z-40
               w-64 h-full bg-[var(--admin-sidebar-bg)] rounded-lg shadow-sm border border-[var(--admin-border)]
               flex flex-col transform transition-transform duration-300 ease-in-out
               md:translate-x-0 overflow-visible">

        {{-- Logo --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-orange-50">
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/FASTBITE_LOGO.png') }}"
                     alt="Logo" class="w-10 h-10 object-contain">
                <span class="text-lg font-black text-[var(--admin-text-primary)] tracking-tight">
                    Fast<span class="text-[#EE6D3C]">Bite</span>
                </span>
            </div>

            <div class="flex items-center gap-2">
                {{-- Desktop Notification Bell --}}
                <button
                    type="button"
                    @click="toggleNotifications()"
                    class="relative hidden md:flex p-2 rounded-xl text-gray-500 hover:bg-orange-50 hover:text-[#EE6D3C] transition-all duration-200"
                    title="Order notifications"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span
                        x-show="unreadCount() > 0"
                        x-cloak
                        class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-[#EE6D3C] text-white text-[10px] font-black flex items-center justify-center"
                        x-text="unreadCount()"
                    ></span>
                </button>

                <button @click="open = false" class="md:hidden p-1 rounded-lg hover:bg-gray-100 text-gray-500 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto no-scrollbar">
            @foreach($grouped as $groupName => $items)
                @if(!$loop->first)
                    <div class="pt-2 pb-1 border-t border-gray-100"></div>
                @endif
                <p class="px-4 pb-1 pt-1 text-[10px] font-black uppercase tracking-widest text-[var(--admin-text-secondary)]">
                    {{ $groupName }}
                </p>

                @foreach($items as $item)
                    @php
                        if (!empty($item['adminOnly']) && !$isAdmin) continue;
                        $active = request()->routeIs($item['match']);
                    @endphp

                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold text-sm transition-all duration-200
                           {{ $active
                               ? 'bg-[var(--admin-aside-active-bg)] text-[var(--admin-aside-active-text)]'
                               : 'text-[var(--admin-text-secondary)] hover:bg-orange-50/10 hover:text-[#EE6D3C]' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                        </svg>
                        <span class="flex-1">{{ $item['label'] }}</span>

                        @if($active)
                            <span class="w-1.5 h-1.5 rounded-full bg-white/60 ml-auto"></span>
                        @endif
                    </a>
                @endforeach
            @endforeach
        </nav>

        {{-- Bottom --}}
        <div class="px-4 py-4 border-t border-[var(--admin-border)] space-y-2">
            {{-- Theme Toggle --}}
            <button @click="toggleTheme()"
                class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold text-[var(--admin-text-secondary)] hover:bg-orange-50/10 hover:text-[#EE6D3C] transition-all duration-200">
                <template x-if="theme === 'dark'">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span>Light Mode</span>
                    </div>
                </template>
                <template x-if="theme !== 'dark'">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <span>Dark Mode</span>
                    </div>
                </template>
            </button>

            {{-- User info --}}
            <div class="flex items-center gap-3 px-3 py-2.5 bg-[var(--admin-bg-primary)] rounded-xl border border-[var(--admin-border)]">
                <div class="w-8 h-8 rounded-full bg-[#EE6D3C]/20 text-[#EE6D3C] font-black text-sm flex items-center justify-center flex-shrink-0 border border-[#EE6D3C]/30">
                    {{ strtoupper(substr($authUser->username ?? 'A', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-[var(--admin-text-primary)] truncate">{{ $authUser->username ?? 'Admin' }}</p>
                    <p class="text-[10px] text-[var(--admin-text-secondary)] truncate">{{ $authUser->role ?? 'Admin' }}</p>
                </div>
                <span class="w-2 h-2 rounded-full bg-green-400 flex-shrink-0" title="Online"></span>
            </div>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold
                           text-[var(--admin-text-secondary)] hover:bg-red-00/10 hover:text-red-400 transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>
</div>

<script>
function sidebarNotifications(config) {
    return {
        open: false,
        notificationOpen: false,
        notifications: config.initialNotifications || [],
        notificationUrl: config.notificationUrl,
        userId: config.userId || 0,
        lastSeenId: 0,
        theme: localStorage.getItem('theme') || 'light',

        init() {
            this.restoreSeenState();

            if (this.notificationUrl) {
                this.fetchNotifications();
                setInterval(() => this.fetchNotifications(), 20000);
            }
        },

        toggleTheme() {
            window.toggleTheme();
            this.theme = localStorage.getItem('theme');
        },

        storageKey() {
            return 'fastbite:last-seen-order-notification:' + this.userId;
        },

        restoreSeenState() {
            this.lastSeenId = Number(window.localStorage.getItem(this.storageKey()) || 0);
        },

        unreadCount() {
            return this.notifications.filter(item => Number(item.id) > Number(this.lastSeenId)).length;
        },

        markNotificationsRead() {
            const latestId = this.notifications.length ? Number(this.notifications[0].id) : this.lastSeenId;
            this.lastSeenId = Math.max(Number(this.lastSeenId), latestId);
            window.localStorage.setItem(this.storageKey(), String(this.lastSeenId));
        },

        toggleNotifications() {
            this.notificationOpen = !this.notificationOpen;

            if (this.notificationOpen) {
                this.markNotificationsRead();
            }
        },

        async fetchNotifications(forceMarkRead = false) {
            if (!this.notificationUrl) return;

            try {
                const response = await fetch(this.notificationUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) return;

                const data = await response.json();
                this.notifications = data.notifications || [];

                if (forceMarkRead || this.notificationOpen) {
                    this.markNotificationsRead();
                }
            } catch (error) {
                console.error('Failed to load order notifications.', error);
            }
        },
    };
}
</script>