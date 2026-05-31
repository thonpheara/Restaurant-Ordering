<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ResourceDeletionRequest extends Model
{
    protected $fillable = [
        'requester_id',
        'resource_type',
        'resource_id',
        'resource_name',
        'payload',
        'reason',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'payload' => 'array',
    ];

    public function requester()
    {
        return $this->belongsTo(Logins::class, 'requester_id');
    }

    public function approver()
    {
        return $this->belongsTo(Logins::class, 'approved_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->resource_type) {
            'product' => 'Product',
            'category' => 'Category',
            'order' => 'Order History',
            'reservation' => 'Reservation',
            default => Str::headline((string) $this->resource_type),
        };
    }

    public function getItemNameAttribute(): string
    {
        return (string) $this->resource_name;
    }

    public function getItemContextAttribute(): ?string
    {
        return $this->payload['context'] ?? null;
    }
}
