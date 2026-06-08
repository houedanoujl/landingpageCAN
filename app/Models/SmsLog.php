<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $fillable = [
        'to_number',
        'message',
        'status',
        'twilio_sid',
        'error',
        'context',
        'sent_by',
    ];

    /**
     * Admin ayant déclenché l'envoi (si applicable).
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
