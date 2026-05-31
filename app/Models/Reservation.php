<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations'; 

    protected $fillable = [
        'full_name',
        'phone_number',
        'date',
        'time',
        'guest_count',
        'table_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(Logins::class, 'user_id');
    }
}