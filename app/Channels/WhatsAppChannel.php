<?php

namespace App\Channels;

use App\Services\WhatsAppService;
use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct(protected WhatsAppService $whatsAppService)
    {
    }

    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): array
    {
        // Get the WhatsApp message from the notification
        $message = $notification->toWhatsApp($notifiable);

        if (!$message) {
            return ['success' => false, 'error' => 'No message to send'];
        }

        // Get the user's WhatsApp number
        $phoneNumber = $notifiable->routeNotificationForWhatsapp();

        if (!$phoneNumber) {
            return ['success' => false, 'error' => 'No phone number available'];
        }

        // Send the message using WhatsAppService
        return $this->whatsAppService->sendMessage($phoneNumber, $message);
    }
}
