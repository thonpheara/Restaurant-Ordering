@include('partials.theme-head')
@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
    body { font-family: 'Inter', sans-serif; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #EE6D3C; border-radius: 10px; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
</style>
<title>FastBite | Create Reservation</title>

@php
    $initialTime = old('time', isset($reservation) ? \Illuminate\Support\Str::of($reservation->time)->substr(0, 5) : '');
    $initialDate = old('date', $reservation->date ?? '');
    $initialTable = old('table_id', $reservation->table_id ?? '');
    $initialGuests = old('guest_count', $reservation->guest_count ?? 1);
    $initialSlotAvailabilityActive = old('date') && old('time');
@endphp

<div class="bg-[var(--admin-bg-primary)] min-h-screen text-[var(--admin-text-primary)]"
    x-data="reservationForm({
        selectedTable: '{{ $initialTable }}',
        guestCount: '{{ $initialGuests }}',
        selectedDate: '{{ $initialDate }}',
        selectedTime: '{{ $initialTime }}',
        slotAvailabilityActive: {{ Js::from($initialSlotAvailabilityActive) }},
        reservations: {{ Js::from($tableReservations ?? []) }},
        currentReservationId: {{ Js::from($reservation->id ?? null) }},
    })"
    x-init="initialize()">
    <div class="flex flex-col md:flex-row md:h-screen md:p-4 md:gap-6 md:overflow-hidden relative">

        @include('components.asidebar')

        <main class="flex-1 overflow-y-auto px-3 pb-4 md:px-0 md:pr-2 custom-scrollbar">
            <div class="bg-[var(--admin-card-bg)] rounded-lg shadow-sm border border-[var(--admin-border)] p-6 md:p-8 mt-3 md:mt-0 mb-8">

                @include('components.alerts')
                <div x-effect="syncSelectedTable()"></div>

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
                                d="M8 7V3m8 4V3m-9 8h10m-5 4v6m3-3H9m-4 3h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-[var(--admin-text-primary)]">
                        {{ isset($reservation) ? 'Edit Reservation' : 'Create New Reservation' }}
                    </h2>
                    <span class="text-xs font-bold bg-[var(--admin-bg-primary)] text-[#EE6D3C] px-3 py-1 rounded-full border border-[#EE6D3C]/20">
                        {{ isset($reservation) ? 'Editing' : 'New' }}
                    </span>
                </div>

                <form action="{{ isset($reservation) ? route('reservations.update', $reservation->id) : route('reservations.store') }}"
                      method="POST">
                    @csrf
                    @if(isset($reservation)) @method('PUT') @endif

                    <div class="flex flex-col lg:flex-row gap-8">

                        <div class="w-full lg:w-2/5 flex flex-col gap-5">

                            <div class="flex flex-col gap-2">
                                <label class="font-bold text-[var(--admin-text-secondary)] text-sm uppercase tracking-wide">Full Name *</label>
                                <input type="text" name="full_name" required
                                    value="{{ old('full_name', $reservation->full_name ?? '') }}"
                                    placeholder="Enter full name"
                                    class="w-full px-4 py-3 border border-[var(--admin-border)] bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition
                                        @error('full_name') border-red-400 @enderror">
                                @error('full_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                             <div class="grid grid-cols-2 gap-4">
                                 <div class="flex flex-col gap-2">
                                    <label class="font-bold text-[var(--admin-text-secondary)] text-sm uppercase tracking-wide">Phone Number *</label>
                                    <input type="tel" name="phone_number" required
                                        value="{{ old('phone_number', $reservation->phone_number ?? '') }}"
                                        placeholder="+1 234 567 8900"
                                        class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]
                                            @error('phone_number') border-red-400 @enderror">
                                    @error('phone_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-bold text-[var(--admin-text-secondary)] text-sm uppercase tracking-wide">Guests *</label>
                                    <input type="number" name="guest_count" required min="1" max="20"
                                        value="{{ $initialGuests }}"
                                        x-model="guestCount"
                                        class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]
                                            @error('guest_count') border-red-400 @enderror">
                                    @error('guest_count') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                 <div class="flex flex-col gap-2">
                                    <label class="font-bold text-[var(--admin-text-secondary)] text-sm uppercase tracking-wide">Date *</label>
                                    <input type="date" name="date" required
                                        value="{{ $initialDate }}"
                                        x-model="selectedDate"
                                        @input="activateSlotAvailability()"
                                        class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]
                                            @error('date') border-red-400 @enderror">
                                    @error('date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-bold text-[var(--admin-text-secondary)] text-sm uppercase tracking-wide">Time *</label>
                                    <input type="time" name="time" required
                                        value="{{ $initialTime }}"
                                        x-model="selectedTime"
                                        @input="activateSlotAvailability()"
                                        class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm transition bg-[var(--admin-card-bg)] text-[var(--admin-text-primary)]
                                            @error('time') border-red-400 @enderror">
                                    @error('time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="font-bold text-[var(--admin-text-secondary)] text-sm uppercase tracking-wide">Selected Table *</label>
                                <div class="relative">
                                    <input type="hidden" name="table_id" x-model="selectedTable">
                                    <input type="text" required readonly
                                        value="{{ $initialTable ? 'Table ' . $initialTable : '' }}"
                                        :value="selectedTable ? 'Table ' + selectedTable : ''"
                                        placeholder="Click a table on the right →"
                                        class="w-full px-4 py-3 border border-[var(--admin-border)] rounded-xl outline-none focus:ring-2 focus:ring-orange-200 text-sm bg-[var(--admin-bg-primary)] cursor-default transition"
                                        :class="selectedTable ? 'border-orange-500 bg-orange-500/10 text-[#EE6D3C] font-black' : 'text-[var(--admin-text-secondary)]'">
                                    <template x-if="selectedTable">
                                        <span class="absolute right-4 top-1/2 -translate-y-1/2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#EE6D3C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </span>
                                    </template>
                                </div>
                                <p class="text-xs text-[var(--admin-text-secondary)]" x-show="!selectedDate || !selectedTime">
                                    Table colors are shown immediately. Choose date and time to check the exact slot.
                                </p>
                                <p class="text-xs font-semibold text-[var(--admin-text-secondary)]" x-show="selectedDate && selectedTime && selectedTable && !shouldUseSlotAvailability()">
                                    Table <span x-text="selectedTable"></span> is selected. Change the date or time to check the exact reservation slot.
                                </p>
                                <p class="text-xs font-semibold text-emerald-600" x-show="shouldUseSlotAvailability() && selectedTable">
                                    Table <span x-text="selectedTable"></span> is free for the selected reservation slot.
                                </p>
                                @error('table_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                        </div>

                        <div class="w-full lg:flex-1 bg-[var(--admin-bg-primary)] border border-[var(--admin-border)] rounded-2xl p-6 sm:p-8">
                            <div class="flex flex-wrap items-center gap-3 mb-6">
                                <h3 class="font-bold text-[var(--admin-text-secondary)] text-sm uppercase tracking-wide">Select Table</h3>
                                <span class="text-xs font-bold bg-[var(--admin-bg-primary)] text-[#EE6D3C] px-3 py-1 rounded-full border border-[#EE6D3C]/20"
                                    x-text="availableTablesCount() + ' Free'"></span>
                                <span class="text-xs font-bold bg-red-50 text-red-500 px-3 py-1 rounded-full border border-red-200"
                                    x-text="reservedTablesCount() + ' Reserved'"></span>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 mb-8 text-xs font-semibold">
                                <span class="inline-flex items-center gap-2 text-[var(--admin-text-secondary)]">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    Free
                                </span>
                                <span class="inline-flex items-center gap-2 text-[var(--admin-text-secondary)]">
                                    <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                                    Reserved
                                </span>
                                <span class="inline-flex items-center gap-2 text-[var(--admin-text-secondary)]">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#EE6D3C]"></span>
                                    Selected
                                </span>
                            </div>
                            <div class="mb-6 rounded-2xl border border-dashed border-[var(--admin-border)] bg-[var(--admin-card-bg)] px-4 py-3 text-sm text-[var(--admin-text-secondary)]"
                                :class="shouldUseSlotAvailability() ? 'border-emerald-200 bg-emerald-50/40 text-emerald-700' : ''">
                                <span x-show="!shouldUseSlotAvailability()">
                                    Colors are shown immediately. Pick date and time to check the exact reservation slot for each table.
                                </span>
                                <span x-show="shouldUseSlotAvailability()">
                                    Live availability for <span class="font-bold" x-text="selectedDate"></span> at <span class="font-bold" x-text="formatTime(selectedTime)"></span>.
                                </span>
                            </div>
                            <div class="grid grid-cols-3 gap-y-12 gap-x-6 place-items-center">
                                @for ($i = 1; $i <= 9; $i++)
                                    @php
                                        // Simple logic to vary table sizes visually
                                        $isLarge = in_array($i, [1, 5, 9]);
                                        $capacity = $isLarge ? 4 : 2;
                                    @endphp
                                    <button type="button"
                                         @click="selectTable({{ $i }})"
                                         title="Table {{ $i }} ({{ $capacity }} seats)"
                                         class="group relative rounded-xl shadow-sm flex items-center justify-center transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-orange-300"
                                         style="width: {{ $isLarge ? '72px' : '64px' }}; height: {{ $isLarge ? '72px' : '64px' }};"
                                         :class="selectedTable == '{{ $i }}'
                                             ? 'bg-[#EE6D3C] border border-orange-500 scale-110 shadow-lg z-10'
                                             : (tableStatus({{ $i }}) === 'reserved'
                                                ? 'bg-red-100 border border-red-300 shadow-sm cursor-not-allowed'
                                                : (tableStatus({{ $i }}) === 'available'
                                                    ? 'bg-emerald-100 border border-emerald-300 shadow-sm hover:scale-105 hover:shadow-md hover:bg-emerald-200'
                                                    : 'bg-white border border-transparent hover:scale-105 hover:shadow-md hover:bg-orange-50'))">
                                        
                                        {{-- Chairs visual decoration --}}
                                        <div class="absolute -top-3 left-1/4 w-1/2 h-2 rounded-t-full transition-colors"
                                             :class="selectedTable == '{{ $i }}'
                                                ? 'bg-orange-300'
                                                : (tableStatus({{ $i }}) === 'reserved'
                                                    ? 'bg-red-300'
                                                    : (tableStatus({{ $i }}) === 'available'
                                                        ? 'bg-emerald-300'
                                                        : 'bg-[var(--admin-border)] group-hover:bg-orange-200'))"></div>
                                        <div class="absolute -bottom-3 left-1/4 w-1/2 h-2 rounded-b-full transition-colors"
                                             :class="selectedTable == '{{ $i }}'
                                                ? 'bg-orange-300'
                                                : (tableStatus({{ $i }}) === 'reserved'
                                                    ? 'bg-red-300'
                                                    : (tableStatus({{ $i }}) === 'available'
                                                        ? 'bg-emerald-300'
                                                        : 'bg-[var(--admin-border)] group-hover:bg-orange-200'))"></div>
                                        
                                        @if($isLarge)
                                            <div class="absolute -left-3 top-1/4 w-2 h-1/2 rounded-l-full transition-colors"
                                                 :class="selectedTable == '{{ $i }}'
                                                    ? 'bg-orange-300'
                                                    : (tableStatus({{ $i }}) === 'reserved'
                                                        ? 'bg-red-300'
                                                        : (tableStatus({{ $i }}) === 'available'
                                                            ? 'bg-emerald-300'
                                                            : 'bg-[var(--admin-border)] group-hover:bg-orange-200'))"></div>
                                            <div class="absolute -right-3 top-1/4 w-2 h-1/2 rounded-r-full transition-colors"
                                                 :class="selectedTable == '{{ $i }}'
                                                    ? 'bg-orange-300'
                                                    : (tableStatus({{ $i }}) === 'reserved'
                                                        ? 'bg-red-300'
                                                        : (tableStatus({{ $i }}) === 'available'
                                                            ? 'bg-emerald-300'
                                                            : 'bg-[var(--admin-border)] group-hover:bg-orange-200'))"></div>
                                        @endif

                                        <div class="flex flex-col items-center leading-tight">
                                            <span class="font-bold text-xl transition-colors"
                                                  :class="selectedTable == '{{ $i }}'
                                                    ? 'text-white'
                                                    : (tableStatus({{ $i }}) === 'reserved'
                                                        ? 'text-red-700'
                                                        : (tableStatus({{ $i }}) === 'available'
                                                            ? 'text-emerald-700'
                                                            : 'text-[var(--admin-text-primary)]'))">
                                                {{ $i }}
                                            </span>
                                            <span class="text-[8px] font-bold uppercase tracking-tighter transition-colors"
                                                  :class="selectedTable == '{{ $i }}'
                                                    ? 'text-orange-200'
                                                    : (tableStatus({{ $i }}) === 'reserved'
                                                        ? 'text-red-500'
                                                        : (tableStatus({{ $i }}) === 'available'
                                                            ? 'text-emerald-500'
                                                            : 'text-[var(--admin-text-secondary)] group-hover:text-orange-300'))">
                                                {{ $capacity }} Seats
                                            </span>
                                            <span class="mt-1 rounded-full px-2 py-0.5 text-[8px] font-black uppercase tracking-[0.18em] transition-colors"
                                                  :class="selectedTable == '{{ $i }}'
                                                    ? 'bg-orange-400/30 text-orange-50'
                                                    : (tableStatus({{ $i }}) === 'reserved'
                                                        ? 'bg-red-200 text-red-700'
                                                        : (tableStatus({{ $i }}) === 'available'
                                                            ? 'bg-emerald-200 text-emerald-700'
                                                            : 'bg-slate-100 text-[var(--admin-text-secondary)]'))"
                                                  x-text="tableStatusLabel({{ $i }})"></span>
                                        </div>
                                    </button>
                                @endfor
                            </div>
                        </div>

                    </div>

                    <div class="flex flex-col sm:flex-row justify-end gap-3 mt-8 pt-6 border-t border-[var(--admin-border)]">
                        <a href="{{ route('reservations.index') }}"
                            class="px-6 py-3 border border-[var(--admin-border)] text-[var(--admin-text-secondary)] font-bold rounded-xl hover:bg-orange-50/10 transition text-sm text-center">
                            Cancel
                        </a>
                        <button type="submit"
                            class="flex items-center gap-2 px-6 py-3 bg-[#EE6D3C] hover:bg-orange-600 text-white font-bold rounded-xl shadow-lg transition text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ isset($reservation) ? 'Update Reservation' : 'Confirm Reservation' }}
                        </button>
                    </div>

                </form>
            </div>
        </main>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('reservationForm', (config) => ({
            ...config,
            initialize() {
                this.selectedDate = this.selectedDate || '';
                this.selectedTime = this.normalizeTime(this.selectedTime);
                this.selectedTable = this.selectedTable ? String(this.selectedTable) : '';
                this.guestCount = this.guestCount || '1';
                this.slotAvailabilityActive = Boolean(this.slotAvailabilityActive);
            },
            shouldUseSlotAvailability() {
                return Boolean(this.slotAvailabilityActive && this.selectedDate && this.selectedTime);
            },
            activateSlotAvailability() {
                this.slotAvailabilityActive = Boolean(this.selectedDate && this.selectedTime);
            },
            normalizeTime(value) {
                return String(value || '').slice(0, 5);
            },
            formatTime(value) {
                const normalized = this.normalizeTime(value);

                if (!normalized) {
                    return '--:--';
                }

                const [hours, minutes] = normalized.split(':');

                return `${hours}:${minutes}`;
            },
            tableReservation(tableId) {
                return this.reservations.find((reservation) => (
                    Number(reservation.table_id) === Number(tableId)
                    && (!this.shouldUseSlotAvailability() || reservation.date === this.selectedDate)
                    && (!this.shouldUseSlotAvailability() || this.normalizeTime(reservation.time) === this.normalizeTime(this.selectedTime))
                    && Number(reservation.id) !== Number(this.currentReservationId)
                )) || null;
            },
            tableStatus(tableId) {
                return this.tableReservation(tableId) ? 'reserved' : 'available';
            },
            tableStatusLabel(tableId) {
                const status = this.tableStatus(tableId);

                if (status === 'reserved') {
                    return 'Reserved';
                }

                if (status === 'available') {
                    return 'Free';
                }

                return 'Free';
            },
            availableTablesCount() {
                return Array.from({ length: 9 }, (_, index) => index + 1)
                    .filter((tableId) => this.tableStatus(tableId) === 'available')
                    .length;
            },
            reservedTablesCount() {
                return Array.from({ length: 9 }, (_, index) => index + 1)
                    .filter((tableId) => this.tableStatus(tableId) === 'reserved')
                    .length;
            },
            selectTable(tableId) {
                if (!this.selectedDate || !this.selectedTime) {
                    return;
                }

                this.activateSlotAvailability();

                if (this.tableStatus(tableId) === 'reserved') {
                    return;
                }

                this.selectedTable = String(tableId);
            },
            syncSelectedTable() {
                if (this.shouldUseSlotAvailability() && this.selectedTable && this.tableStatus(this.selectedTable) === 'reserved') {
                    this.selectedTable = '';
                }
            },
        }));
    });
</script>
