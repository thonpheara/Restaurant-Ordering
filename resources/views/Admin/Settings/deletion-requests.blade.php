@include('partials.theme-head')
@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
    body { font-family: 'Inter', sans-serif; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: var(--admin-accent); border-radius: 10px; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
</style>
<title>FastBite | Deletion Requests</title>

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
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[var(--admin-accent)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6M18 9l3 3-3 3"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-[var(--admin-text-primary)]">Account Deletion Requests</h2>
                        <span class="text-xs font-bold bg-[var(--admin-bg-primary)] text-[var(--admin-accent)] px-3 py-1 rounded-full border border-[var(--admin-accent)]/20">Admin</span>
                    </div>

                    <form method="POST" action="{{ route('deletion-requests.delete-all-approved') }}"
                        onsubmit="return confirm('Are you sure you want to delete all approved requests? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete All Approved
                        </button>
                    </form>
                </div>

                <!-- Requests Table -->
                <div class="w-full overflow-x-auto rounded-xl border border-[var(--admin-border)]">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-[var(--admin-bg-primary)]">
                            <tr class="border-b border-[var(--admin-border)] text-[var(--admin-text-secondary)] uppercase text-xs">
                                <th class="p-4">User</th>
                                <th class="p-4">Reason</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 hidden md:table-cell">Requested At</th>
                                <th class="p-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($accountDeletionRequests as $request)
                                <tr class="hover:bg-orange-50/50 transition">
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-[#FFE4DB] text-[#EE6D3C] font-black text-sm flex items-center justify-center flex-shrink-0">
                                                {{ strtoupper(substr($request->user->username ?? 'D', 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-[var(--admin-text-primary)]">{{ $request->user->username ?? 'Deleted User' }}</p>
                                                <p class="text-xs text-[var(--admin-text-secondary)]">{{ $request->user->email ?? '' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 text-[var(--admin-text-secondary)]">
                                        {{ $request->reason ?? 'No reason provided' }}
                                    </td>
                                    <td class="p-4">
                                        <span class="px-3 py-1 text-xs font-bold rounded-full
                                            {{ $request->status === 'pending' ? 'bg-yellow-100/10 text-yellow-500 border border-yellow-500/20' :
                                               ($request->status === 'approved' ? 'bg-green-100/10 text-green-500 border border-green-500/20' : 'bg-red-100/10 text-red-500 border border-red-500/20') }}">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </td>
                                    <td class="p-4 hidden md:table-cell text-[var(--admin-text-secondary)]">
                                        {{ $request->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="p-4">
                                        @if($request->status === 'pending')
                                            <div class="flex gap-2">
                                                <form method="POST" action="{{ route('deletion-requests.approve', $request->id) }}" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-green-500 text-white text-xs font-semibold rounded-lg hover:bg-green-600 transition">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        Approve
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('deletion-requests.deny', $request->id) }}" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-red-500 text-white text-xs font-semibold rounded-lg hover:bg-red-600 transition">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                        Deny
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="flex items-center gap-1.5 text-xs">
                                                @if($request->status === 'approved')
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span class="text-green-600 font-medium">Approved</span>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span class="text-red-600 font-medium">Denied</span>
                                                @endif
                                                @if($request->approved_at)
                                                    <span class="text-gray-400">on {{ $request->approved_at->format('M d, Y') }}</span>
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-10 text-center text-gray-400 italic">No deletion requests found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($accountDeletionRequests->hasPages())
                    <div class="mt-6">
                        {{ $accountDeletionRequests->links() }}
                    </div>
                @endif

            </div>

            <div class="bg-[var(--admin-card-bg)] rounded-lg shadow-sm border border-[var(--admin-border)] p-6 md:p-8 mt-3 md:mt-0 mb-8">

                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[var(--admin-accent)]/10 flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[var(--admin-accent)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h11M9 12h11M9 17h11M5 7h.01M5 12h.01M5 17h.01"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-[var(--admin-text-primary)]">Recent Deletion Requests</h2>
                        <span class="text-xs font-bold bg-[var(--admin-bg-primary)] text-[var(--admin-accent)] px-3 py-1 rounded-full border border-[var(--admin-accent)]/20">Products, Categories, Orders, Reservations</span>
                    </div>

                    <form method="POST" action="{{ route('resource-deletion-requests.delete-all-approved') }}"
                        onsubmit="return confirm('Are you sure you want to delete all approved requests? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete All Approved
                        </button>
                    </form>
                </div>

                <!-- Requests Table -->
                <div class="w-full overflow-x-auto rounded-xl border border-[var(--admin-border)]">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-[var(--admin-bg-primary)]">
                            <tr class="border-b border-[var(--admin-border)] text-[var(--admin-text-secondary)] uppercase text-xs">
                                <th class="p-4">Requested By</th>
                                <th class="p-4">Item</th>
                                <th class="p-4">Type</th>
                                <th class="p-4">Reason</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 hidden md:table-cell">Requested At</th>
                                <th class="p-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($resourceDeletionRequests as $request)
                                <tr class="hover:bg-orange-50/50 transition">
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-[#FFE4DB] text-[#EE6D3C] font-black text-sm flex items-center justify-center flex-shrink-0">
                                                {{ strtoupper(substr($request->requester->username ?? 'D', 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-[var(--admin-text-primary)]">{{ $request->requester->username ?? 'Deleted User' }}</p>
                                                <p class="text-xs text-[var(--admin-text-secondary)]">{{ $request->requester->role ?? 'User' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div>
                                            <p class="font-semibold text-[var(--admin-text-primary)]">{{ $request->item_name }}</p>
                                            <p class="text-xs text-[var(--admin-text-secondary)]">{{ $request->item_context ?? 'Resource record' }}</p>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        @php
                                            $typeClasses = match($request->resource_type) {
                                                'product' => 'bg-orange-100/10 text-orange-500 border border-orange-500/20',
                                                'category' => 'bg-blue-100/10 text-blue-500 border border-blue-500/20',
                                                'order' => 'bg-purple-100/10 text-purple-500 border border-purple-500/20',
                                                'reservation' => 'bg-pink-100/10 text-pink-500 border border-pink-500/20',
                                                default => 'bg-gray-100/10 text-gray-500 border border-gray-500/20',
                                            };
                                        @endphp
                                        <span class="px-3 py-1 text-xs font-bold rounded-full {{ $typeClasses }}">
                                            {{ $request->type_label }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-[var(--admin-text-secondary)]">
                                        {{ $request->reason ?? 'No reason provided' }}
                                    </td>
                                    <td class="p-4">
                                        <span class="px-3 py-1 text-xs font-bold rounded-full
                                            {{ $request->status === 'pending' ? 'bg-yellow-100/10 text-yellow-500 border border-yellow-500/20' :
                                               ($request->status === 'approved' ? 'bg-green-100/10 text-green-500 border border-green-500/20' : 'bg-red-100/10 text-red-500 border border-red-500/20') }}">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </td>
                                    <td class="p-4 hidden md:table-cell text-[var(--admin-text-secondary)]">
                                        {{ $request->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="p-4">
                                        @if($request->status === 'pending')
                                            <div class="flex gap-2">
                                                <form method="POST" action="{{ route('resource-deletion-requests.approve', $request->id) }}" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-green-500 text-white text-xs font-semibold rounded-lg hover:bg-green-600 transition">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        Approve
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('resource-deletion-requests.deny', $request->id) }}" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="flex items-center gap-1.5 px-3 py-1.5 bg-red-500 text-white text-xs font-semibold rounded-lg hover:bg-red-600 transition">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                        Deny
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="flex items-center gap-1.5 text-xs">
                                                @if($request->status === 'approved')
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span class="text-green-600 font-medium">Approved</span>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span class="text-red-600 font-medium">Denied</span>
                                                @endif
                                                @if($request->approved_at)
                                                    <span class="text-gray-400">on {{ $request->approved_at->format('M d, Y') }}</span>
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-10 text-center text-gray-400 italic">No resource deletion requests found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($resourceDeletionRequests->hasPages())
                    <div class="mt-6">
                        {{ $resourceDeletionRequests->links() }}
                    </div>
                @endif

            </div>
        </main>
    </div>
</div>