<?php

namespace App\Console\Commands;

use App\Models\MatchGame;
use App\Models\MatchNotification;
use App\Notifications\MatchReminderNotification;
use Illuminate\Console\Command;

class Send30MinReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:send-match-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Send WhatsApp reminders 30 minutes before matches';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Recherche des matchs commençant dans 25-35 minutes...');

        // Matchs commençant dans 25-35 minutes
        $upcomingMatches = MatchGame::with(['homeTeam', 'awayTeam', 'predictions.user'])
            ->where('status', 'scheduled')
            ->whereBetween('match_date', [
                now()->addMinutes(25),
                now()->addMinutes(35)
            ])
            ->get();

        if ($upcomingMatches->isEmpty()) {
            $this->info('✅ Aucun match à venir dans cette fenêtre de temps.');
            return 0;
        }

        $this->info("📢 {$upcomingMatches->count()} match(s) trouvé(s)");

        $sentCount = 0;
        $skippedCount = 0;

        foreach ($upcomingMatches as $match) {
            $matchName = ($match->homeTeam->name ?? $match->team_a) . ' vs ' . ($match->awayTeam->name ?? $match->team_b);
            $this->line("⚽ {$matchName}");

            foreach ($match->predictions as $prediction) {
                // Vérifier si déjà envoyé
                $exists = MatchNotification::where('user_id', $prediction->user_id)
                    ->where('match_id', $match->id)
                    ->where('notification_type', '30_min_reminder')
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
                        'notification_type' => '30_min_reminder',
                        'status' => 'pending'
                    ]);

                    // Envoyer la notification
                    $prediction->user->notify(new MatchReminderNotification($match, $prediction));

                    $sentCount++;
                    $this->line("   ✓ Rappel envoyé à {$prediction->user->name}");
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
