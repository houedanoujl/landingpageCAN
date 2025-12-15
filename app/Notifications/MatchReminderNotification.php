<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\MatchGame;
use App\Models\Prediction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class MatchReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public MatchGame $match,
        public Prediction $prediction
    ) {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    /**
     * Get the WhatsApp message representation of the notification.
     */
    public function toWhatsApp(object $notifiable): string
    {
        $teamA = $this->match->homeTeam->name ?? $this->match->team_a;
        $teamB = $this->match->awayTeam->name ?? $this->match->team_b;
        $matchDate = $this->match->match_date->format('d/m à H:i');
        $stadium = $this->match->stadium ?? 'Stade non défini';

        $message = "⏰ *Rappel CAN 2025*\n\n";
        $message .= "Le match commence dans 30 minutes !\n\n";
        $message .= "🏆 {$teamA} vs {$teamB}\n";
        $message .= "📅 {$matchDate}\n";
        $message .= "📍 {$stadium}\n\n";
        $message .= "Votre pronostic : {$this->prediction->score_a} - {$this->prediction->score_b}\n\n";
        $message .= "Bonne chance ! 🍀";

        return $message;
    }
}
