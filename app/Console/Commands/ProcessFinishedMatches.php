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
    protected $description = 'Process points for finished matches that haven\'t been calculated yet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find all finished matches with scores that haven't had points calculated
        $finishedMatches = MatchGame::where('status', 'finished')
            ->whereNotNull('score_a')
            ->whereNotNull('score_b')
            ->get();

        $processedCount = 0;

        foreach ($finishedMatches as $match) {
            // Check if this match already has point logs (points already calculated)
            $hasPointLogs = PointLog::where('match_id', $match->id)->exists();

            if (!$hasPointLogs) {
                // Points not yet calculated, dispatch the job
                ProcessMatchPoints::dispatch($match->id);
                $processedCount++;

                Log::info("ProcessFinishedMatches: Dispatched job for match {$match->id} ({$match->team_a} vs {$match->team_b})");
                $this->info("Processing match {$match->id}: {$match->team_a} vs {$match->team_b}");
            }
        }

        if ($processedCount === 0) {
            $this->info('No finished matches requiring points calculation.');
        } else {
            $this->info("Dispatched {$processedCount} match(es) for points calculation.");
        }

        return 0;
    }
}
