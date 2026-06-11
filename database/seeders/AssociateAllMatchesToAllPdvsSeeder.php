<?php

namespace Database\Seeders;

use App\Models\Bar;
use App\Models\MatchGame;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Associe TOUS les matchs à TOUS les PDV partenaires actifs.
 *
 * La relation match ↔ PDV est portée par la table `animations`
 * (bar_id, match_id, unique [bar_id, match_id]). C'est elle que
 * PointsService::awardPredictionVenuePoints consulte pour accorder
 * le bonus +4 : sans association, le bonus est silencieusement refusé.
 *
 * Garanties production :
 *  - Idempotent et relançable : upsert sur la contrainte unique [bar_id, match_id],
 *    aucun doublon possible.
 *  - Aucune suppression ni modification des associations existantes
 *    (les lignes déjà présentes sont laissées telles quelles, y compris
 *    leur animation_time personnalisé).
 *  - Insertion par lots (pas de N requêtes par match).
 *
 * Lancement :
 *   php artisan db:seed --class=AssociateAllMatchesToAllPdvsSeeder
 */
class AssociateAllMatchesToAllPdvsSeeder extends Seeder
{
    public function run(): void
    {
        $matches = MatchGame::query()->get(['id', 'match_date']);
        $barIds = Bar::where('is_active', true)->pluck('id');

        if ($matches->isEmpty() || $barIds->isEmpty()) {
            $this->command->warn('⚠️  Aucun match ou aucun PDV actif : rien à associer.');
            return;
        }

        $before = DB::table('animations')->count();
        $now = now();

        $matches->chunk(50)->each(function ($chunk) use ($barIds, $now) {
            $rows = [];
            foreach ($chunk as $match) {
                foreach ($barIds as $barId) {
                    $rows[] = [
                        'bar_id' => $barId,
                        'match_id' => $match->id,
                        'animation_date' => $match->match_date,
                        'animation_time' => $match->match_date?->format('H') . ' H',
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // insertOrIgnore : la contrainte unique [bar_id, match_id] fait
            // ignorer les associations déjà présentes (y compris désactivées
            // volontairement) — seules les manquantes sont insérées.
            DB::table('animations')->insertOrIgnore($rows);
        });

        $after = DB::table('animations')->count();

        $this->command->info(sprintf(
            '✅ Association terminée : %d matchs × %d PDV actifs — %d associations créées (%d → %d).',
            $matches->count(),
            $barIds->count(),
            $after - $before,
            $before,
            $after
        ));
    }
}
