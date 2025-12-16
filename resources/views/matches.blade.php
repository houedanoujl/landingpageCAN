<x-layouts.app title="Matchs">
    <div class="space-y-6" x-data="{ activePhase: 'all', activeGroup: 'all' }">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-soboa-blue">Calendrier des Matchs</h1>
            <span class="text-sm text-gray-500">CAN 2025 - Maroc</span>
        </div>

        <!-- Bannière du point de vente sélectionné -->
        @if(isset($selectedVenue))
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-4 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-white/80">Point de vente actuel</p>
                            <p class="font-bold text-lg">{{ $selectedVenue->name }}</p>
                        </div>
                    </div>
                    <a href="{{ route('venues') }}"
                        class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm font-medium transition">
                        Changer
                    </a>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg" role="alert">
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg" role="alert">
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Onglets de Phases (niveau 1) -->
        <div class="bg-white rounded-xl shadow-sm p-4 sticky top-[64px] md:top-[80px] z-40 border border-gray-200">
            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                <button @click="activePhase = 'all'; activeGroup = 'all'"
                    :class="activePhase === 'all' ? 'bg-soboa-blue text-white' : 'bg-gray-100 text-gray-600 hover:bg-soboa-orange hover:text-white'"
                    class="px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition">
                    Tous
                </button>

                @php $hasMatches = ($phaseCounts['group_stage'] ?? 0) > 0; @endphp
                <button @click="activePhase = 'group_stage'; activeGroup = 'all'"
                    :class="activePhase === 'group_stage' ? 'bg-soboa-blue text-white' : 'bg-gray-100 text-gray-600 hover:bg-soboa-orange hover:text-white'"
                    class="px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition {{ !$hasMatches ? 'opacity-50 cursor-not-allowed' : '' }}"
                    {{ !$hasMatches ? 'disabled' : '' }}>
                    Poules
                    @if($hasMatches)
                        <span class="ml-1 text-xs bg-white/20 px-1.5 py-0.5 rounded-full">{{ $phaseCounts['group_stage'] }}</span>
                    @endif
                </button>

                @php $hasMatches = ($phaseCounts['round_of_16'] ?? 0) > 0; @endphp
                <button @click="activePhase = 'round_of_16'; activeGroup = 'all'"
                    :class="activePhase === 'round_of_16' ? 'bg-soboa-orange text-white' : 'bg-gray-100 text-gray-600 hover:bg-soboa-orange hover:text-white'"
                    class="px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition {{ !$hasMatches ? 'opacity-50 cursor-not-allowed' : '' }}"
                    {{ !$hasMatches ? 'disabled' : '' }}>
                    1/8e finale
                    @if($hasMatches)
                        <span class="ml-1 text-xs bg-white/20 px-1.5 py-0.5 rounded-full">{{ $phaseCounts['round_of_16'] }}</span>
                    @endif
                </button>

                @php $hasMatches = ($phaseCounts['quarter_final'] ?? 0) > 0; @endphp
                <button @click="activePhase = 'quarter_final'; activeGroup = 'all'"
                    :class="activePhase === 'quarter_final' ? 'bg-soboa-orange text-white' : 'bg-gray-100 text-gray-600 hover:bg-soboa-orange hover:text-white'"
                    class="px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition {{ !$hasMatches ? 'opacity-50 cursor-not-allowed' : '' }}"
                    {{ !$hasMatches ? 'disabled' : '' }}>
                    1/4 finale
                    @if($hasMatches)
                        <span class="ml-1 text-xs bg-white/20 px-1.5 py-0.5 rounded-full">{{ $phaseCounts['quarter_final'] }}</span>
                    @endif
                </button>

                @php $hasMatches = ($phaseCounts['semi_final'] ?? 0) > 0; @endphp
                <button @click="activePhase = 'semi_final'; activeGroup = 'all'"
                    :class="activePhase === 'semi_final' ? 'bg-soboa-orange text-white' : 'bg-gray-100 text-gray-600 hover:bg-soboa-orange hover:text-white'"
                    class="px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition {{ !$hasMatches ? 'opacity-50 cursor-not-allowed' : '' }}"
                    {{ !$hasMatches ? 'disabled' : '' }}>
                    Demi-finales
                    @if($hasMatches)
                        <span class="ml-1 text-xs bg-white/20 px-1.5 py-0.5 rounded-full">{{ $phaseCounts['semi_final'] }}</span>
                    @endif
                </button>

                @php $hasMatches = ($phaseCounts['third_place'] ?? 0) > 0; @endphp
                <button @click="activePhase = 'third_place'; activeGroup = 'all'"
                    :class="activePhase === 'third_place' ? 'bg-soboa-orange text-white' : 'bg-gray-100 text-gray-600 hover:bg-soboa-orange hover:text-white'"
                    class="px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition {{ !$hasMatches ? 'opacity-50 cursor-not-allowed' : '' }}"
                    {{ !$hasMatches ? 'disabled' : '' }}>
                    3e place
                    @if($hasMatches)
                        <span class="ml-1 text-xs bg-white/20 px-1.5 py-0.5 rounded-full">{{ $phaseCounts['third_place'] }}</span>
                    @endif
                </button>

                @php $hasMatches = ($phaseCounts['final'] ?? 0) > 0; @endphp
                <button @click="activePhase = 'final'; activeGroup = 'all'"
                    :class="activePhase === 'final' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-green-600 hover:text-white'"
                    class="px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition {{ !$hasMatches ? 'opacity-50 cursor-not-allowed' : '' }}"
                    {{ !$hasMatches ? 'disabled' : '' }}>
                    Finale
                    @if($hasMatches)
                        <span class="ml-1 text-xs bg-white/20 px-1.5 py-0.5 rounded-full">{{ $phaseCounts['final'] }}</span>
                    @endif
                </button>
            </div>
        </div>

        <!-- Onglets de Groupes (niveau 2) - Visible uniquement pour la phase de poules -->
        <div class="bg-white rounded-xl shadow-sm p-4 sticky top-[120px] md:top-[140px] z-30 border border-gray-200"
             x-show="activePhase === 'all' || activePhase === 'group_stage'"
             x-transition>
            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                <button @click="activeGroup = 'all'"
                    :class="activeGroup === 'all' ? 'bg-soboa-blue text-white' : 'bg-gray-100 text-gray-600 hover:bg-soboa-orange hover:text-white'"
                    class="px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition">
                    Tous les groupes
                </button>
                @foreach(['A', 'B', 'C', 'D', 'E', 'F'] as $group)
                    @php $hasMatches = ($groupCounts[$group] ?? 0) > 0; @endphp
                    <button @click="activeGroup = '{{ $group }}'"
                        :class="activeGroup === '{{ $group }}' ? 'bg-soboa-blue text-white' : 'bg-gray-100 text-gray-600 hover:bg-soboa-orange hover:text-white'"
                        class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition {{ !$hasMatches ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ !$hasMatches ? 'disabled' : '' }}>
                        Groupe {{ $group }}
                        @if($hasMatches)
                            <span class="ml-1 text-xs bg-white/20 px-1.5 py-0.5 rounded-full">{{ $groupCounts[$group] }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Prochains matchs en carousel (uniquement sur l'onglet "Tous") -->
        @if($upcomingMatches && count($upcomingMatches) > 0)
        <div class="mb-8" x-show="activePhase === 'all'" x-transition>
            <div class="bg-gradient-to-r from-soboa-orange to-orange-500 rounded-xl p-6 text-white shadow-lg">
                <!-- Header -->
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-3xl">🔥</span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-2xl font-black">Prochains matchs</h2>
                            <span class="text-2xl">🇸🇳</span>
                        </div>
                        <p class="text-white/80 text-sm">À ne pas manquer!</p>
                    </div>
                </div>

                <!-- Swiper Container -->
                <div class="swiper upcomingMatchesSwiper" x-init="initUpcomingMatchesSwiper()">
                    <div class="swiper-wrapper">
                        @foreach($upcomingMatches as $match)
                        <div class="swiper-slide">
                            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20 hover:bg-white/20 transition">
                                <!-- Date -->
                                <div class="text-xs text-white/70 mb-2 text-center font-medium">
                                    📅 {{ $match->match_date->translatedFormat('l d F Y') }}
                                </div>

                                <!-- Match Info -->
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1 text-right">
                                                <p class="font-bold text-lg">{{ $match->team_a }}</p>
                                            </div>
                                            <div class="flex flex-col items-center gap-1 px-3">
                                                <span class="text-sm font-bold text-white/80">VS</span>
                                                <span class="text-xs font-bold bg-white/20 px-2 py-1 rounded">
                                                    {{ $match->match_date->format('H:i') }}
                                                </span>
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-bold text-lg">{{ $match->team_b }}</p>
                                            </div>
                                        </div>
                                        @if($match->stadium)
                                        <p class="text-xs text-white/70 mt-2 text-center">📍 {{ $match->stadium }}</p>
                                        @endif
                                    </div>
                                </div>

                                <!-- CTA Parier -->
                                <a href="#match-{{ $match->id }}"
                                   class="block w-full bg-white hover:bg-white/90 text-soboa-orange font-bold py-2 px-4 rounded-lg text-center transition shadow-md hover:shadow-lg"
                                   onclick="scrollToMatch({{ $match->id }})">
                                    🎯 Parier maintenant
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Navigation -->
                    <!-- <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-pagination"></div> -->
                </div>
            </div>
        </div>
        @endif

        <!-- Matchs par phase -->
        @forelse($matchesByPhase as $phase => $phaseData)
            @if($phase === 'group_stage')
                {{-- Phase de poules : afficher par groupes --}}
                @foreach($phaseData as $groupName => $groupMatches)
                    <div class="space-y-4"
                         x-show="(activePhase === 'all' || activePhase === 'group_stage') && (activeGroup === 'all' || activeGroup === '{{ $groupName }}')"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0">
                        <!-- En-tête du groupe -->
                        <div class="flex items-center gap-3">
                            <div class="bg-soboa-blue text-white px-4 py-2 rounded-lg font-bold text-lg shadow">
                                Groupe {{ $groupName ?: 'N/A' }}
                            </div>
                            <div class="flex-1 h-0.5 bg-soboa-blue/20 rounded"></div>
                            <span class="text-sm text-gray-500">{{ $groupMatches->count() }} matchs</span>
                        </div>

                        <!-- Liste des matchs du groupe -->
                @foreach($groupMatches as $match)
                    @php
                        $isFavoriteMatch = false;
                        if (isset($favoriteTeamId) && $favoriteTeamId) {
                            $isFavoriteMatch = ($match->home_team_id == $favoriteTeamId || $match->away_team_id == $favoriteTeamId);
                        }
                    @endphp
                    <div data-match-id="{{ $match->id }}"
                        class="bg-white rounded-lg shadow p-5 border-l-4 {{ $match->status === 'finished' ? 'border-gray-400' : ($isFavoriteMatch ? 'border-green-500' : 'border-soboa-orange') }} {{ $isFavoriteMatch ? 'bg-gradient-to-r from-green-50 via-white to-yellow-50' : '' }} relative">
                        <!-- Badge pour match favori -->
                        @if($isFavoriteMatch)
                        <div class="absolute top-2 right-2 bg-green-600 text-white text-xs font-bold px-2 py-1 rounded-full flex items-center gap-1 shadow-lg z-10">
                            <span>⭐</span>
                            <span>À suivre</span>
                        </div>
                        @endif

                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <span
                                    class="text-xs font-bold uppercase text-soboa-blue tracking-wide">{{ $match->match_date->translatedFormat('l d F Y') }}</span>
                                <div class="text-sm text-gray-500">📍 {{ $match->stadium }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 bg-soboa-blue/10 text-soboa-blue text-xs font-bold rounded">Groupe
                                    {{ $match->group_name ?: 'N/A' }}</span>
                                @if($match->status === 'finished')
                                    <span class="px-2 py-1 bg-gray-200 text-gray-600 text-xs font-bold rounded">Terminé</span>
                                @else
                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded">À venir</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <!-- Team A -->
                            <div class="flex-1 flex flex-col items-center">
                                @if($match->homeTeam)
                                    <img src="https://flagcdn.com/w80/{{ $match->homeTeam->iso_code }}.png"
                                        alt="{{ $match->team_a }}" class="w-16 h-12 object-cover rounded shadow mb-2">
                                @else
                                    <div
                                        class="w-16 h-12 bg-soboa-blue/10 rounded flex items-center justify-center mb-2 shadow-inner">
                                        <span class="text-lg font-bold text-soboa-blue">{{ mb_substr($match->team_a, 0, 3) }}</span>
                                    </div>
                                @endif
                                <span
                                    class="font-bold text-lg text-center leading-tight text-gray-800">{{ $match->team_a }}</span>
                            </div>

                            <!-- Score / Time -->
                            <div class="px-4 text-center">
                                @if($match->status === 'finished')
                                    <div class="text-3xl font-black text-gray-800 tracking-widest">
                                        {{ $match->score_a ?? 0 }} - {{ $match->score_b ?? 0 }}
                                    </div>
                                @else
                                    <div class="text-2xl font-black text-gray-300">VS</div>
                                    <div class="text-sm font-bold text-soboa-orange mt-1">🕐 {{ $match->match_date->format('H:i') }}
                                    </div>
                                @endif
                            </div>

                            <!-- Team B -->
                            <div class="flex-1 flex flex-col items-center">
                                @if($match->awayTeam)
                                    <img src="https://flagcdn.com/w80/{{ $match->awayTeam->iso_code }}.png"
                                        alt="{{ $match->team_b }}" class="w-16 h-12 object-cover rounded shadow mb-2">
                                @else
                                    <div
                                        class="w-16 h-12 bg-soboa-blue/10 rounded flex items-center justify-center mb-2 shadow-inner">
                                        <span class="text-lg font-bold text-soboa-blue">{{ mb_substr($match->team_b, 0, 3) }}</span>
                                    </div>
                                @endif
                                <span
                                    class="font-bold text-lg text-center leading-tight text-gray-800">{{ $match->team_b }}</span>
                            </div>
                        </div>

                        @if($match->status !== 'finished' && $match->match_date > now())
                            <div class="mt-6 border-t pt-4">
                                @if(session('user_id'))
                                    @php
                                        $userPrediction = $userPredictions[$match->id] ?? null;
                                        $lockTime = $match->match_date->copy()->subMinutes(2);
                                        $isLocked = now()->gte($lockTime);
                                    @endphp

                                    @if($isLocked)
                                        <!-- Match verrouillé (moins de 2 minutes avant le coup d'envoi) -->
                                        <div class="text-center">
                                            <div class="bg-gray-100 border border-gray-200 rounded-xl p-4">
                                                <div class="flex items-center justify-center gap-2 text-gray-600 mb-2">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                                        </path>
                                                    </svg>
                                                    <span class="font-bold">Pronostics fermés</span>
                                                </div>
                                                @if($userPrediction)
                                                    <div class="text-gray-700">
                                                        <span class="font-medium">Votre pronostic :</span>
                                                        <span class="text-xl font-black text-soboa-orange mx-2">
                                                            {{ $userPrediction->score_a }} - {{ $userPrediction->score_b }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <p class="text-gray-500 text-sm">Vous n'avez pas pronostiqué sur ce match</p>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <!-- Formulaire de pronostic (création ou modification) -->
                                        <form action="{{ route('predictions.store') }}" method="POST" class="prediction-form space-y-4" data-match-id="{{ $match->id }}">
                                            @csrf
                                            <input type="hidden" name="match_id" value="{{ $match->id }}">
                                            <input type="hidden" name="venue_id" value="{{ $selectedVenue->id ?? '' }}">

                                            <!-- Message de statut -->
                                            <div class="prediction-message hidden text-sm rounded-lg p-3 mb-3"></div>

                                            @if($userPrediction)
                                                <div class="flex items-center justify-center gap-2 text-green-600 text-sm mb-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    <span class="font-medium">Pronostic enregistré - Vous pouvez le modifier</span>
                                                </div>
                                            @else
                                                <p class="text-sm text-gray-600 text-center font-medium">Entrez votre pronostic :</p>
                                            @endif

                                            <div class="flex items-center justify-center gap-4">
                                                <!-- Score équipe A -->
                                                <div class="flex flex-col items-center">
                                                    <label class="text-xs text-gray-500 mb-1">{{ $match->team_a }}</label>
                                                    <input type="number" name="score_a" min="0" max="20"
                                                        value="{{ $userPrediction ? $userPrediction->score_a : 0 }}"
                                                        class="w-16 h-12 text-center text-2xl font-bold border-2 {{ $userPrediction ? 'border-green-400 bg-green-50' : 'border-gray-300' }} rounded-lg focus:border-soboa-orange focus:ring-soboa-orange"
                                                        required>
                                                </div>

                                                <span class="text-2xl font-bold text-gray-400">-</span>

                                                <!-- Score équipe B -->
                                                <div class="flex flex-col items-center">
                                                    <label class="text-xs text-gray-500 mb-1">{{ $match->team_b }}</label>
                                                    <input type="number" name="score_b" min="0" max="20"
                                                        value="{{ $userPrediction ? $userPrediction->score_b : 0 }}"
                                                        class="w-16 h-12 text-center text-2xl font-bold border-2 {{ $userPrediction ? 'border-green-400 bg-green-50' : 'border-gray-300' }} rounded-lg focus:border-soboa-orange focus:ring-soboa-orange"
                                                        required>
                                                </div>
                                            </div>

                                            <button type="submit"
                                                class="prediction-submit w-full {{ $userPrediction ? 'bg-green-600 hover:bg-green-700' : 'bg-soboa-orange hover:bg-orange-600' }} text-white font-bold py-3 px-4 rounded-lg shadow transition transform active:scale-95 flex items-center justify-center gap-2">
                                                <svg class="submit-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span class="submit-text">{{ $userPrediction ? 'Modifier mon pronostic' : 'Valider mon pronostic' }}</span>
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <!-- Message pour inviter à se connecter -->
                                    <div class="text-center">
                                        <p class="text-gray-600 mb-3">Connectez-vous pour faire vos pronostics</p>
                                        <a href="/login"
                                            class="inline-block bg-soboa-orange hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-lg shadow transition">
                                            Se connecter
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @elseif($match->status !== 'finished')
                            <div class="mt-6 border-t pt-4">
                                <div class="text-center text-gray-500 text-sm">
                                    <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Match en cours - Pronostics fermés
                                </div>
                            </div>
                        @endif
                    </div>
                        @endforeach
                    </div>
                @endforeach
            @else
                {{-- Phases finales : afficher directement les matchs --}}
                <div class="space-y-4"
                     x-show="activePhase === 'all' || activePhase === '{{ $phase }}'"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0">
                    <!-- En-tête de la phase -->
                    <div class="flex items-center gap-3">
                        <div class="bg-soboa-orange text-white px-4 py-2 rounded-lg font-bold text-lg shadow">
                            @php
                                $phaseName = match($phase) {
                                    'round_of_16' => '1/8e de Finale',
                                    'quarter_final' => 'Quart de Finale',
                                    'semi_final' => 'Demi-Finales',
                                    'third_place' => 'Match pour la 3e Place',
                                    'final' => 'Finale',
                                    default => ucfirst(str_replace('_', ' ', $phase))
                                };
                            @endphp
                            {{ $phaseName }}
                        </div>
                        <div class="flex-1 h-0.5 bg-soboa-orange/20 rounded"></div>
                        <span class="text-sm text-gray-500">{{ $phaseData->count() }} match(s)</span>
                    </div>

                    <!-- Liste des matchs de la phase -->
                    @foreach($phaseData as $match)
                        @php
                            $isFavoriteMatch = false;
                            if (isset($favoriteTeamId) && $favoriteTeamId) {
                                $isFavoriteMatch = ($match->home_team_id == $favoriteTeamId || $match->away_team_id == $favoriteTeamId);
                            }
                        @endphp
                        <div data-match-id="{{ $match->id }}"
                            class="bg-white rounded-lg shadow p-5 border-l-4 {{ $match->status === 'finished' ? 'border-gray-400' : ($isFavoriteMatch ? 'border-green-500' : 'border-soboa-orange') }} {{ $isFavoriteMatch ? 'bg-gradient-to-r from-green-50 via-white to-yellow-50' : '' }} relative">
                            <!-- Badge pour match favori -->
                            @if($isFavoriteMatch)
                            <div class="absolute top-2 right-2 bg-green-600 text-white text-xs font-bold px-2 py-1 rounded-full flex items-center gap-1 shadow-lg z-10">
                                <span>⭐</span>
                                <span>À suivre</span>
                            </div>
                            @endif

                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <span
                                        class="text-xs font-bold uppercase text-soboa-blue tracking-wide">{{ $match->match_date->translatedFormat('l d F Y') }}</span>
                                    <div class="text-sm text-gray-500">📍 {{ $match->stadium }}</div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 bg-soboa-orange/10 text-soboa-orange text-xs font-bold rounded">
                                        {{ $phaseName }}
                                    </span>
                                    @if($match->status === 'finished')
                                        <span class="px-2 py-1 bg-gray-200 text-gray-600 text-xs font-bold rounded">Terminé</span>
                                    @else
                                        <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded">À venir</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <!-- Team A -->
                                <div class="flex-1 flex flex-col items-center">
                                    @if($match->homeTeam)
                                        <img src="https://flagcdn.com/w80/{{ $match->homeTeam->iso_code }}.png"
                                            alt="{{ $match->team_a }}" class="w-16 h-12 object-cover rounded shadow mb-2">
                                    @else
                                        <div
                                            class="w-16 h-12 bg-soboa-blue/10 rounded flex items-center justify-center mb-2 shadow-inner">
                                            <span class="text-lg font-bold text-soboa-blue">{{ mb_substr($match->team_a, 0, 3) }}</span>
                                        </div>
                                    @endif
                                    <span
                                        class="font-bold text-lg text-center leading-tight text-gray-800">{{ $match->team_a }}</span>
                                </div>

                                <!-- Score / Time -->
                                <div class="px-4 text-center">
                                    @if($match->status === 'finished')
                                        <div class="text-3xl font-black text-gray-800 tracking-widest">
                                            {{ $match->score_a ?? 0 }} - {{ $match->score_b ?? 0 }}
                                        </div>
                                    @else
                                        <div class="text-2xl font-black text-gray-300">VS</div>
                                        <div class="text-sm font-bold text-soboa-orange mt-1">🕐 {{ $match->match_date->format('H:i') }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Team B -->
                                <div class="flex-1 flex flex-col items-center">
                                    @if($match->awayTeam)
                                        <img src="https://flagcdn.com/w80/{{ $match->awayTeam->iso_code }}.png"
                                            alt="{{ $match->team_b }}" class="w-16 h-12 object-cover rounded shadow mb-2">
                                    @else
                                        <div
                                            class="w-16 h-12 bg-soboa-blue/10 rounded flex items-center justify-center mb-2 shadow-inner">
                                            <span class="text-lg font-bold text-soboa-blue">{{ mb_substr($match->team_b, 0, 3) }}</span>
                                        </div>
                                    @endif
                                    <span
                                        class="font-bold text-lg text-center leading-tight text-gray-800">{{ $match->team_b }}</span>
                                </div>
                            </div>

                            @if($match->status !== 'finished' && $match->match_date > now())
                                <div class="mt-6 border-t pt-4">
                                    @if(session('user_id'))
                                        @php
                                            $userPrediction = $userPredictions[$match->id] ?? null;
                                            $lockTime = $match->match_date->copy()->subMinutes(2);
                                            $isLocked = now()->gte($lockTime);
                                        @endphp

                                        @if($isLocked)
                                            <!-- Match verrouillé (moins de 2 minutes avant le coup d'envoi) -->
                                            <div class="text-center">
                                                <div class="bg-gray-100 border border-gray-200 rounded-xl p-4">
                                                    <div class="flex items-center justify-center gap-2 text-gray-600 mb-2">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                                            </path>
                                                        </svg>
                                                        <span class="font-bold">Pronostics fermés</span>
                                                    </div>
                                                    @if($userPrediction)
                                                        <div class="text-gray-700">
                                                            <span class="font-medium">Votre pronostic :</span>
                                                            <span class="text-xl font-black text-soboa-orange mx-2">
                                                                {{ $userPrediction->score_a }} - {{ $userPrediction->score_b }}
                                                            </span>
                                                        </div>
                                                    @else
                                                        <p class="text-gray-500 text-sm">Vous n'avez pas pronostiqué sur ce match</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <!-- Formulaire de pronostic (création ou modification) -->
                                            <form action="{{ route('predictions.store') }}" method="POST" class="prediction-form space-y-4" data-match-id="{{ $match->id }}">
                                                @csrf
                                                <input type="hidden" name="match_id" value="{{ $match->id }}">
                                                <input type="hidden" name="venue_id" value="{{ $selectedVenue->id ?? '' }}">

                                                <!-- Message de statut -->
                                                <div class="prediction-message hidden text-sm rounded-lg p-3 mb-3"></div>

                                                @if($userPrediction)
                                                    <div class="flex items-center justify-center gap-2 text-green-600 text-sm mb-2">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        <span class="font-medium">Pronostic enregistré - Vous pouvez le modifier</span>
                                                    </div>
                                                @else
                                                    <p class="text-sm text-gray-600 text-center font-medium">Entrez votre pronostic :</p>
                                                @endif

                                                <div class="flex items-center justify-center gap-4">
                                                    <!-- Score équipe A -->
                                                    <div class="flex flex-col items-center">
                                                        <label class="text-xs text-gray-500 mb-1">{{ $match->team_a }}</label>
                                                        <input type="number" name="score_a" min="0" max="20"
                                                            value="{{ $userPrediction ? $userPrediction->score_a : 0 }}"
                                                            class="w-16 h-12 text-center text-2xl font-bold border-2 {{ $userPrediction ? 'border-green-400 bg-green-50' : 'border-gray-300' }} rounded-lg focus:border-soboa-orange focus:ring-soboa-orange"
                                                            required>
                                                    </div>

                                                    <span class="text-2xl font-bold text-gray-400">-</span>

                                                    <!-- Score équipe B -->
                                                    <div class="flex flex-col items-center">
                                                        <label class="text-xs text-gray-500 mb-1">{{ $match->team_b }}</label>
                                                        <input type="number" name="score_b" min="0" max="20"
                                                            value="{{ $userPrediction ? $userPrediction->score_b : 0 }}"
                                                            class="w-16 h-12 text-center text-2xl font-bold border-2 {{ $userPrediction ? 'border-green-400 bg-green-50' : 'border-gray-300' }} rounded-lg focus:border-soboa-orange focus:ring-soboa-orange"
                                                            required>
                                                    </div>
                                                </div>

                                                <!-- Submit button -->
                                                <button type="submit"
                                                    class="w-full {{ $userPrediction ? 'bg-green-600 hover:bg-green-700' : 'bg-soboa-orange hover:bg-orange-600' }} text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:shadow-xl transition duration-200 flex items-center justify-center gap-2">
                                                    <span class="prediction-icon">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </span>
                                                    <span class="prediction-text">{{ $userPrediction ? 'Modifier mon pronostic' : 'Valider mon pronostic' }}</span>
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <!-- Non connecté -->
                                        <div class="text-center">
                                            <p class="text-gray-600 mb-3">Connectez-vous pour faire vos pronostics</p>
                                            <a href="/login"
                                                class="inline-block bg-soboa-orange hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-lg shadow transition">
                                                Se connecter
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @elseif($match->status !== 'finished')
                                <div class="mt-6 border-t pt-4">
                                    <div class="text-center text-gray-500 text-sm">
                                        <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Match en cours - Pronostics fermés
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        @empty
            <div class="text-center py-10">
                <p class="text-gray-500">Aucun match trouvé.</p>
            </div>
        @endforelse
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle all prediction forms with AJAX
            const predictionForms = document.querySelectorAll('.prediction-form');

            predictionForms.forEach(form => {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const submitButton = form.querySelector('.prediction-submit');
                    const submitIcon = form.querySelector('.submit-icon');
                    const submitText = form.querySelector('.submit-text');
                    const messageDiv = form.querySelector('.prediction-message');
                    const originalText = submitText.textContent;

                    // Disable button and show loading state
                    submitButton.disabled = true;
                    submitButton.classList.add('opacity-75', 'cursor-not-allowed');
                    submitIcon.innerHTML = `<svg class="animate-spin h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>`;
                    submitText.textContent = 'Enregistrement...';

                    // Clear previous messages
                    messageDiv.classList.add('hidden');
                    messageDiv.textContent = '';

                    try {
                        const formData = new FormData(form);
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (response.ok) {
                            // Show success message
                            messageDiv.classList.remove('hidden');
                            messageDiv.classList.add('bg-green-100', 'border', 'border-green-400', 'text-green-700');
                            messageDiv.textContent = data.message || 'Pronostic enregistré avec succès!';

                            // Update button appearance for updated prediction
                            submitButton.classList.remove('bg-soboa-orange', 'hover:bg-orange-600');
                            submitButton.classList.add('bg-green-600', 'hover:bg-green-700');
                            submitText.textContent = 'Modifier mon pronostic';

                            // Déclencher toast notification
                            window.dispatchEvent(new CustomEvent('show-toast', {
                                detail: {
                                    type: 'success',
                                    message: 'Pronostic enregistré ! 🎯',
                                    description: (data.teams || 'Votre pronostic') + ' (depuis ' + (data.venue || 'le point de vente') + ') • +1 pt + jusqu\'à 6 pts bonus'
                                }
                            }));

                            // Mettre à jour les points du header
                            if (data.user_points_total !== undefined) {
                                window.dispatchEvent(new CustomEvent('update-points', {
                                    detail: {
                                        points: data.user_points_total
                                    }
                                }));
                            }

                            // Hide success message after 3 seconds
                            setTimeout(() => {
                                messageDiv.classList.add('hidden');
                            }, 3000);
                        } else {
                            // Show error message
                            messageDiv.classList.remove('hidden');
                            messageDiv.classList.add('bg-red-100', 'border', 'border-red-400', 'text-red-700');
                            messageDiv.textContent = data.message || 'Une erreur est survenue.';
                        }
                    } catch (error) {
                        // Show error message
                        messageDiv.classList.remove('hidden');
                        messageDiv.classList.add('bg-red-100', 'border', 'border-red-400', 'text-red-700');
                        messageDiv.textContent = 'Erreur de connexion. Veuillez réessayer.';
                        console.error('Error:', error);
                    } finally {
                        // Re-enable button and restore icon
                        submitButton.disabled = false;
                        submitButton.classList.remove('opacity-75', 'cursor-not-allowed');
                        submitIcon.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>`;
                        submitText.textContent = originalText;
                    }
                });
            });
        });
    </script>
</x-layouts.app>