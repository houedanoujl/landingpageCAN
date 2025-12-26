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
        'role',
        'points_total',
        'last_login_at',
        'last_daily_reward_at',
        'password',
        'otp_code',
        'otp_expires_at',
        'phone_verified',
        'firebase_uid',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'last_login_at' => 'datetime',
        'last_daily_reward_at' => 'date',
        'otp_expires_at' => 'datetime',
        'phone_verified' => 'boolean',
    ];

    /**
     * Route notifications for the WhatsApp channel.
     */
    public function routeNotificationForWhatsapp(): string
    {
        // Utiliser le WhatsAppService pour formater le numéro
        return app(\App\Services\WhatsAppService::class)->formatWhatsAppNumber($this->phone);
    }
}
