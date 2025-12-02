<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'is_admin',
        'points_total',
        'last_login_at',
        'password',
        'otp_code',
        'otp_expires_at',
        'phone_verified',
        'firebase_uid',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'last_login_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'phone_verified' => 'boolean',
    ];
}
