<?php

namespace App\Console\Commands;

use App\Jobs\ProcessMatchPoints;
use App\Models\MatchGame;
use App\Models\PointLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessFinishedMatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matches:process-finished';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-finalize stale matches with scores and dispatch points calculation';

    /**
     * Two-step pipeline:
     * 1. Auto-finalize: matches whose kickoff was more than 3h ago and have both
     *    scores filled but status != 'finished' get flipped to 'finished'. Covers
     *    the case where an admin enters scores but forgets to change the status.
     * 2. Dispatch points calculation for finished+scored matches not yet logged
     *    (idempotent — guarded by point_logs).
     */
    public function handle()
    {
        $finalizedCount = 0;
        $cutoff = now()->subHours(3);

        $toFinalize = MatchGame::whereNotNull('score_a')
            ->whereNotNull('score_b')
            ->where('status', '!=', 'finished')
            ->where('match_date', '<=', $cutoff)
            ->get();

        foreach ($toFinalize as $match) {
            $match->status = 'finished';
            $match->save();
            $finalizedCount++;
            Log::info("ProcessFinishedMatches: auto-finalized match {$match->id} ({$match->team_a} vs {$match->team_b})");
            $this->info("Auto-finalized match {$match->id}: {$match->team_a} vs {$match->team_b}");
        }

        $finishedMatches = MatchGame::where('status', 'finished')
            ->whereNotNull('score_a')
            ->whereNotNull('score_b')
            ->get();

        $processedCount = 0;

        foreach ($finishedMatches as $match) {
            $hasPointLogs = PointLog::where('match_id', $match->id)->exists();

            if (!$hasPointLogs) {
                ProcessMatchPoints::dispatch($match->id);
                $processedCount++;

                Log::info("ProcessFinishedMatches: Dispatched job for match {$match->id} ({$match->team_a} vs {$match->team_b})");
                $this->info("Processing match {$match->id}: {$match->team_a} vs {$match->team_b}");
            }
        }

        $this->info("Auto-finalized {$finalizedCount} match(es). Dispatched {$processedCount} match(es) for points calculation.");

        return 0;
    }
}
