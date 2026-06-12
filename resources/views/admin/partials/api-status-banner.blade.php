{{--
    Bandeau d'état de la synchro automatique des scores (football-data.org).
    Autonome : s'injecte le service et fait ses propres requêtes (légères),
    donc utilisable par simple @include depuis n'importe quelle page admin.
--}}
@inject('footballApi', 'App\Services\FootballDataService')

@php
    $apiEnabled = $footballApi->enabled();
    $totalMatches = \App\Models\MatchGame::count();
    $mappedMatches = \App\Models\MatchGame::whereNotNull('external_id')->count();
    $lastSync = \App\Models\MatchGame::max('last_synced_at');
    $lastSync = $lastSync ? \Illuminate\Support\Carbon::parse($lastSync) : null;

    // Un match devrait être en cours de synchro s'il est dans la fenêtre live
    // (même logique que matches:sync-scores : kickoff -4 h / +2 h, non terminé).
    $now = \Illuminate\Support\Carbon::now();
    $liveWindowCount = \App\Models\MatchGame::whereNotNull('external_id')
        ->where('status', '!=', 'finished')
        ->whereBetween('match_date', [$now->copy()->subHours(4), $now->copy()->addHours(2)])
        ->count();

    // Synchro attendue toutes les 2 min : au-delà de 10 min avec un match
    // dans la fenêtre live, le scheduler a probablement un problème.
    $syncStale = $apiEnabled
        && $liveWindowCount > 0
        && (!$lastSync || $lastSync->diffInMinutes($now) > 10);

    if (!$apiEnabled) {
        $tone = 'gray';
        $title = 'Synchro automatique des scores désactivée';
        $detail = 'Saisie manuelle uniquement. Activer FOOTBALL_DATA_ENABLED et la clé API pour la synchro auto.';
    } elseif ($syncStale) {
        $tone = 'red';
        $title = 'Synchro des scores en retard';
        $detail = $liveWindowCount . ' match(s) dans la fenêtre live mais '
            . ($lastSync ? 'dernière synchro il y a ' . $lastSync->diffForHumans($now, true) : 'aucune synchro encore effectuée')
            . '. Vérifier le scheduler (php artisan schedule:run).';
    } elseif (!$lastSync) {
        $tone = 'orange';
        $title = 'API scores active — en attente de la première synchro';
        $detail = 'La synchro tourne toutes les 2 min pendant les matchs. Aucun appel encore enregistré.';
    } else {
        $tone = 'green';
        $title = 'API football-data.org active';
        $detail = 'Dernière synchro il y a ' . $lastSync->diffForHumans($now, true) . '.';
    }

    $styles = [
        'gray'   => ['wrap' => 'bg-gray-100 border-gray-300 text-gray-700', 'dot' => 'bg-gray-400'],
        'orange' => ['wrap' => 'bg-orange-50 border-orange-300 text-orange-800', 'dot' => 'bg-orange-400'],
        'red'    => ['wrap' => 'bg-red-50 border-red-300 text-red-800', 'dot' => 'bg-red-500 animate-pulse'],
        'green'  => ['wrap' => 'bg-green-50 border-green-300 text-green-800', 'dot' => 'bg-green-500'],
    ][$tone];
@endphp

<div class="border rounded-xl px-4 py-3 mb-6 flex flex-wrap items-center gap-x-4 gap-y-2 {{ $styles['wrap'] }}">
    <span class="flex items-center gap-2 font-bold">
        <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ $styles['dot'] }}"></span>
        {{ $title }}
    </span>
    <span class="text-sm">{{ $detail }}</span>
    <span class="text-sm ml-auto whitespace-nowrap">
        {{ $mappedMatches }}/{{ $totalMatches }} matchs liés à l'API
        @if($liveWindowCount > 0)
            · {{ $liveWindowCount }} en fenêtre live
        @endif
    </span>
    @if($apiEnabled && $mappedMatches < $totalMatches)
        <form method="POST" action="{{ route('admin.map-external-ids') }}" class="shrink-0"
              onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').innerText = 'Liaison en cours…';">
            @csrf
            <button type="submit"
                    class="bg-soboa-blue hover:bg-soboa-blue/90 text-white text-sm font-bold py-2 px-4 rounded-lg transition"
                    title="Associe les matchs locaux aux matchs football-data.org (poules par équipes+date, knockout par phase+horaire). Sans risque : ne touche ni scores ni équipes placées manuellement.">
                🔗 Lier les matchs à l'API
            </button>
        </form>
    @endif
</div>
