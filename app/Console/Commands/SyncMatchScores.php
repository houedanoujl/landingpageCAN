<?php

namespace App\Console\Commands;

use App\Models\MatchGame;
use App\Services\FootballDataService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Sync scores from football-data.org for matches in the active window.
 *
 * Cheap-by-design:
 *  - Skips entirely if the integration is disabled or no candidates exist.
 *  - Bulk-fetches the whole competition for the active window (1 API call
 *    covers many matches) instead of per-match polling.
 *  - Throttles itself via Cache config (default 60 s) inside the service.
 *  - Failures are non-fatal: the existing manual workflow keeps working.
 *
 * Fallback chain:
 *  1. This command tries the API.
 *  2. ProcessFinishedMatches auto-finalizes matches +3 h past kickoff with
 *     scores entered manually by the admin.
 *  3. Admin can always edit scores manually in the admin UI.
 */
class SyncMatchScores extends Command
{
    protected $signature = 'matches:sync-scores
                            {--force : Bypass active-window filter and sync all known matches}';

    protected $description = 'Fetch live scores from football-data.org for matches in the active window';

    public function __construct(private readonly FootballDataService $api)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (!$this->api->enabled()) {
            $this->info('football-data.org integration disabled (FOOTBALL_DATA_ENABLED or FOOTBALL_DATA_API_KEY missing). Skipping.');
            return self::SUCCESS;
        }

        $candidates = $this->candidateMatches();
        if ($candidates->isEmpty()) {
            $this->info('No candidate matches in the active window — skipping API call.');
            return self::SUCCESS;
        }

        $dateFrom = $candidates->min('match_date')->copy()->subDay()->format('Y-m-d');
        $dateTo   = $candidates->max('match_date')->copy()->addDay()->format('Y-m-d');

        $payload = $this->api->getCompetitionMatches($dateFrom, $dateTo);

        if ($payload === null) {
            $this->warn('API call failed or returned no data. Manual workflow remains the source of truth.');
            Log::warning('SyncMatchScores: API unavailable, deferring to manual entry');
            return self::SUCCESS; // exit cleanly so the scheduler does not bubble up the error
        }

        $byExternalId = collect($payload['matches'] ?? [])
            ->keyBy(fn ($m) => (string) ($m['id'] ?? ''));

        $updated = 0;
        $finalized = 0;

        foreach ($candidates as $match) {
            $external = $byExternalId->get((string) $match->external_id);
            if (!$external) {
                continue;
            }

            $score = $external['score']['fullTime'] ?? null;
            $status = strtoupper($external['status'] ?? '');

            // Update scores when available (live or finished).
            if (is_array($score) && $score['home'] !== null && $score['away'] !== null) {
                if ($match->score_a !== (int) $score['home'] || $match->score_b !== (int) $score['away']) {
                    $match->score_a = (int) $score['home'];
                    $match->score_b = (int) $score['away'];
                    $updated++;
                }
            }

            // Lifecycle: API tells us when the match is over.
            if ($status === 'FINISHED' && $match->status !== 'finished') {
                $match->status = 'finished';
                $finalized++;
            } elseif (in_array($status, ['IN_PLAY', 'PAUSED', 'LIVE'], true) && $match->status !== 'live') {
                $match->status = 'live';
            }

            $match->last_synced_at = now();
            $match->save();
        }

        $this->info(sprintf(
            'Synced %d match(es). Scores updated: %d. Finalized: %d.',
            $candidates->count(),
            $updated,
            $finalized
        ));

        return self::SUCCESS;
    }

    /**
     * Matches eligible for sync: have external_id, not yet finished, and either
     * kicked off recently (< 4 h ago) or are about to (within 2 h). The 2-hour
     * pre-window catches early "LIVE" status changes from the API. The 4-hour
     * post-window covers extra time + penalties + admin lag.
     */
    private function candidateMatches()
    {
        if ($this->option('force')) {
            return MatchGame::whereNotNull('external_id')->get();
        }

        $now = Carbon::now();
        return MatchGame::whereNotNull('external_id')
            ->where('status', '!=', 'finished')
            ->whereBetween('match_date', [$now->copy()->subHours(4), $now->copy()->addHours(2)])
            ->get();
    }
}
