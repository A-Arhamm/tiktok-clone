<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', // The user who receives the notification
        'type',    // Type of notification, e.g. 'follow'
        'data',    // JSON data for notification details
        'read_at', // Timestamp when notification was read
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function getDataAttribute($value)
    {
        return is_string($value) ? json_decode($value, true) : $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
