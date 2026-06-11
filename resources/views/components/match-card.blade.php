@props(['match', 'trend' => null])

@php
    $homeTeam = $match->homeTeam ?? null;
    $awayTeam = $match->awayTeam ?? null;
    $homeName = \App\Models\Team::fr($homeTeam ? $homeTeam->name : $match->team_a);
    $awayName = \App\Models\Team::fr($awayTeam ? $awayTeam->name : $match->team_b);

    // Tendance des pronostics (agrégée et anonyme)
    $trendTotal = $trend['total'] ?? 0;
    $pctHome = $trendTotal > 0 ? (int) round(($trend['home'] / $trendTotal) * 100) : 0;
    $pctDraw = $trendTotal > 0 ? (int) round(($trend['draw'] / $trendTotal) * 100) : 0;
    $pctAway = $trendTotal > 0 ? (int) round(($trend['away'] / $trendTotal) * 100) : 0;
    $homeFlag = $homeTeam ? "https://flagicons.lipis.dev/flags/4x3/{$homeTeam->iso_code}.svg" : null;
    $awayFlag = $awayTeam ? "https://flagicons.lipis.dev/flags/4x3/{$awayTeam->iso_code}.svg" : null;

    // Determine if match is live, upcoming, or finished
    $isLive = $match->status === 'live';
    $isFinished = $match->status === 'finished';
    $isUpcoming = !$isLive && !$isFinished;

    // Vérifier si c'est un match de phase finale à déterminer
    $isTbd = $match->is_tbd;
@endphp

<div class="relative bg-gradient-to-br from-white via-gray-50 to-white rounded-3xl shadow-xl border-2 border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-300 group">

    <!-- Decorative Background Pattern -->
    <div class="absolute inset-0 opacity-5 pointer-events-none">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <circle cx="20" cy="20" r="1" fill="currentColor" class="text-soboa-blue"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid)" />
        </svg>
    </div>

    <!-- Top Status Bar -->
    <div class="relative bg-gradient-to-r from-soboa-blue to-blue-600 px-4 py-2">
        <div class="flex items-center justify-between">
            <!-- Group Badge -->
            @if($match->group_name)
                <div class="flex items-center gap-2 text-white">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span class="text-sm font-bold uppercase tracking-wider">Groupe {{ $match->group_name }}</span>
                </div>
            @endif

            <!-- Status Badge -->
            @if($isLive)
                <div class="flex items-center gap-2 bg-red-500 px-3 py-1 rounded-full animate-pulse">
                    <span class="w-2 h-2 bg-white rounded-full"></span>
                    <span class="text-white text-xs font-black uppercase">En direct</span>
                </div>
            @elseif($isFinished)
                <div class="bg-gray-700 px-3 py-1 rounded-full">
                    <span class="text-white text-xs font-bold uppercase">Terminé</span>
                </div>
            @else
                <div class="flex items-center gap-2 bg-green-500 px-3 py-1 rounded-full">
                    <i data-lucide="clock" class="w-3 h-3 text-white"></i>
                    <span class="text-white text-xs font-bold uppercase">À venir</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Match Content -->
    <div class="relative p-4">

        <!-- Date & Time Display (compact, une seule ligne) -->
        <div class="text-center mb-4">
            <div class="inline-flex items-center gap-2 bg-soboa-orange/10 px-4 py-1.5 rounded-full border border-soboa-orange/20 text-sm">
                <i data-lucide="calendar-days" class="w-4 h-4 text-soboa-orange"></i>
                <span class="text-gray-700 font-semibold capitalize">{{ $match->match_date->translatedFormat('D d M') }}</span>
                <span class="text-soboa-orange/40">·</span>
                <span class="font-black text-soboa-orange text-base">{{ $match->match_date->format('H:i') }}</span>
            </div>
        </div>

        <!-- Teams Display -->
        @if($isTbd)
            <!-- Match de phase finale à déterminer - Afficher le nom de la phase -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-28 h-28 fold:w-32 fold:h-32 bg-gradient-to-br from-soboa-blue to-blue-600 rounded-full shadow-xl mb-4">
                    <span class="text-4xl fold:text-5xl"></span>
                </div>
                <h2 class="text-2xl fold:text-3xl font-black text-soboa-blue mb-2">{{ $match->phase_name }}</h2>
                <p class="text-sm fold:text-base text-gray-600 font-semibold">Équipes à déterminer</p>
            </div>
        @else
            <!-- Regular Match with Teams -->
            <div class="flex flex-wrap items-center justify-between gap-3 fold:gap-4 mb-4">

                <!-- Home Team -->
                <div class="flex-1 min-w-[100px] text-center group/team">
                    <div class="relative inline-block mb-2 fold:mb-3">
                        @if($homeFlag)
                            <div class="w-12 h-12 fold:w-14 fold:h-14 rounded-full overflow-hidden shadow-lg ring-2 fold:ring-4 ring-white group-hover/team:ring-soboa-orange transition-all duration-300 transform group-hover/team:scale-110 home-flag-container">
                                <img src="{{ $homeFlag }}"
                                     alt="{{ $homeName }}"
                                     class="w-full h-full object-cover home-flag-img"
                                     onerror="this.parentElement.outerHTML='<div class=\'w-20 h-20 rounded-full bg-gradient-to-br from-soboa-blue to-blue-600 shadow-lg ring-4 ring-white flex items-center justify-center group-hover/team:ring-soboa-orange transition-all duration-300 transform group-hover/team:scale-110\'><span class=\'text-2xl font-black text-white\'>{{ mb_substr($match->team_a, 0, 2) }}</span></div>'">
                            </div>
                        @else
                            <div class="w-12 h-12 fold:w-14 fold:h-14 rounded-full bg-gradient-to-br from-soboa-blue to-blue-600 shadow-lg ring-2 fold:ring-4 ring-white flex items-center justify-center group-hover/team:ring-soboa-orange transition-all duration-300 transform group-hover/team:scale-110">
                                <span class="text-base fold:text-lg font-black text-white">{{ mb_substr($homeName, 0, 2) }}</span>
                            </div>
                        @endif
                    </div>
                    <h3 class="font-black text-gray-800 text-sm fold:text-base leading-tight px-1 fold:px-2">
                        {{ $homeName }}
                    </h3>
                </div>

                <!-- VS / Score Separator -->
                <div class="flex-shrink-0 px-2 fold:px-3">
                    @if($isFinished && $match->score_a !== null && $match->score_b !== null)
                        <!-- Final Score -->
                        <div class="text-center">
                            <div class="flex items-center gap-3">
                                <span class="text-4xl font-black text-soboa-blue">{{ $match->score_a }}</span>
                                <span class="text-2xl font-bold text-gray-300">-</span>
                                <span class="text-4xl font-black text-soboa-blue">{{ $match->score_b }}</span>
                            </div>
                            <span class="text-xs text-gray-500 font-medium uppercase mt-1 block">Score final</span>
                        </div>
                    @else
                        <!-- VS Display -->
                        <div class="text-center">
                            <div class="w-10 h-10 fold:w-12 fold:h-12 rounded-full bg-gradient-to-br from-soboa-orange to-orange-600 flex items-center justify-center shadow-lg transform group-hover:rotate-12 transition-transform duration-300">
                                <span class="text-sm fold:text-lg font-black text-white">VS</span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Away Team -->
                <div class="flex-1 min-w-[100px] text-center group/team">
                    <div class="relative inline-block mb-2 fold:mb-3">
                        @if($awayFlag)
                            <div class="w-12 h-12 fold:w-14 fold:h-14 rounded-full overflow-hidden shadow-lg ring-2 fold:ring-4 ring-white group-hover/team:ring-soboa-orange transition-all duration-300 transform group-hover/team:scale-110 away-flag-container">
                                <img src="{{ $awayFlag }}"
                                     alt="{{ $awayName }}"
                                     class="w-full h-full object-cover away-flag-img"
                                     onerror="this.parentElement.outerHTML='<div class=\'w-20 h-20 rounded-full bg-gradient-to-br from-soboa-blue to-blue-600 shadow-lg ring-4 ring-white flex items-center justify-center group-hover/team:ring-soboa-orange transition-all duration-300 transform group-hover/team:scale-110\'><span class=\'text-2xl font-black text-white\'>{{ mb_substr($match->team_b, 0, 2) }}</span></div>'">
                            </div>
                        @else
                            <div class="w-12 h-12 fold:w-14 fold:h-14 rounded-full bg-gradient-to-br from-soboa-blue to-blue-600 shadow-lg ring-2 fold:ring-4 ring-white flex items-center justify-center group-hover/team:ring-soboa-orange transition-all duration-300 transform group-hover/team:scale-110">
                                <span class="text-base fold:text-lg font-black text-white">{{ mb_substr($awayName, 0, 2) }}</span>
                            </div>
                        @endif
                    </div>
                    <h3 class="font-black text-gray-800 text-sm fold:text-base leading-tight px-1 fold:px-2">
                        {{ $awayName }}
                    </h3>
                </div>
            </div>
        @endif

        <!-- Tendance des pronostics (agrégée, anonyme) -->
        @if(!$isTbd && $trendTotal > 0)
            <div class="mb-4">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wide">Tendance des pronostics</span>
                    <span class="text-[11px] text-gray-400 font-medium">{{ $trendTotal }} {{ $trendTotal > 1 ? 'pronostics' : 'pronostic' }}</span>
                </div>
                <div class="flex h-5 rounded-lg overflow-hidden text-white text-[11px] font-black">
                    @if($pctHome > 0)
                        <div class="bg-soboa-blue flex items-center justify-center min-w-0" style="width: {{ $pctHome }}%" title="{{ $homeName }} : {{ $pctHome }}%">{{ $pctHome }}%</div>
                    @endif
                    @if($pctDraw > 0)
                        <div class="bg-gray-400 flex items-center justify-center min-w-0" style="width: {{ $pctDraw }}%" title="Nul : {{ $pctDraw }}%">{{ $pctDraw }}%</div>
                    @endif
                    @if($pctAway > 0)
                        <div class="bg-soboa-orange flex items-center justify-center min-w-0" style="width: {{ $pctAway }}%" title="{{ $awayName }} : {{ $pctAway }}%">{{ $pctAway }}%</div>
                    @endif
                </div>
                <div class="flex justify-between text-[11px] font-bold mt-1.5 gap-2">
                    <span class="text-soboa-blue truncate">{{ $homeName }}</span>
                    <span class="text-gray-500 flex-shrink-0">Nul</span>
                    <span class="text-soboa-orange truncate text-right">{{ $awayName }}</span>
                </div>
            </div>
        @endif

        <!-- Action Button -->
        @if(!$isFinished)
            @if(session('user_id'))
                <a href="/matches#match-{{ $match->id }}"
                   class="block w-full bg-gradient-to-r from-soboa-orange to-soboa-orange-secondary hover:from-soboa-orange-secondary hover:to-soboa-orange text-white font-black py-3 rounded-xl text-center transition-all duration-base transform hover:scale-[1.02] hover:shadow-elev-3 flex items-center justify-center gap-2 group/button focus:outline-none focus:ring-2 focus:ring-soboa-orange focus:ring-offset-2">
                    <i data-lucide="star" class="w-4 h-4 group-hover/button:rotate-12 transition-transform"></i>
                    <span class="text-base">Pronostiquer maintenant</span>
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover/button:translate-x-1 transition-transform"></i>
                </a>
            @else
                <a href="/login"
                   class="block w-full bg-gradient-to-r from-soboa-blue to-soboa-blue-light hover:from-soboa-blue-light hover:to-soboa-blue text-white font-black py-3 rounded-xl text-center transition-all duration-base transform hover:scale-[1.02] hover:shadow-elev-3 flex items-center justify-center gap-2 group/button focus:outline-none focus:ring-2 focus:ring-soboa-blue focus:ring-offset-2">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span class="text-base">Se connecter pour pronostiquer</span>
                </a>
            @endif

            {{-- Actions secondaires compactes : commentaires + rappel sur une ligne --}}
            @if(!$isTbd)
                <div class="flex gap-2 mt-2">
                    <button type="button"
                            onclick="window.dispatchEvent(new CustomEvent('open-match-wall', { detail: { matchId: {{ $match->id }} } }))"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 text-xs font-bold text-soboa-blue hover:text-white hover:bg-soboa-blue ring-1 ring-soboa-blue/20 py-2 rounded-lg transition-colors">
                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                        Commentaires
                        @if(($match->comments_count ?? 0) > 0)
                            <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-soboa-orange text-white text-[10px] font-black">{{ $match->comments_count }}</span>
                        @endif
                    </button>

                    @if($isUpcoming)
                        @php
                            $calStart = $match->match_date->copy()->utc()->format('Ymd\THis\Z');
                            $calEnd = $match->match_date->copy()->utc()->addHours(2)->format('Ymd\THis\Z');
                            $calUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
                                . '&text=' . rawurlencode("⚽ {$homeName} vs {$awayName} — SOBOA FOOT TIME")
                                . '&dates=' . $calStart . '/' . $calEnd
                                . '&details=' . rawurlencode("Le match commence ! Pronostique avant le coup d'envoi : " . url('/matches') . '#match-' . $match->id);
                        @endphp
                        <a href="{{ $calUrl }}" target="_blank" rel="noopener"
                           class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 rounded-lg text-xs font-bold text-soboa-blue bg-soboa-blue/5 hover:bg-soboa-blue/10 ring-1 ring-soboa-blue/20 transition-colors focus:outline-none focus:ring-2 focus:ring-soboa-blue">
                            <i data-lucide="bell-ring" class="w-3.5 h-3.5"></i>
                            Me rappeler
                        </a>
                    @endif
                </div>
            @endif
        @else
            <!-- Finished Match - Show result summary -->
            <div class="bg-gradient-to-r from-gray-100 to-gray-200 p-3 rounded-xl text-center">
                <div class="flex items-center justify-center gap-2 text-gray-600">
                    <i data-lucide="badge-check" class="w-5 h-5"></i>
                    <span class="font-bold">Match terminé</span>
                </div>
            </div>
            @if(!$isTbd)
                <button type="button"
                        onclick="window.dispatchEvent(new CustomEvent('open-match-wall', { detail: { matchId: {{ $match->id }} } }))"
                        class="w-full inline-flex items-center justify-center gap-1.5 text-xs font-bold text-soboa-blue hover:text-white hover:bg-soboa-blue ring-1 ring-soboa-blue/20 py-2 mt-2 rounded-lg transition-colors">
                    <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                    Commentaires
                    @if(($match->comments_count ?? 0) > 0)
                        <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-soboa-orange text-white text-[10px] font-black">{{ $match->comments_count }}</span>
                    @endif
                </button>
            @endif
        @endif

    </div>

    <!-- Bottom Accent -->
    <div class="h-2 bg-gradient-to-r from-soboa-orange via-soboa-blue to-soboa-orange"></div>
</div>
