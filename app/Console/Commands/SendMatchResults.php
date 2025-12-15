<?php

namespace App\Console\Commands;

use App\Models\MatchGame;
use App\Models\MatchNotification;
use App\Notifications\MatchResultNotification;
use Illuminate\Console\Command;

class SendMatchResults extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:send-match-results';

    /**
     * The console command description.
     */
    protected $description = 'Send match results to users who made predictions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Recherche des matchs terminés récemment...');

        // Matchs terminés récemment (derniers 10 minutes)
        $finishedMatches = MatchGame::with(['homeTeam', 'awayTeam', 'predictions.user'])
            ->where('status', 'finished')
            ->where('updated_at', '>=', now()->subMinutes(10))
            ->get();

        if ($finishedMatches->isEmpty()) {
            $this->info('✅ Aucun match terminé récemment.');
            return 0;
        }

        $this->info("📢 {$finishedMatches->count()} match(s) terminé(s)");

        $sentCount = 0;
        $skippedCount = 0;

        foreach ($finishedMatches as $match) {
            $matchName = ($match->homeTeam->name ?? $match->team_a) . ' vs ' . ($match->awayTeam->name ?? $match->team_b);
            $this->line("⚽ {$matchName} ({$match->score_a}-{$match->score_b})");

            foreach ($match->predictions as $prediction) {
                // Vérifier si déjà envoyé
                $exists = MatchNotification::where('user_id', $prediction->user_id)
                    ->where('match_id', $match->id)
                    ->where('notification_type', 'post_match_result')
                    ->exists();

                if ($exists) {
                    $skippedCount++;
                    continue;
                }

                try {
                    // Créer l'enregistrement de notification
                    MatchNotification::create([
                        'user_id' => $prediction->user_id,
                        'match_id' => $match->id,
                        'notification_type' => 'post_match_result',
                        'status' => 'pending'
                    ]);

                    // Envoyer la notification avec les points
                    $pointsEarned = $prediction->points_earned ?? 0;
                    $prediction->user->notify(new MatchResultNotification(
                        $match,
                        $prediction,
                        $pointsEarned
                    ));

                    $sentCount++;
                    $this->line("   ✓ Résultat envoyé à {$prediction->user->name} ({$pointsEarned} pts)");
                } catch (\Exception $e) {
                    $this->error("   ✗ Erreur pour {$prediction->user->name}: {$e->getMessage()}");
                }
            }
        }

        $this->newLine();
        $this->info("📊 Résumé:");
        $this->info("   • {$sentCount} notification(s) envoyée(s)");
        $this->info("   • {$skippedCount} notification(s) déjà envoyée(s)");

        return 0;
    }
}
