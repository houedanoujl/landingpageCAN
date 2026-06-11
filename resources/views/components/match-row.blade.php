@props(['match', 'userPrediction' => null, 'trend' => null, 'favoriteTeamId' => null, 'tournamentEnded' => false])

@php
    $homeTeam = $match->homeTeam ?? null;
    $awayTeam = $match->awayTeam ?? null;
    $homeFlag = $homeTeam?->iso_code ? 'https://flagicons.lipis.dev/flags/4x3/' . strtolower($homeTeam->iso_code) . '.svg' : null;
    $awayFlag = $awayTeam?->iso_code ? 'https://flagicons.lipis.dev/flags/4x3/' . strtolower($awayTeam->iso_code) . '.svg' : null;
    $homeName = \App\Models\Team::fr($homeTeam?->name ?? $match->team_a);
    $awayName = \App\Models\Team::fr($awayTeam?->name ?? $match->team_b);
    $isFinished = $match->status === 'finished';
    $isLive = $match->status === 'live';
    $isLocked = \Carbon\Carbon::parse($match->match_date)->isPast();
    $isFavorite = $favoriteTeamId && ($match->home_team_id == $favoriteTeamId || $match->away_team_id == $favoriteTeamId);
    $isKnockout = $match->is_knockout; // accessor modèle : tout sauf group_stage (pas de nul possible)

    $payload = [
        'id' => $match->id,
        'matchInfo' => "{$homeName} vs {$awayName}",
        'homeName' => $homeName,
        'awayName' => $awayName,
        'homeFlag' => $homeFlag,
        'awayFlag' => $awayFlag,
        'kickoff' => \Carbon\Carbon::parse($match->match_date)->translatedFormat('l d F · H\hi'),
        'isKnockout' => $isKnockout,
        'existing' => $userPrediction ? [
            'scoreA' => (int) $userPrediction->score_a,
            'scoreB' => (int) $userPrediction->score_b,
            'penaltyWinner' => $userPrediction->penalty_winner ?? null,
            'createdAt' => $userPrediction->created_at->format('d/m/Y H:i'),
        ] : null,
    ];

    // Tendance des pronostics (agrégée et anonyme)
    $trendTotal = $trend['total'] ?? 0;
    $pctHome = $trendTotal > 0 ? (int) round(($trend['home'] / $trendTotal) * 100) : 0;
    $pctDraw = $trendTotal > 0 ? (int) round(($trend['draw'] / $trendTotal) * 100) : 0;
    $pctAway = $trendTotal > 0 ? (int) round(($trend['away'] / $trendTotal) * 100) : 0;
@endphp

<article id="match-{{ $match->id }}"
         data-phase="{{ $match->phase }}"
         data-group="{{ $match->group_name }}"
         data-home-team="{{ $homeName }}"
         data-away-team="{{ $awayName }}"
         class="group relative bg-white rounded-2xl shadow-elev-1 hover:shadow-elev-2 transition-all duration-base overflow-hidden border border-gray-100 {{ $isFavorite ? 'ring-2 ring-soboa-orange' : '' }}">

    {{-- Kickoff bar --}}
    <header class="flex items-center justify-between px-4 py-2.5 bg-gradient-to-r from-soboa-blue to-soboa-blue-light text-white text-xs font-semibold">
        <span class="inline-flex items-center gap-1.5">
            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
            <span>{{ \Carbon\Carbon::parse($match->match_date)->translatedFormat('D d M · H\hi') }}</span>
            <abbr title="Heure GMT (Temps Universel)" class="opacity-70 no-underline">GMT</abbr>
        </span>
        @if($isFinished)
            <span class="inline-flex items-center gap-1 bg-gray-700/40 px-2 py-0.5 rounded-full uppercase tracking-wide">
                <i data-lucide="flag" class="w-3 h-3"></i>Terminé
            </span>
        @elseif($isLive)
            <span class="inline-flex items-center gap-1 bg-red-500 px-2 py-0.5 rounded-full uppercase tracking-wide animate-pulse">
                <span class="w-1.5 h-1.5 bg-white rounded-full"></span>En direct
            </span>
        @else
            <span class="inline-flex items-center gap-1 bg-white/15 px-2 py-0.5 rounded-full uppercase tracking-wide">
                <i data-lucide="hourglass" class="w-3 h-3"></i>À venir
            </span>
        @endif
    </header>

    {{-- Teams + score/VS --}}
    <div class="px-4 py-5 flex items-center justify-between gap-3">
        <div class="flex-1 min-w-0 text-center">
            <div class="w-14 h-14 mx-auto rounded-full bg-gray-50 ring-1 ring-gray-200 overflow-hidden flex items-center justify-center mb-2">
                @if($homeFlag)
                    <img src="{{ $homeFlag }}" alt="{{ $homeName }}" loading="lazy" class="w-full h-full object-cover">
                @else
                    <span class="font-black text-soboa-blue text-lg">{{ mb_substr($homeName, 0, 2) }}</span>
                @endif
            </div>
            <p class="font-bold text-sm text-soboa-text-dark truncate">{{ $homeName }}</p>
        </div>

        <div class="flex flex-col items-center px-2">
            @if($isFinished && $match->score_a !== null)
                <div class="flex items-center gap-2 text-3xl font-black text-soboa-blue">
                    <span>{{ $match->score_a }}</span>
                    <span class="text-gray-300">-</span>
                    <span>{{ $match->score_b }}</span>
                </div>
                <span class="text-[10px] uppercase tracking-wider text-gray-500 mt-0.5">Score final</span>
            @else
                <span class="font-black text-gray-400 text-xl tracking-tight">VS</span>
                <span class="text-[10px] uppercase tracking-wider text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($match->match_date)->format('H\hi') }}</span>
            @endif
        </div>

        <div class="flex-1 min-w-0 text-center">
            <div class="w-14 h-14 mx-auto rounded-full bg-gray-50 ring-1 ring-gray-200 overflow-hidden flex items-center justify-center mb-2">
                @if($awayFlag)
                    <img src="{{ $awayFlag }}" alt="{{ $awayName }}" loading="lazy" class="w-full h-full object-cover">
                @else
                    <span class="font-black text-soboa-blue text-lg">{{ mb_substr($awayName, 0, 2) }}</span>
                @endif
            </div>
            <p class="font-bold text-sm text-soboa-text-dark truncate">{{ $awayName }}</p>
        </div>
    </div>

    {{-- Venues --}}
    @if($match->animations && $match->animations->count() > 0)
        <div class="px-4 pb-3">
            <details class="group/details">
                <summary class="cursor-pointer list-none flex items-center justify-between text-xs font-semibold text-gray-600 hover:text-soboa-blue py-1.5 focus:outline-none focus:ring-2 focus:ring-soboa-blue rounded">
                    <span class="inline-flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                        Diffusé dans {{ $match->animations->count() }} PDV
                    </span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform duration-base group-open/details:rotate-180"></i>
                </summary>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @foreach($match->animations as $animation)
                        @php $bar = $animation->bar; @endphp
                        @if($bar)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-soboa-cream text-soboa-text-dark text-[11px] font-medium ring-1 ring-soboa-orange/20">
                                {{ $bar->name }}@if($bar->zone)<span class="opacity-60">· {{ $bar->zone }}</span>@endif
                            </span>
                        @endif
                    @endforeach
                </div>
            </details>
        </div>
    @endif

    {{-- Tendance des pronostics (agrégée, anonyme) --}}
    <div id="trend-wrap-{{ $match->id }}" class="px-4 pb-3" @style(['display:none' => $trendTotal === 0])>
        <div class="flex items-center justify-between mb-1">
            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Tendance</span>
            <span class="text-[10px] text-gray-400 font-medium" data-trend-label>{{ $trendTotal }} {{ $trendTotal > 1 ? 'pronostics' : 'pronostic' }}</span>
        </div>
        <div class="flex h-6 rounded-md overflow-hidden text-white text-[10px] font-black bg-gray-100">
            <div data-trend-home class="bg-soboa-blue flex items-center justify-center min-w-0" style="width: {{ $pctHome }}%">{{ $pctHome > 0 ? $pctHome.'%' : '' }}</div>
            <div data-trend-draw class="bg-gray-400 flex items-center justify-center min-w-0" style="width: {{ $pctDraw }}%">{{ $pctDraw > 0 ? $pctDraw.'%' : '' }}</div>
            <div data-trend-away class="bg-soboa-orange flex items-center justify-center min-w-0" style="width: {{ $pctAway }}%">{{ $pctAway > 0 ? $pctAway.'%' : '' }}</div>
        </div>
        <div class="flex justify-between text-[10px] font-bold mt-1 gap-2">
            <span class="text-soboa-blue truncate">{{ $homeName }}</span>
            <span class="text-gray-500 flex-shrink-0">Nul</span>
            <span class="text-soboa-orange truncate text-right">{{ $awayName }}</span>
        </div>
    </div>

    {{-- Action footer --}}
    <footer id="match-footer-{{ $match->id }}" class="px-4 pb-4 pt-1 border-t border-gray-100 mt-1">
        @if($tournamentEnded)
            <div class="text-center text-sm text-gray-500 py-2">Pronostics fermés</div>
        @elseif($isFinished)
            <div class="inline-flex items-center gap-2 text-sm text-gray-600 py-2 w-full justify-center">
                <i data-lucide="badge-check" class="w-4 h-4"></i>
                Match terminé
            </div>
        @elseif(!session('user_id'))
            <a href="/login" class="btn btn-blue btn-md btn-block">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                Se connecter pour pronostiquer
            </a>
        @elseif($userPrediction)
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-full bg-green-100 text-green-700 flex items-center justify-center">
                        <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 leading-tight">Votre pronostic</p>
                        <p class="text-base font-black text-soboa-text-dark leading-tight">
                            {{ $userPrediction->score_a }} - {{ $userPrediction->score_b }}
                            @if($userPrediction->points_earned > 0)
                                <span class="text-soboa-orange ml-1">+{{ $userPrediction->points_earned }} pts</span>
                            @endif
                        </p>
                    </div>
                </div>
                @if(!$isLocked)
                    <button type="button"
                            x-on:click="openPrediction({{ \Illuminate\Support\Js::from($payload) }})"
                            class="btn btn-ghost btn-sm">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                        Modifier
                    </button>
                @endif
            </div>
        @elseif($isLocked)
            <div class="inline-flex items-center gap-2 text-sm text-gray-500 py-2 w-full justify-center">
                <i data-lucide="lock" class="w-4 h-4"></i>
                Pronostics fermés
            </div>
        @else
            <button type="button"
                    x-on:click="openPrediction({{ \Illuminate\Support\Js::from($payload) }})"
                    class="btn btn-primary btn-md btn-block">
                <i data-lucide="target" class="w-4 h-4"></i>
                Pronostiquer
            </button>
        @endif
    </footer>

    {{-- Mur de commentaires public (modal global) --}}
    <div class="px-4 pb-4 -mt-1">
        <button type="button"
                @click="$dispatch('open-match-wall', { matchId: {{ $match->id }} })"
                class="w-full inline-flex items-center justify-center gap-2 text-xs font-bold text-soboa-blue hover:text-white hover:bg-soboa-blue ring-1 ring-soboa-blue/20 py-2 rounded-lg transition-colors">
            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
            Commentaires
            @if(($match->comments_count ?? 0) > 0)
                <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-soboa-orange text-white text-[10px] font-black">{{ $match->comments_count }}</span>
            @endif
        </button>
    </div>
</article>
