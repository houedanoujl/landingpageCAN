<?php

namespace Database\Seeders;

use App\Models\MatchGame;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TestMatchSeeder extends Seeder
{
    /**
     * Match TEST commençant « dès demain » (relatif à la date du seed).
     *
     * Permet de tester les pronostics avant le coup d'envoi de la Coupe du
     * Monde 2026. Idempotent : identifié par match_number = 999.
     * Le décompte de la page d'accueil reste calé sur le début de la Coupe
     * du Monde (config game.world_cup_start), pas sur ce match test.
     */
    const TEST_MATCH_NUMBER = 999;

    public function run(): void
    {
        // Deux équipes réelles déjà présentes (groupe I) pour un match jouable
        $home = Team::where('name', 'Senegal')->first();
        $away = Team::where('name', 'France')->first();

        // Demain à 20h00 UTC, relatif à l'exécution du seed
        $matchDate = Carbon::tomorrow()->setTime(20, 0, 0);

        $match = MatchGame::updateOrCreate(
            ['match_number' => self::TEST_MATCH_NUMBER],
            [
                'home_team_id' => $home?->id,
                'away_team_id' => $away?->id,
                'team_a'       => $home?->name ?? 'Senegal',
                'team_b'       => $away?->name ?? 'France',
                'match_date'   => $matchDate,
                'phase'        => 'group_stage',
                'group_name'   => 'TEST',
                'stadium'      => 'Match Test',
                'status'       => 'scheduled',
            ]
        );

        $verb = $match->wasRecentlyCreated ? 'créé' : 'mis à jour';
        $this->command->info("🧪 Match test {$verb} : {$match->team_a} vs {$match->team_b} le {$matchDate->format('d/m/Y H:i')} UTC");
    }
}
