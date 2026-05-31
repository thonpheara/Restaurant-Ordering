<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Logins extends Authenticatable
{
    use Notifiable;

    protected $table = 'logins'; 

    protected $fillable = [
        'username',
        'password',
        'role',
        'last_seen',
    ];

    protected $casts = [
        'last_seen' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }

    public function isOnline()
    {
        return $this->last_seen && $this->last_seen->diffInMinutes(now()) < 5;
    }

    public function hasAdminAccess(): bool
    {
        return strcasecmp((string) $this->role, 'Admin') === 0;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasAdminAccess() && strcasecmp((string) $this->username, 'admin') === 0;
    }
}
