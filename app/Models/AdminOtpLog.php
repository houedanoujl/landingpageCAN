<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminOtpLog extends Model
{
    protected $fillable = [
        'phone',
        'code',
        'status',
        'whatsapp_number',
        'verification_attempts',
        'otp_sent_at',
        'otp_verified_at',
        'error_message',
    ];

    protected $casts = [
        'otp_sent_at' => 'datetime',
        'otp_verified_at' => 'datetime',
    ];
}
