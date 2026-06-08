<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around football-data.org v4. Designed to be cheap on the API
 * budget (free tier = 10 calls/min): cache GETs, throttle, gracefully degrade.
 *
 * Only the calls actually used by SyncMatchScores are exposed. Any failure
 * returns `null` so callers can fall back to manual admin entry.
 */
class FootballDataService
{
    public function __construct(private readonly array $config)
    {
    }

    public function enabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false)
            && !empty($this->config['key']);
    }

    /**
     * Fetch a single match by football-data.org match id.
     * Returns the raw `match` payload or null on failure.
     */
    public function getMatch(string $externalId): ?array
    {
        if (!$this->enabled()) {
            return null;
        }

        return $this->cachedGet("match:{$externalId}", "/matches/{$externalId}");
    }

    /**
     * Fetch the matches of the configured competition within a date window.
     * Bulk endpoint = 1 API call covers many matches → preferred over per-match
     * polling during a tournament.
     *
     * Returns ['matches' => [...]] array or null on failure.
     */
    public function getCompetitionMatches(?string $dateFrom = null, ?string $dateTo = null): ?array
    {
        if (!$this->enabled()) {
            return null;
        }

        $competition = $this->config['competition'] ?? 'WC';
        $cacheKey = sprintf('competition:%s:%s:%s', $competition, $dateFrom ?? 'na', $dateTo ?? 'na');

        $path = "/competitions/{$competition}/matches";
        $query = array_filter([
            'dateFrom' => $dateFrom,
            'dateTo'   => $dateTo,
        ]);

        return $this->cachedGet($cacheKey, $path, $query);
    }

    private function cachedGet(string $cacheKey, string $path, array $query = []): ?array
    {
        $ttl = (int) ($this->config['cache_ttl'] ?? 60);
        $fullKey = 'football_data:' . sha1($cacheKey . json_encode($query));

        $cached = Cache::get($fullKey);
        if ($cached !== null) {
            return $cached;
        }

        $response = $this->request($path, $query);
        if (!$response || !$response->successful()) {
            return null;
        }

        $payload = $response->json();
        Cache::put($fullKey, $payload, $ttl);
        return $payload;
    }

    private function request(string $path, array $query = []): ?Response
    {
        $baseUrl = rtrim($this->config['base_url'] ?? '', '/');
        $url = $baseUrl . $path;

        try {
            return Http::withHeaders([
                'X-Auth-Token' => $this->config['key'] ?? '',
                'Accept' => 'application/json',
            ])
                ->timeout((int) ($this->config['timeout'] ?? 10))
                ->retry(2, 250, throw: false)
                ->get($url, $query);
        } catch (ConnectionException $e) {
            Log::warning('FootballDataService: connection error', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::warning('FootballDataService: unexpected error', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
