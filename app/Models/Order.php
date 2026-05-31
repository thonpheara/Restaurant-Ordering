<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class Order extends Model
{
    protected $fillable = [
        'table_number',
        'reservation_name',
        'total_amount',
        'status',
        'user_id',
        'prep_time_min',
        'prep_time_max',
        'estimated_ready_at',
    ];

    protected $casts = [
        'estimated_ready_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(Logins::class, 'user_id');
    }

    public function getTrackingWindowAttribute(): ?string
    {
        if ($this->prep_time_min && $this->prep_time_max) {
            return $this->prep_time_min . '-' . $this->prep_time_max . ' min';
        }

        if ($this->prep_time_max) {
            return $this->prep_time_max . ' min';
        }

        return null;
    }

    public function getTrackingTargetAttribute(): string
    {
        return $this->reservation_name ?: ($this->table_number ?: 'Walk-in Order');
    }

    public function getRemainingMinutesAttribute(): ?int
    {
        if (!$this->estimated_ready_at instanceof Carbon) {
            return null;
        }

        return now()->diffInMinutes($this->estimated_ready_at, false);
    }

    public function resolveAutoStatus(?Carbon $referenceTime = null): string
    {
        $referenceTime ??= now();
        $currentStatus = strtolower((string) $this->status);

        if (in_array($currentStatus, ['cancelled', 'completed'], true)) {
            return $currentStatus;
        }

        if (!$this->created_at || !$this->estimated_ready_at instanceof Carbon) {
            return $currentStatus ?: 'pending';
        }

        $startTime = $this->created_at instanceof Carbon
            ? $this->created_at
            : Carbon::parse($this->created_at);

        $totalMinutes = max(1, $startTime->diffInMinutes($this->estimated_ready_at));

        if ($referenceTime->greaterThanOrEqualTo($this->estimated_ready_at)) {
            return 'completed';
        }

        $elapsedMinutes = max(0, $startTime->diffInMinutes($referenceTime, false));

        if ($elapsedMinutes >= max(1, (int) ceil($totalMinutes * 0.35))) {
            return 'processing';
        }

        if ($elapsedMinutes >= 1) {
            return 'confirmed';
        }

        return 'pending';
    }

    public function syncAutoStatus(bool $save = true): string
    {
        $resolvedStatus = $this->resolveAutoStatus();

        if ($resolvedStatus !== $this->status) {
            $this->status = $resolvedStatus;

            if ($save && $this->exists) {
                $this->save();
            }
        }

        return $resolvedStatus;
    }

    public static function notificationsFor(?Logins $user, int $limit = 6): Collection
    {
        if (!$user) {
            return collect();
        }

        $query = static::with('user')->latest();
        $query->where('status', 'completed');

        if (!$user->isSuperAdmin()) {
            $query->where('user_id', $user->id);
        }

        return $query
            ->take($limit)
            ->get()
            ->map(function (self $order) use ($user) {
                $customerName = $order->user?->username ?? 'Guest';
                $formattedTotal = number_format((float) $order->total_amount, 2);

                return [
                    'id' => $order->id,
                    'title' => 'Order #' . $order->id,
                    'message' => $user->isSuperAdmin()
                        ? "{$customerName}'s order is completed for \${$formattedTotal}"
                        : "Your order is completed. Total: \${$formattedTotal}",
                    'customer' => $customerName,
                    'status' => ucfirst((string) $order->status),
                    'time' => $order->created_at?->diffForHumans() ?? 'Just now',
                    'created_at' => $order->created_at?->toIso8601String(),
                ];
            })
            ->values();
    }
}
