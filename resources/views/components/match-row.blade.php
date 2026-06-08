@props(['match', 'userPrediction' => null, 'favoriteTeamId' => null, 'tournamentEnded' => false])

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
    $isKnockout = in_array($match->phase, ['round_of_32', 'round_of_16', 'quarter_final', 'semi_final', 'third_place', 'final']);

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
@endphp

<article id="match-{{ $match->id }}"
         data-phase="{{ $match->phase }}"
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

    {{-- Action footer --}}
    <footer class="px-4 pb-4 pt-1 border-t border-gray-100 mt-1">
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

    {{-- Pronostics des autres + likes --}}
    <div x-data="matchPredictions({{ $match->id }})" class="px-4 pb-4 -mt-1">
        <button type="button" @click="open()"
                class="w-full inline-flex items-center justify-center gap-1.5 text-xs font-bold text-soboa-blue hover:text-soboa-blue-dark py-2 rounded-lg hover:bg-soboa-blue/5 transition-colors">
            <i data-lucide="users" class="w-3.5 h-3.5"></i>
            Voir les pronostics
        </button>

        <div x-show="isOpen" x-cloak style="display:none;"
             @keydown.escape.window="close()"
             class="modal-backdrop">
            <div class="modal-panel p-0 overflow-hidden" @click.outside="close()">
                <header class="bg-gradient-to-r from-soboa-blue to-soboa-blue-light text-white px-5 py-4 flex items-center justify-between">
                    <div class="min-w-0">
                        <h3 class="font-black text-lg leading-tight">Pronostics</h3>
                        <p class="text-xs text-white/80 truncate" x-text="title"></p>
                    </div>
                    <button @click="close()" class="modal-close" aria-label="Fermer">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </header>
                <div class="max-h-[60vh] overflow-y-auto p-4 space-y-2">
                    <template x-if="loading">
                        <p class="text-center text-gray-500 py-6">Chargement…</p>
                    </template>
                    <template x-if="!loading && items.length === 0">
                        <p class="text-center text-gray-500 py-6">Aucun pronostic pour ce match.</p>
                    </template>
                    <template x-for="p in items" :key="p.id">
                        <div class="flex items-center justify-between gap-3 bg-gray-50 rounded-xl px-3 py-2.5">
                            <div class="min-w-0">
                                <p class="font-bold text-sm text-soboa-text-dark truncate" x-text="p.user_name + (p.is_mine ? ' (vous)' : '')"></p>
                                <p class="text-xs text-gray-500">
                                    Pronostic : <span class="font-black text-soboa-blue" x-text="p.score_a + ' - ' + p.score_b"></span>
                                    <span x-show="p.points_earned > 0" class="text-soboa-orange font-bold" x-text="'· +' + p.points_earned + ' pts'"></span>
                                </p>
                            </div>
                            <button type="button" @click="toggleLike(p)" :disabled="!auth || p.is_mine"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-bold transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    :class="p.liked ? 'bg-red-100 text-red-600' : 'bg-white text-gray-500 ring-1 ring-gray-200 hover:bg-gray-100'">
                                <i data-lucide="heart" class="w-4 h-4" :class="p.liked ? 'fill-current' : ''"></i>
                                <span x-text="p.likes_count"></span>
                            </button>
                        </div>
                    </template>
                    <p x-show="!auth && !loading" class="text-center text-xs text-gray-400 pt-2">
                        <a href="/login" class="text-soboa-blue font-bold">Connectez-vous</a> pour liker.
                    </p>
                </div>
            </div>
        </div>
    </div>
</article>
