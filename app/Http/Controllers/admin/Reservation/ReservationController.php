<?php

namespace App\Http\Controllers\Admin\Reservation;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\ResourceDeletionRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function index() {
        $user = auth()->user();
        $query = Reservation::query();

        if (!$user->isSuperAdmin()) {
            $query->where('user_id', $user->id);
        }

        $reservations = $query->latest()->paginate(5); // changed 10 → 5
        return view('Admin.Reservations.table-list', compact('reservations'));
    }

    public function create() {
        return view('Admin.Reservations.table-form', [
            'tableReservations' => $this->getTableReservations(),
        ]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'full_name'    => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'date'         => 'required|date',
            'time'         => 'required',
            'guest_count'  => 'required|integer|min:1|max:20',
            'table_id'     => 'required|integer',
        ]);

        $this->ensureTableIsAvailable($validated['date'], $validated['time'], $validated['table_id']);

        $validated['user_id'] = auth()->id();

        Reservation::create($validated);

        return redirect()->route('reservations.index')->with('success', 'Table reserved successfully!');
    }

    public function edit($id) {
        $reservation = Reservation::findOrFail($id);
        return view('Admin.Reservations.table-form', [
            'reservation' => $reservation,
            'tableReservations' => $this->getTableReservations(),
        ]);
    }

    public function update(Request $request, $id) {
        $reservation = Reservation::findOrFail($id);

        $validated = $request->validate([
            'full_name'    => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'date'         => 'required|date',
            'time'         => 'required',
            'guest_count'  => 'required|integer|min:1|max:20',
            'table_id'     => 'required|integer',
        ]);

        $this->ensureTableIsAvailable($validated['date'], $validated['time'], $validated['table_id'], $reservation->id);

        $reservation->update($validated);

        return redirect()->route('reservations.index')
                         ->with('success', 'Table updated successfully');
    }

    public function destroy($id) {
        $reservation = Reservation::findOrFail($id);

        $result = app(ResourceDeletionRequestService::class)->submit([
            'requester_id' => auth()->id(),
            'resource_type' => 'reservation',
            'resource_id' => $reservation->id,
            'resource_name' => $reservation->full_name,
            'payload' => ['context' => 'Table ' . $reservation->table_id],
            'reason' => null,
        ]);

        if (!$result['created']) {
            return redirect()->route('reservations.index')
                ->withErrors(['error' => 'A pending deletion request already exists for this reservation.']);
        }

        return redirect()->route('reservations.index')->with('success', 'Reservation deletion request submitted for admin approval.');
    }

    public function search(Request $request) {
        $query = $request->input('query');
        $user = auth()->user();

        $reservationsQuery = Reservation::query()
            ->where('full_name', 'like', "%{$query}%")
            ->orWhere('phone_number', 'like', "%{$query}%");

        if (!$user->isSuperAdmin()) {
            $reservationsQuery->where('user_id', $user->id);
        }

        $reservations = $reservationsQuery
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view('Admin.Reservations.table-list', compact('reservations', 'query'));
    }

    private function getTableReservations()
    {
        return Reservation::query()
            ->select(['id', 'full_name', 'date', 'time', 'table_id'])
            ->orderBy('date')
            ->orderBy('time')
            ->get()
            ->map(fn (Reservation $reservation) => [
                'id' => $reservation->id,
                'full_name' => $reservation->full_name,
                'date' => $reservation->date,
                'time' => Carbon::parse($reservation->time)->format('H:i:s'),
                'table_id' => (int) $reservation->table_id,
            ]);
    }

    private function ensureTableIsAvailable(string $date, string $time, int $tableId, ?int $ignoreReservationId = null): void
    {
        $conflictExists = Reservation::query()
            ->when($ignoreReservationId, fn ($query) => $query->whereKeyNot($ignoreReservationId))
            ->whereDate('date', $date)
            ->whereTime('time', Carbon::parse($time)->format('H:i:s'))
            ->where('table_id', $tableId)
            ->exists();

        if ($conflictExists) {
            throw ValidationException::withMessages([
                'table_id' => 'This table is already reserved for the selected date and time.',
            ]);
        }
    }
}
