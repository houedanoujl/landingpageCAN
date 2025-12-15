<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\MatchGame;
use App\Models\Prediction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class MatchResultNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public MatchGame $match,
        public Prediction $prediction,
        public int $pointsEarned
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

        $message = "🏁 *Match terminé !*\n\n";
        $message .= "🏆 {$teamA} {$this->match->score_a} - {$this->match->score_b} {$teamB}\n\n";
        $message .= "Votre pronostic : {$this->prediction->score_a} - {$this->prediction->score_b}\n\n";
        $message .= "🎯 *Points gagnés : {$this->pointsEarned} pts*\n\n";

        // Détail des points
        $breakdown = $this->getPointsBreakdown();
        $message .= $breakdown . "\n\n";
        $message .= "Continuez comme ça ! 💪";

        return $message;
    }

    /**
     * Get the breakdown of points earned.
     */
    private function getPointsBreakdown(): string
    {
        $breakdown = "Détails :\n";
        $breakdown .= "• +1 pt participation\n";

        // Vérifier si le vainqueur a été correctement prédit
        $actualWinner = $this->getActualWinner();
        if ($this->prediction->predicted_winner === $actualWinner) {
            $breakdown .= "• +3 pts bon vainqueur\n";
        }

        // Vérifier si le score est exact
        if ($this->prediction->score_a === $this->match->score_a &&
            $this->prediction->score_b === $this->match->score_b) {
            $breakdown .= "• +3 pts score exact";
        }

        return $breakdown;
    }

    /**
     * Determine the actual winner of the match.
     */
    private function getActualWinner(): string
    {
        if ($this->match->score_a > $this->match->score_b) {
            return 'home';
        } elseif ($this->match->score_b > $this->match->score_a) {
            return 'away';
        }
        return 'draw';
    }
}
