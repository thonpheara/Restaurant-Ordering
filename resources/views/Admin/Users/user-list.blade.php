@include('partials.theme-head')
@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
    body { font-family: 'Inter', sans-serif; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #EE6D3C; border-radius: 10px; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
</style>
<title>FastBite | User Management</title>

<div class="bg-[var(--admin-bg-primary)] min-h-screen text-[var(--admin-text-primary)]" x-data="{ mobileMenuOpen: false }">
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
                                class="h-5 w-5 text-[var(--admin-accent)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11c1.657 0 3-1.343 3-3S17.657 5 16 5s-3 1.343-3 3 1.343 3 3 3zM8 11c1.657 0 3-1.343 3-3S9.657 5 8 5 5 6.343 5 8s1.343 3 3 3zm8 2c-2.21 0-4 1.79-4 4v1h8v-1c0-2.21-1.79-4-4-4zM8 13c-2.21 0-4 1.79-4 4v1h8v-1c0-2.21-1.79-4-4-4z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-[var(--admin-text-primary)]">User Management</h2>
                        <span class="text-xs font-bold bg-[var(--admin-bg-primary)] text-[var(--admin-accent)] px-3 py-1 rounded-full border border-[var(--admin-accent)]/20">
                            {{ $users->total() }} users
                        </span>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <form method="GET" action="{{ route('user.index') }}" class="relative w-full md:w-72">
                            <input type="text" name="search"
                                value="{{ request('search') }}"
                                placeholder="Search users..."
                                class="w-full pl-4 pr-10 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]">
                            <button type="submit" class="absolute right-3 top-3.5 text-[var(--admin-text-secondary)] hover:text-[#EE6D3C] transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </form>
                        <a href="{{ route('user.create') }}"
                            class="flex items-center gap-2 bg-gray-900 hover:bg-gray-700 text-white text-sm font-bold px-4 py-3 rounded-xl transition whitespace-nowrap">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add User
                        </a>
                    </div>
                </div>

                <!-- Table -->
                <div class="w-full overflow-x-auto rounded-xl border border-[var(--admin-border)]">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[var(--admin-bg-primary)]">
                            <tr class="border-b border-[var(--admin-border)] text-[var(--admin-text-secondary)] uppercase text-xs">
                                <th class="p-4">ID</th>
                                <th class="p-4">Username</th>
                                <th class="p-4 hidden md:table-cell">Role</th>
                                <th class="p-4 hidden md:table-cell">Status</th>
                                <th class="p-4 hidden md:table-cell">Created At</th>
                                <th class="p-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($users as $user)
                            <tr class="hover:bg-orange-50/50 transition">
                                <td class="p-4 font-medium text-[var(--admin-text-secondary)]">#{{ $user->id }}</td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full text-[#EE6D3C] font-black text-sm flex items-center justify-center flex-shrink-0 border border-[var(--admin-accent)]/20">
                                            {{ strtoupper(substr($user->username, 0, 1)) }}
                                        </div>
                                        <span class="font-semibold text-[var(--admin-text-primary)]">{{ $user->username }}</span>
                                    </div>
                                </td>
                                <td class="p-4 hidden md:table-cell">
                                    @php
                                        $roleColors = [
                                            'Admin' => 'bg-purple-100 text-purple-700',
                                            'User'  => 'bg-blue-100 text-blue-700',
                                        ];
                                        $roleColor = $roleColors[$user->role] ?? 'bg-gray-100 text-gray-600';
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $roleColor }}">
                                        {{ $user->role ?? 'User' }}
                                    </span>
                                </td>
                                <td class="p-4 hidden md:table-cell">
                                    @if($user->isOnline())
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100">
                                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                            Online
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                                            <span class="w-2 h-2 bg-gray-400 rounded-full mr-2"></span>
                                            Offline
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 hidden md:table-cell">
                                    <span class="block text-[var(--admin-text-primary)] text-xs font-medium">
                                        {{ \Carbon\Carbon::parse($user->created_at)->format('d M Y') }}
                                    </span>
                                    <span class="text-[var(--admin-text-secondary)] text-xs">
                                        {{ \Carbon\Carbon::parse($user->created_at)->format('h:i A') }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex justify-center items-center gap-2">
                                        <a href="{{ route('user.edit', $user->id) }}"
                                            class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-800 hover:text-white hover:border-gray-800 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('user.destroy', $user->id) }}" method="POST"
                                              onsubmit="return confirm('Delete user {{ $user->username }}?')">
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
                                <td colspan="6" class="p-10 text-center text-gray-400 italic">No users found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $users->links() }}</div>

            </div>
        </main>
    </div>
</div>