<?php

namespace Database\Seeders;

use App\Models\MatchGame;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TestMatchSeeder extends Seeder
{
    /**
     * Matchs TEST (relatifs à la date du seed) pour tester les pronostics
     * avant le coup d'envoi de la Coupe du Monde 2026.
     *
     * Idempotents : identifiés par match_number (998, 999).
     * Le décompte de la page d'accueil reste calé sur le début de la Coupe
     * du Monde (config game.world_cup_start), pas sur ces matchs test.
     */
    public function run(): void
    {
        $testMatches = [
            [
                'num'   => 999,
                'home'  => 'Senegal',
                'away'  => 'France',
                'date'  => Carbon::tomorrow()->setTime(20, 0, 0),       // dès demain 20h00 UTC
            ],
            [
                'num'   => 998,
                'home'  => 'Brazil',
                'away'  => 'Morocco',
                'date'  => Carbon::now()->addHours(3)->startOfHour(),   // dans ~3h, jouable tout de suite
            ],
        ];

        foreach ($testMatches as $data) {
            $home = Team::where('name', $data['home'])->first();
            $away = Team::where('name', $data['away'])->first();

            $match = MatchGame::updateOrCreate(
                ['match_number' => $data['num']],
                [
                    'home_team_id' => $home?->id,
                    'away_team_id' => $away?->id,
                    'team_a'       => $home?->name ?? $data['home'],
                    'team_b'       => $away?->name ?? $data['away'],
                    'match_date'   => $data['date'],
                    'phase'        => 'group_stage',
                    'group_name'   => 'TEST',
                    'stadium'      => 'Match Test',
                    'status'       => 'scheduled',
                ]
            );

            $verb = $match->wasRecentlyCreated ? 'créé' : 'mis à jour';
            $this->command->info("🧪 Match test {$verb} : {$match->team_a} vs {$match->team_b} le {$data['date']->format('d/m/Y H:i')} UTC");
        }
    }
}
