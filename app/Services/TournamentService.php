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

            // Récupérer les groupes officiels (A-L pour la Coupe du Monde 2026,
            // en excluant les groupes de test type "TEST")
            $groups = MatchGame::where('phase', 'group_stage')
                ->whereNotNull('group_name')
                ->distinct()
                ->pluck('group_name')
                ->filter(fn ($g) => preg_match('/^[A-L]$/', $g))
                ->sort()
                ->values();

            $qualifiedTeams = [];

            foreach ($groups as $groupName) {
                // Calculer le classement du groupe
                $groupStandings = $this->calculateGroupStandings($groupName);

                // Coupe du Monde 2026 : 12 groupes de 4, les 2 premiers se qualifient
                // (24 équipes) + les 8 meilleurs 3èmes = 32 équipes en 1/16e de finale
                if (count($groupStandings) >= 2) {
                    $qualifiedTeams[$groupName] = [
                        'first' => $groupStandings[0],
                        'second' => $groupStandings[1],
                        'third' => $groupStandings[2] ?? null,
                    ];

                    // Remplir automatiquement les slots "1X" / "2X" du bracket
                    // (uniquement si la propagation auto est activée ET que
                    // tous les matchs du groupe sont terminés). Par défaut
                    // l'admin place les équipes à la main.
                    if (config('game.auto_bracket_propagation', false) && $this->isGroupFinished($groupName)) {
                        $this->fillBracketSlot('1' . $groupName, $groupStandings[0]['team_id']);
                        $this->fillBracketSlot('2' . $groupName, $groupStandings[1]['team_id']);
                    }
                }
            }

            // Sélectionner les 8 meilleurs 3èmes (critères FIFA : points,
            // différence de buts, buts marqués). Leur affectation aux slots
            // "3X/Y/Z" du bracket suit la table officielle FIFA et reste
            // à faire manuellement par l'admin (bouton "Qualifier une équipe").
            $thirdPlaceTeams = collect($qualifiedTeams)
                ->pluck('third')
                ->filter()
                ->sort(function ($a, $b) {
                    return [$b['points'], $b['goal_difference'], $b['goals_for']]
                        <=> [$a['points'], $a['goal_difference'], $a['goals_for']];
                })
                ->take(8)
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
     * Indique si tous les matchs d'un groupe sont terminés.
     */
    public function isGroupFinished(string $groupName): bool
    {
        return !MatchGame::where('phase', 'group_stage')
            ->where('group_name', $groupName)
            ->where('status', '!=', 'finished')
            ->exists();
    }

    /**
     * Remplit un slot du bracket identifié par son code FIFA
     * ("1A", "2B", "W73", "L101", ...) avec l'équipe donnée.
     * Ne touche que les matchs à élimination directe dont le slot
     * porte encore le code (jamais d'écrasement d'une équipe déjà placée).
     */
    public function fillBracketSlot(string $code, int $teamId): void
    {
        $team = Team::find($teamId);
        if (!$team) {
            return;
        }

        $knockoutPhases = ['round_of_32', 'round_of_16', 'quarter_final', 'semi_final', 'third_place', 'final'];

        MatchGame::whereIn('phase', $knockoutPhases)
            ->where('team_a', $code)
            ->get()
            ->each(function (MatchGame $m) use ($teamId, $team, $code) {
                $m->update(['home_team_id' => $teamId, 'team_a' => $team->name]);
                Log::info('Slot bracket rempli (home)', ['code' => $code, 'match_id' => $m->id, 'team' => $team->name]);
            });

        MatchGame::whereIn('phase', $knockoutPhases)
            ->where('team_b', $code)
            ->get()
            ->each(function (MatchGame $m) use ($teamId, $team, $code) {
                $m->update(['away_team_id' => $teamId, 'team_b' => $team->name]);
                Log::info('Slot bracket rempli (away)', ['code' => $code, 'match_id' => $m->id, 'team' => $team->name]);
            });
    }

    /**
     * Détermine le vainqueur et le perdant d'un match terminé,
     * en tenant compte des tirs au but (colonne `winner` si score égal).
     *
     * @return array{0:int|null,1:int|null} [winnerTeamId, loserTeamId]
     */
    public function resolveWinnerLoser(MatchGame $match): array
    {
        if ($match->score_a === null || $match->score_b === null) {
            return [null, null];
        }

        if ($match->score_a > $match->score_b) {
            return [$match->home_team_id, $match->away_team_id];
        }
        if ($match->score_b > $match->score_a) {
            return [$match->away_team_id, $match->home_team_id];
        }

        // Égalité => le vainqueur des tirs au but est dans `winner`
        if ($match->winner === 'home') {
            return [$match->home_team_id, $match->away_team_id];
        }
        if ($match->winner === 'away') {
            return [$match->away_team_id, $match->home_team_id];
        }

        return [null, null];
    }

    /**
     * Mettre à jour l'équipe dans un match à élimination directe
     * quand son match parent est terminé.
     *
     * Deux mécanismes :
     *  1. Codes FIFA "W{n}" / "L{n}" (n = match_number) présents dans les
     *     slots team_a/team_b du bracket importé (Coupe du Monde 2026).
     *     Couvre aussi le match pour la 3e place (perdants des demies).
     *  2. Liens parent_match_1_id / parent_match_2_id (mécanisme historique).
     */
    public function updateKnockoutMatchTeams(MatchGame $finishedMatch)
    {
        if ($finishedMatch->status !== 'finished') {
            return;
        }

        [$winnerId, $loserId] = $this->resolveWinnerLoser($finishedMatch);

        if (!$winnerId) {
            Log::warning('Match terminé sans gagnant (égalité sans vainqueur TAB ?)', ['match_id' => $finishedMatch->id]);
            return;
        }

        // 1. Propagation par codes FIFA (W73, L101, ...)
        if ($finishedMatch->match_number) {
            $this->fillBracketSlot('W' . $finishedMatch->match_number, $winnerId);
            if ($loserId) {
                $this->fillBracketSlot('L' . $finishedMatch->match_number, $loserId);
            }
        }

        // 2. Mécanisme historique par liens parents
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
     * Tableau officiel Coupe du Monde 2026 (matchs n°73 à 104).
     * Slots au format FIFA :
     *  - "1A"/"2A"        : 1er / 2e du groupe A (remplis par qualifyTeamsFromGroupStage)
     *  - "3C/D/E/F"       : meilleur 3e parmi ces groupes (affectation manuelle admin)
     *  - "W73" / "L101"   : vainqueur / perdant du match n°73 / n°101 (propagation auto)
     */
    private const WORLD_CUP_BRACKET = [
        'round_of_32' => [
            73 => ['2A', '2B'],
            74 => ['1E', '3A/B/C/D/F'],
            75 => ['1F', '2C'],
            76 => ['1C', '2F'],
            77 => ['1I', '3C/D/F/G/H'],
            78 => ['2E', '2I'],
            79 => ['1A', '3C/E/F/H/I'],
            80 => ['1L', '3E/H/I/J/K'],
            81 => ['1D', '3B/E/F/I/J'],
            82 => ['1G', '3A/E/H/I/J'],
            83 => ['2K', '2L'],
            84 => ['1H', '2J'],
            85 => ['1B', '3E/F/G/I/J'],
            86 => ['1J', '2H'],
            87 => ['1K', '3D/E/I/J/L'],
            88 => ['2D', '2G'],
        ],
        'round_of_16' => [
            89 => ['W74', 'W77'],
            90 => ['W73', 'W75'],
            91 => ['W76', 'W78'],
            92 => ['W79', 'W80'],
            93 => ['W83', 'W84'],
            94 => ['W81', 'W82'],
            95 => ['W86', 'W88'],
            96 => ['W85', 'W87'],
        ],
        'quarter_final' => [
            97 => ['W89', 'W90'],
            98 => ['W93', 'W94'],
            99 => ['W91', 'W92'],
            100 => ['W95', 'W96'],
        ],
        'semi_final' => [
            101 => ['W97', 'W98'],
            102 => ['W99', 'W100'],
        ],
        'third_place' => [
            103 => ['L101', 'L102'],
        ],
        'final' => [
            104 => ['W101', 'W102'],
        ],
    ];

    /**
     * Créer le tableau complet du tournoi à élimination directe
     * (format Coupe du Monde 2026 : 1/16e -> finale, matchs n°73 à 104).
     *
     * Refuse de s'exécuter si un bracket existe déjà (anti-duplication).
     */
    public function createKnockoutBracket()
    {
        $knockoutPhases = array_keys(self::WORLD_CUP_BRACKET);

        if (MatchGame::whereIn('phase', $knockoutPhases)->exists()) {
            throw new \Exception(
                'Un tableau à élimination directe existe déjà. ' .
                'Supprimez les matchs des phases finales avant d\'en régénérer un.'
            );
        }

        DB::beginTransaction();

        try {
            $created = [];
            // Décalage de date indicatif par phase (à ajuster ensuite dans l'admin)
            $daysOffset = [
                'round_of_32' => 17,
                'round_of_16' => 22,
                'quarter_final' => 27,
                'semi_final' => 31,
                'third_place' => 34,
                'final' => 35,
            ];

            foreach (self::WORLD_CUP_BRACKET as $phase => $matches) {
                $position = 1;
                foreach ($matches as $number => [$slotA, $slotB]) {
                    $created[$phase][] = MatchGame::create([
                        'phase' => $phase,
                        'match_number' => $number,
                        'bracket_position' => $position,
                        'display_order' => $number,
                        'team_a' => $slotA,
                        'team_b' => $slotB,
                        'stadium' => 'À définir',
                        'match_date' => now()->addDays($daysOffset[$phase])->addHours($position),
                        'status' => 'scheduled',
                    ]);
                    $position++;
                }
            }

            DB::commit();

            Log::info('Tableau Coupe du Monde 2026 créé', [
                'matchs' => collect($created)->map(fn ($m) => count($m)),
            ]);

            return $created;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du tableau à élimination directe', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
