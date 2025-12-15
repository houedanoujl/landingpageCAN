<?php

namespace App\Services;

use App\Models\MatchGame;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TournamentService
{
    /**
     * Qualifier automatiquement les équipes pour la phase à élimination directe
     * basé sur les résultats de la phase de poules.
     */
    public function qualifyTeamsFromGroupStage()
    {
        try {
            DB::beginTransaction();

            // Récupérer tous les groupes
            $groups = MatchGame::where('phase', 'group_stage')
                ->whereNotNull('group_name')
                ->distinct()
                ->pluck('group_name');

            $qualifiedTeams = [];

            foreach ($groups as $groupName) {
                // Calculer le classement du groupe
                $groupStandings = $this->calculateGroupStandings($groupName);

                // Les 2 premiers de chaque groupe se qualifient (CAN = 6 groupes de 4)
                // Total: 12 équipes qualifiées + 4 meilleurs 3èmes = 16 équipes
                if (count($groupStandings) >= 2) {
                    $qualifiedTeams[$groupName] = [
                        'first' => $groupStandings[0],
                        'second' => $groupStandings[1],
                        'third' => $groupStandings[2] ?? null,
                    ];
                }
            }

            // Sélectionner les 4 meilleurs 3èmes
            $thirdPlaceTeams = collect($qualifiedTeams)
                ->pluck('third')
                ->filter()
                ->sortByDesc('points')
                ->take(4)
                ->values();

            Log::info('Équipes qualifiées', [
                'premiers' => collect($qualifiedTeams)->pluck('first')->count(),
                'seconds' => collect($qualifiedTeams)->pluck('second')->count(),
                'meilleurs_troisiemes' => $thirdPlaceTeams->count(),
            ]);

            DB::commit();

            return [
                'qualified_teams' => $qualifiedTeams,
                'best_thirds' => $thirdPlaceTeams,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la qualification des équipes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculer le classement d'un groupe.
     */
    public function calculateGroupStandings($groupName)
    {
        // Récupérer tous les matchs terminés du groupe
        $matches = MatchGame::where('group_name', $groupName)
            ->where('phase', 'group_stage')
            ->where('status', 'finished')
            ->whereNotNull('score_a')
            ->whereNotNull('score_b')
            ->get();

        // Récupérer toutes les équipes du groupe
        $teamIds = $matches->pluck('home_team_id')
            ->merge($matches->pluck('away_team_id'))
            ->unique()
            ->filter();

        $standings = [];

        foreach ($teamIds as $teamId) {
            $team = Team::find($teamId);
            if (!$team) continue;

            $teamMatches = $matches->filter(function ($match) use ($teamId) {
                return $match->home_team_id == $teamId || $match->away_team_id == $teamId;
            });

            $stats = [
                'team_id' => $teamId,
                'team_name' => $team->name,
                'played' => $teamMatches->count(),
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_difference' => 0,
                'points' => 0,
            ];

            foreach ($teamMatches as $match) {
                $isHome = $match->home_team_id == $teamId;
                $teamScore = $isHome ? $match->score_a : $match->score_b;
                $opponentScore = $isHome ? $match->score_b : $match->score_a;

                $stats['goals_for'] += $teamScore;
                $stats['goals_against'] += $opponentScore;

                if ($teamScore > $opponentScore) {
                    $stats['won']++;
                    $stats['points'] += 3;
                } elseif ($teamScore == $opponentScore) {
                    $stats['drawn']++;
                    $stats['points'] += 1;
                } else {
                    $stats['lost']++;
                }
            }

            $stats['goal_difference'] = $stats['goals_for'] - $stats['goals_against'];
            $standings[] = $stats;
        }

        // Trier par: points > différence de buts > buts marqués
        usort($standings, function ($a, $b) {
            if ($a['points'] != $b['points']) {
                return $b['points'] - $a['points'];
            }
            if ($a['goal_difference'] != $b['goal_difference']) {
                return $b['goal_difference'] - $a['goal_difference'];
            }
            return $b['goals_for'] - $a['goals_for'];
        });

        return $standings;
    }

    /**
     * Générer automatiquement les matchs de 1/8e de finale
     * selon le tableau officiel de la CAN.
     */
    public function generateRoundOf16Matches($qualifiedTeams, $bestThirds)
    {
        // Configuration du tableau de 1/8e de finale CAN
        // Format: [match_number => [team_position_1, team_position_2]]
        $bracket = [
            1 => ['1A', '3C/D/E/F'],  // Match 1: 1er Groupe A vs 3e meilleur (C/D/E/F)
            2 => ['2A', '2C'],         // Match 2: 2e Groupe A vs 2e Groupe C
            3 => ['1B', '3A/C/D/E'],   // Match 3: 1er Groupe B vs 3e meilleur (A/C/D/E)
            4 => ['2B', '2D'],         // Match 4: 2e Groupe B vs 2e Groupe D
            5 => ['1C', '3A/B/E/F'],   // Match 5: 1er Groupe C vs 3e meilleur (A/B/E/F)
            6 => ['2C', '2E'],         // Match 6: 2e Groupe C vs 2e Groupe E
            7 => ['1D', '3A/B/C/F'],   // Match 7: 1er Groupe D vs 3e meilleur (A/B/C/F)
            8 => ['2D', '2F'],         // Match 8: 2e Groupe D vs 2e Groupe F
        ];

        $matches = [];

        foreach ($bracket as $matchNumber => $positions) {
            $matches[] = [
                'match_number' => $matchNumber,
                'phase' => 'round_of_16',
                'team_1_position' => $positions[0],
                'team_2_position' => $positions[1],
                'bracket_position' => $matchNumber,
                'display_order' => $matchNumber,
            ];
        }

        return $matches;
    }

    /**
     * Mettre à jour l'équipe dans un match à élimination directe
     * quand son match parent est terminé.
     */
    public function updateKnockoutMatchTeams(MatchGame $finishedMatch)
    {
        if ($finishedMatch->status !== 'finished') {
            return;
        }

        $winnerId = $finishedMatch->winner_team_id;
        if (!$winnerId) {
            Log::warning('Match terminé sans gagnant (égalité?)', ['match_id' => $finishedMatch->id]);
            return;
        }

        $winner = Team::find($winnerId);
        if (!$winner) {
            return;
        }

        // Trouver les matchs enfants
        $childMatches = $finishedMatch->childMatches();

        foreach ($childMatches as $childMatch) {
            // Déterminer si c'est parent1 ou parent2
            if ($childMatch->parent_match_1_id == $finishedMatch->id) {
                // Ce match alimente l'équipe à domicile du match enfant
                $childMatch->update([
                    'home_team_id' => $winnerId,
                    'team_a' => $winner->name,
                ]);
                Log::info('Équipe qualifiée (home)', [
                    'from_match' => $finishedMatch->id,
                    'to_match' => $childMatch->id,
                    'team' => $winner->name,
                ]);
            } elseif ($childMatch->parent_match_2_id == $finishedMatch->id) {
                // Ce match alimente l'équipe extérieure du match enfant
                $childMatch->update([
                    'away_team_id' => $winnerId,
                    'team_b' => $winner->name,
                ]);
                Log::info('Équipe qualifiée (away)', [
                    'from_match' => $finishedMatch->id,
                    'to_match' => $childMatch->id,
                    'team' => $winner->name,
                ]);
            }
        }
    }

    /**
     * Créer le tableau complet du tournoi à élimination directe.
     */
    public function createKnockoutBracket()
    {
        DB::beginTransaction();

        try {
            // 1. Créer la FINALE
            $final = MatchGame::create([
                'phase' => 'final',
                'match_number' => 1,
                'bracket_position' => 1,
                'display_order' => 100,
                'team_a' => 'TBD',
                'team_b' => 'TBD',
                'stadium' => 'À définir',
                'match_date' => now()->addDays(45), // À ajuster
                'status' => 'scheduled',
            ]);

            // 2. Créer le match pour la 3e place
            $thirdPlace = MatchGame::create([
                'phase' => 'third_place',
                'match_number' => 1,
                'bracket_position' => 1,
                'display_order' => 99,
                'team_a' => 'TBD',
                'team_b' => 'TBD',
                'stadium' => 'À définir',
                'match_date' => now()->addDays(44), // Avant la finale
                'status' => 'scheduled',
            ]);

            // 3. Créer les DEMI-FINALES
            $semi1 = MatchGame::create([
                'phase' => 'semi_final',
                'match_number' => 1,
                'bracket_position' => 1,
                'display_order' => 51,
                'team_a' => 'TBD',
                'team_b' => 'TBD',
                'stadium' => 'À définir',
                'match_date' => now()->addDays(40),
                'status' => 'scheduled',
                'winner_goes_to' => 'home',
            ]);

            $semi2 = MatchGame::create([
                'phase' => 'semi_final',
                'match_number' => 2,
                'bracket_position' => 2,
                'display_order' => 52,
                'team_a' => 'TBD',
                'team_b' => 'TBD',
                'stadium' => 'À définir',
                'match_date' => now()->addDays(40),
                'status' => 'scheduled',
                'winner_goes_to' => 'away',
            ]);

            // Lier les demi-finales à la finale
            $final->update([
                'parent_match_1_id' => $semi1->id,
                'parent_match_2_id' => $semi2->id,
            ]);

            // Lier les perdants des demi-finales au match pour la 3e place
            // Note: Cela nécessiterait une logique supplémentaire pour les perdants

            // 4. Créer les QUARTS DE FINALE (4 matchs)
            $quarters = [];
            for ($i = 1; $i <= 4; $i++) {
                $quarters[$i] = MatchGame::create([
                    'phase' => 'quarter_final',
                    'match_number' => $i,
                    'bracket_position' => $i,
                    'display_order' => 40 + $i,
                    'team_a' => 'TBD',
                    'team_b' => 'TBD',
                    'stadium' => 'À définir',
                    'match_date' => now()->addDays(35),
                    'status' => 'scheduled',
                ]);
            }

            // Lier les quarts aux demi-finales
            $semi1->update([
                'parent_match_1_id' => $quarters[1]->id,
                'parent_match_2_id' => $quarters[2]->id,
            ]);

            $semi2->update([
                'parent_match_1_id' => $quarters[3]->id,
                'parent_match_2_id' => $quarters[4]->id,
            ]);

            // 5. Créer les 1/8e DE FINALE (8 matchs)
            $roundOf16 = [];
            for ($i = 1; $i <= 8; $i++) {
                $roundOf16[$i] = MatchGame::create([
                    'phase' => 'round_of_16',
                    'match_number' => $i,
                    'bracket_position' => $i,
                    'display_order' => 30 + $i,
                    'team_a' => 'TBD',
                    'team_b' => 'TBD',
                    'stadium' => 'À définir',
                    'match_date' => now()->addDays(30),
                    'status' => 'scheduled',
                ]);
            }

            // Lier les 1/8e aux quarts
            $quarters[1]->update([
                'parent_match_1_id' => $roundOf16[1]->id,
                'parent_match_2_id' => $roundOf16[2]->id,
            ]);

            $quarters[2]->update([
                'parent_match_1_id' => $roundOf16[3]->id,
                'parent_match_2_id' => $roundOf16[4]->id,
            ]);

            $quarters[3]->update([
                'parent_match_1_id' => $roundOf16[5]->id,
                'parent_match_2_id' => $roundOf16[6]->id,
            ]);

            $quarters[4]->update([
                'parent_match_1_id' => $roundOf16[7]->id,
                'parent_match_2_id' => $roundOf16[8]->id,
            ]);

            DB::commit();

            Log::info('Tableau à élimination directe créé avec succès');

            return [
                'final' => $final,
                'third_place' => $thirdPlace,
                'semi_finals' => [$semi1, $semi2],
                'quarter_finals' => $quarters,
                'round_of_16' => $roundOf16,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du tableau à élimination directe', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
