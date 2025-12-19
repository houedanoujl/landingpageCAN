<?php

namespace App\Jobs;

use App\Models\MatchGame;
use App\Models\PointLog;
use App\Models\Prediction;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessMatchPoints implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $matchId;
    protected WhatsAppService $whatsAppService;

    /**
     * Create a new job instance.
     */
    public function __construct(int $matchId)
    {
        $this->matchId = $matchId;
        $this->whatsAppService = new WhatsAppService();
    }

    /**
     * Execute the job.
     * 
     * Scoring Rules:
     * - Participation: +1 point (awarded on prediction, not here)
     * - Correct Winner (1/N/2): +3 points
     * - Exact Score: +3 points extra
     * - Total max per match: 7 points (1 + 3 + 3)
     */
    public function handle(): void
    {
        $match = MatchGame::find($this->matchId);

        if (!$match || $match->status !== 'finished') {
            Log::warning("ProcessMatchPoints: Match {$this->matchId} not found or not finished");
            return;
        }

        if ($match->score_a === null || $match->score_b === null) {
            Log::warning("ProcessMatchPoints: Match {$this->matchId} has no final score");
            return;
        }

        // Determine actual match result
        $actualWinner = $this->determineWinner($match->score_a, $match->score_b);
        
        // Vérifier si le match réel a eu des tirs au but
        // Un match a des TAB si le score est égal ET qu'il y a un vainqueur défini
        $matchHadPenalties = ($match->score_a == $match->score_b) && !empty($match->winner);
        if ($matchHadPenalties) {
            $actualWinner = $match->winner; // home, away
        }

        // Get all predictions for this match
        $predictions = Prediction::with('user')
            ->where('match_id', $this->matchId)
            ->get();

        Log::info("ProcessMatchPoints: Processing {$predictions->count()} predictions for match {$this->matchId}");

        foreach ($predictions as $prediction) {
            $totalPoints = 0;

            // 1. Participation (+1 point, une seule fois par match/prédiction)
            $alreadyParticipation = \App\Models\PointLog::where('user_id', $prediction->user_id)
                ->where('source', 'prediction_participation')
                ->where('match_id', $this->matchId)
                ->exists();
            if (!$alreadyParticipation) {
                $totalPoints += 1;
                \App\Models\PointLog::create([
                    'user_id' => $prediction->user_id,
                    'source' => 'prediction_participation',
                    'points' => 1,
                    'match_id' => $this->matchId,
                ]);
            }

            // Vérifier si l'utilisateur a prédit des tirs au but
            $userPredictedPenalties = $prediction->predict_draw && $prediction->penalty_winner;

            // 2. Correct Winner (+3 points)
            // Si le match a eu des TAB: comparer avec penalty_winner de l'utilisateur
            // Sinon: comparer avec le vainqueur des scores
            if ($matchHadPenalties && $userPredictedPenalties) {
                // Match réel avec TAB + utilisateur a prédit TAB
                $predictedWinner = $prediction->penalty_winner;
            } elseif ($matchHadPenalties && !$userPredictedPenalties) {
                // Match réel avec TAB mais utilisateur n'a pas prédit TAB
                // On prend le vainqueur de son score prédit
                $predictedWinner = $this->determineWinner($prediction->score_a, $prediction->score_b);
            } elseif (!$matchHadPenalties && $userPredictedPenalties) {
                // Match sans TAB mais utilisateur a prédit TAB
                // On prend son penalty_winner comme vainqueur prédit
                $predictedWinner = $prediction->penalty_winner;
            } else {
                // Match sans TAB et utilisateur n'a pas prédit TAB
                $predictedWinner = $this->determineWinner($prediction->score_a, $prediction->score_b);
            }
            
            if ($predictedWinner === $actualWinner) {
                $alreadyWinner = \App\Models\PointLog::where('user_id', $prediction->user_id)
                    ->where('source', 'prediction_winner')
                    ->where('match_id', $this->matchId)
                    ->exists();
                if (!$alreadyWinner) {
                    $totalPoints += 3;
                    \App\Models\PointLog::create([
                        'user_id' => $prediction->user_id,
                        'source' => 'prediction_winner',
                        'points' => 3,
                        'match_id' => $this->matchId,
                    ]);

                    // Send WhatsApp notification for correct winner prediction
                    if ($prediction->user) {
                        $teamA = $match->homeTeam->name ?? $match->team_a;
                        $teamB = $match->awayTeam->name ?? $match->team_b;
                        $message = "🎉 *Bravo !*\n\n";
                        $message .= "Vous avez correctement prédit le vainqueur du match :\n";
                        $message .= "*{$teamA}* {$match->score_a} - {$match->score_b} *{$teamB}*\n\n";
                        $message .= "✅ +3 points gagnés !\n";
                        $message .= "📊 Total de vos points : " . ($prediction->user->points_total + 3);

                        try {
                            $this->whatsAppService->sendMessage(
                                $this->whatsAppService->formatWhatsAppNumber($prediction->user->phone),
                                $message
                            );
                        } catch (\Exception $e) {
                            Log::warning("Failed to send WhatsApp for winner prediction: " . $e->getMessage());
                        }
                    }
                }
            }

            // 3. Exact Score (+3 points extra)
            // RÈGLE IMPORTANTE: Si le match a eu des TAB, PAS de points pour score exact
            // Car un match avec TAB n'est pas considéré comme un "score exact" - c'est une égalité qui s'est décidée aux penalties
            if (!$matchHadPenalties && $prediction->score_a == $match->score_a && $prediction->score_b == $match->score_b) {
                $alreadyExact = \App\Models\PointLog::where('user_id', $prediction->user_id)
                    ->where('source', 'prediction_exact')
                    ->where('match_id', $this->matchId)
                    ->exists();
                if (!$alreadyExact) {
                    $totalPoints += 3;
                    \App\Models\PointLog::create([
                        'user_id' => $prediction->user_id,
                        'source' => 'prediction_exact',
                        'points' => 3,
                        'match_id' => $this->matchId,
                    ]);

                    // Send WhatsApp notification for exact score prediction
                    if ($prediction->user) {
                        $teamA = $match->homeTeam->name ?? $match->team_a;
                        $teamB = $match->awayTeam->name ?? $match->team_b;
                        $message = "🏆 *INCROYABLE !*\n\n";
                        $message .= "Vous avez prédit le score EXACT du match :\n";
                        $message .= "*{$teamA}* {$match->score_a} - {$match->score_b} *{$teamB}*\n\n";
                        $message .= "🎯 Score exact ! +3 points bonus !\n";
                        $message .= "📊 Total de vos points : " . ($prediction->user->points_total + $totalPoints);

                        try {
                            $this->whatsAppService->sendMessage(
                                $this->whatsAppService->formatWhatsAppNumber($prediction->user->phone),
                                $message
                            );
                        } catch (\Exception $e) {
                            Log::warning("Failed to send WhatsApp for exact score: " . $e->getMessage());
                        }
                    }
                }
            }

            // Update user's total points
            if ($totalPoints > 0 && $prediction->user) {
                $prediction->user->increment('points_total', $totalPoints);
            }

            // Update prediction with total points earned (calculate from actual point_logs)
            $totalPointsEarned = \App\Models\PointLog::where('user_id', $prediction->user_id)
                ->where('match_id', $this->matchId)
                ->sum('points');
            $prediction->points_earned = $totalPointsEarned;
            $prediction->save();

            Log::info("ProcessMatchPoints: User {$prediction->user_id} earned {$totalPoints} new points (total: {$totalPointsEarned}) for match {$this->matchId}");
        }

        // Clear leaderboard cache since points changed
        Cache::forget('leaderboard_top_5');

        Log::info("ProcessMatchPoints: Completed processing match {$this->matchId}");
    }

    /**
     * Determine winner from scores.
     * 
     * @return string 'home' | 'away' | 'draw'
     */
    private function determineWinner(int $homeScore, int $awayScore): string
    {
        if ($homeScore > $awayScore) {
            return 'home';
        } elseif ($awayScore > $homeScore) {
            return 'away';
        }
        return 'draw';
    }
}
