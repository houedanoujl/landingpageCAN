<x-layouts.app title="Pronostics">
    <!-- Popup Récap Points (cachée par défaut) -->
    <div id="pointsRecapModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="modalContent">
            <!-- Header -->
            <div class="bg-gradient-to-r from-soboa-orange to-yellow-500 p-6 text-center">
                <div class="w-20 h-20 bg-white rounded-full mx-auto flex items-center justify-center mb-3 animate-bounce">
                    <span class="text-4xl">🎯</span>
                </div>
                <h3 class="text-2xl font-black text-white">Pronostic Enregistré !</h3>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-4">
                <!-- Match info -->
                <div class="bg-gray-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-gray-600 font-medium mb-2">Votre pronostic</p>
                    <p class="text-xl font-black text-gray-800" id="modalMatchInfo">Match Info</p>
                    <div class="flex items-center justify-center gap-3 mt-2">
                        <span class="text-3xl font-black text-soboa-blue" id="modalScoreA">0</span>
                        <span class="text-gray-400">-</span>
                        <span class="text-3xl font-black text-soboa-blue" id="modalScoreB">0</span>
                    </div>
                </div>

                <!-- Points détail -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between bg-blue-50 rounded-lg p-3">
                        <div class="flex items-center gap-2">
                            <span>✅</span>
                            <span class="font-medium text-gray-700">Participation</span>
                        </div>
                        <span class="font-black text-blue-600">+1 pt</span>
                    </div>

                    <div id="venueBonus" class="hidden flex items-center justify-between bg-green-50 rounded-lg p-3">
                        <div class="flex items-center gap-2">
                            <span>📍</span>
                            <span class="font-medium text-gray-700">Bonus PDV</span>
                            <span class="text-xs text-green-600" id="venueName"></span>
                        </div>
                        <span class="font-black text-green-600">+4 pts 🎉</span>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-3">
                        <p class="text-xs text-yellow-800">
                            <strong>Bonus possibles :</strong> +3 pts si bon vainqueur • +3 pts si score exact
                        </p>
                    </div>
                </div>

                <!-- Total points actuels -->
                <div class="bg-gradient-to-r from-soboa-blue to-blue-600 rounded-xl p-4 text-white text-center">
                    <p class="text-sm opacity-80 mb-1">Vos points totaux</p>
                    <p class="text-4xl font-black" id="modalTotalPoints">{{ session('user_points', 0) }}</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-6 pt-0">
                <button onclick="closePointsModal()" class="w-full bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-3 px-6 rounded-xl shadow-lg transition transform active:scale-95">
                    Super ! Continuer
                </button>
            </div>
        </div>
    </div>

    <!-- Bulle de Géolocalisation Fixe (en bas à droite) -->
    <div id="geoLocationBubble" class="fixed bottom-6 right-6 z-50" x-data="{ expanded: false }">
        <!-- Bulle fermée -->
        <div x-show="!expanded" 
             @click="expanded = true"
             class="bg-white rounded-full shadow-2xl p-4 cursor-pointer hover:scale-110 transition-transform border-2 border-soboa-blue">
            <div class="relative">
                <svg class="w-8 h-8 text-soboa-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <!-- Badge de notification si PDV proche -->
                <span id="geoNotification" class="hidden absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white animate-pulse"></span>
            </div>
        </div>

        <!-- Bulle étendue -->
        <div x-show="expanded" 
             x-transition
             class="bg-white rounded-2xl shadow-2xl border-2 border-gray-200 overflow-hidden min-w-[300px]">
            <!-- Header -->
            <div class="bg-gradient-to-r from-soboa-blue to-blue-600 px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <h3 class="font-bold text-white text-sm">Géolocalisation</h3>
                </div>
                <button @click="expanded = false" class="text-white hover:text-gray-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-4">
                <!-- État: Détection en cours -->
                <div id="geoDetecting" class="flex items-center gap-3">
                    <div class="animate-spin w-8 h-8 border-3 border-soboa-blue border-t-transparent rounded-full"></div>
                    <div>
                        <p class="font-bold text-gray-800 text-sm">Détection en cours...</p>
                        <p class="text-xs text-gray-500">Recherche de votre position</p>
                    </div>
                </div>

                <!-- État: PDV Trouvé -->
                <div id="geoVenueFound" class="hidden">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-2xl">📍</span>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-green-800 text-sm">PDV Partenaire</p>
                            <p class="text-xs text-green-600" id="geoVenueName">Le Djolof</p>
                            <p class="text-xs text-gray-500" id="geoVenueDistance">À 120m</p>
                        </div>
                    </div>
                    <button id="checkInBtn" 
                            onclick="doCheckIn()"
                            class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Check-in (+4 pts)
                    </button>
                    <p class="text-xs text-center text-gray-500 mt-2">Premier check-in du jour</p>
                </div>

                <!-- État: Déjà Check-in -->
                <div id="geoAlreadyChecked" class="hidden">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-2xl">✅</span>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-blue-800 text-sm">Check-in effectué</p>
                            <p class="text-xs text-blue-600" id="geoCheckedVenueName">Le Djolof</p>
                            <p class="text-xs text-gray-500">+4 points bonus aujourd'hui</p>
                        </div>
                    </div>
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded">
                        <p class="text-xs text-blue-800">
                            Vous avez déjà fait votre check-in aujourd'hui. Revenez demain pour plus de points !
                        </p>
                    </div>
                </div>

                <!-- État: Pas de PDV -->
                <div id="geoNoVenue" class="hidden">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                            <span class="text-2xl">🌍</span>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-800 text-sm">Aucun PDV proche</p>
                            <p class="text-xs text-gray-500">Rendez-vous dans un PDV partenaire pour des bonus !</p>
                        </div>
                    </div>
                    <a href="{{ route('map') }}" 
                       class="w-full bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-3 px-4 rounded-xl shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                        </svg>
                        Voir la carte
                    </a>
                </div>

                <!-- État: Erreur -->
                <div id="geoError" class="hidden">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                            <span class="text-2xl">⚠️</span>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-red-800 text-sm">Géolocalisation désactivée</p>
                            <p class="text-xs text-red-600">Activez-la pour les bonus PDV</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <!-- Header -->
        <div class="relative py-12 px-8 rounded-2xl overflow-hidden mb-8 shadow-2xl">
            <div class="absolute inset-0 z-0">
                <img src="/images/sen.webp" class="w-full h-full object-cover" alt="Background">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-[1px]"></div>
            </div>
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h1 class="text-4xl font-black text-white drop-shadow-2xl">Pronostics</h1>
                    <p class="text-white/80 font-bold uppercase tracking-widest text-xs mt-1 drop-shadow-lg">
                        Pariez sur vos matchs favoris
                    </p>
                </div>
                <div class="bg-white/10 backdrop-blur-md border border-white/20 px-4 py-2 rounded-xl shadow-xl">
                    <span class="text-xs text-white/70 font-black uppercase tracking-wider block">Matchs disponibles</span>
                    <span class="text-soboa-orange font-black drop-shadow-md">{{ $matchesByPhase->flatten()->count() }}</span>
                </div>
            </div>
        </div>

        <!-- Info Bonus PDV -->
        <div id="nearbyVenueInfo" class="hidden bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-4 text-white shadow-lg">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center animate-pulse">
                    <span class="text-2xl">📍</span>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-white/80">Vous êtes proche de</p>
                    <p class="font-black text-lg" id="nearbyVenueName">PDV Détecté</p>
                    <p class="text-xs text-white/70 mt-1">
                        🎉 <strong>+4 points bonus</strong> automatiques sur vos pronostics !
                    </p>
                </div>
            </div>
        </div>

        <!-- Info système -->
        <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4">
            <div class="flex items-center gap-3">
                <span class="text-2xl">ℹ️</span>
                <div>
                    <p class="font-bold text-blue-800">Comment ça marche ?</p>
                    <p class="text-sm text-blue-700 mt-1">
                        Faites vos pronostics partout ! Si vous êtes dans un PDV partenaire, vous recevez automatiquement <strong>+4 points bonus</strong> par jour.
                    </p>
                </div>
            </div>
        </div>

        <!-- Onglets des phases -->
        @if($matchesByPhase->count() > 0)
        <div class="bg-white rounded-xl shadow-lg overflow-hidden" x-data="{ activePhase: '{{ $matchesByPhase->keys()->first() }}' }">
            <!-- Tabs navigation -->
            <div class="border-b border-gray-200 overflow-x-auto scrollbar-hide relative">
                <!-- Gradient indicators for scroll on mobile -->
                <div class="absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-white to-transparent pointer-events-none z-10 lg:hidden"></div>

                <nav class="flex flex-nowrap gap-1 px-1 py-1">
                    @foreach($phaseOrder as $phaseKey => $phaseName)
                        @if(isset($matchesByPhase[$phaseKey]) && $matchesByPhase[$phaseKey]->count() > 0)
                            <button
                                @click="activePhase = '{{ $phaseKey }}'"
                                :class="activePhase === '{{ $phaseKey }}' ? 'border-soboa-blue text-soboa-blue bg-blue-50 font-black shadow-sm' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50'"
                                class="whitespace-nowrap py-3 px-3 sm:px-6 border-b-3 font-bold text-sm sm:text-base transition-all flex-shrink-0 rounded-t-lg min-w-fit touch-manipulation active:scale-95"
                                id="tab-{{ $phaseKey }}">
                                <span class="hidden sm:inline">{{ $phaseName }}</span>
                                <span class="sm:hidden">{{ str_replace(['Phase de Poules', '1/8e de Finale', 'Quarts de Finale', 'Demi-Finales', 'Match pour la 3e Place', 'Finale'], ['Poules', '1/8', 'Quarts', 'Demis', '3e Place', 'Finale'], $phaseName) }}</span>
                                <span class="ml-1.5 sm:ml-2 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full text-xs font-bold" :class="activePhase === '{{ $phaseKey }}' ? 'bg-soboa-blue text-white' : 'bg-gray-200 text-gray-700'">
                                    {{ $matchesByPhase[$phaseKey]->count() }}
                                </span>
                            </button>
                        @endif
                    @endforeach
                </nav>
            </div>

            <!-- Tabs content -->
            <div class="p-6">
                @foreach($phaseOrder as $phaseKey => $phaseName)
                    @if(isset($matchesByPhase[$phaseKey]) && $matchesByPhase[$phaseKey]->count() > 0)
                        <div x-show="activePhase === '{{ $phaseKey }}'">
                            @if($phaseKey === 'group_stage' && $groupStageByGroup->count() > 0)
                                <!-- Sous-onglets pour les groupes -->
                                <div class="mb-6" x-data="{ activeGroup: '{{ $groupStageByGroup->keys()->first() }}' }">
                                    <!-- Navigation groupes -->
                                    <div class="flex flex-wrap gap-2 mb-6 justify-center sm:justify-start">
                                        @foreach($groupStageByGroup as $groupName => $groupMatches)
                                            <button
                                                @click="activeGroup = '{{ $groupName }}'"
                                                :class="activeGroup === '{{ $groupName }}' ? 'bg-soboa-blue text-white shadow-lg scale-105' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                                class="px-4 sm:px-5 py-2.5 sm:py-3 rounded-lg font-bold text-sm sm:text-base transition-all touch-manipulation active:scale-95 min-w-[80px] sm:min-w-[100px]">
                                                {{ $groupName }}
                                                <span class="ml-1 opacity-75 text-xs">({{ $groupMatches->count() }})</span>
                                            </button>
                                        @endforeach
                                    </div>

                                    <!-- Contenu des groupes -->
                                    @foreach($groupStageByGroup as $groupName => $groupMatches)
                                        <div x-show="activeGroup === '{{ $groupName }}'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                            @foreach($groupMatches as $match)
            <div id="match-{{ $match->id }}"
                class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all border-2 border-gray-100
                    {{ isset($favoriteTeamId) && ($match->home_team_id == $favoriteTeamId || $match->away_team_id == $favoriteTeamId) ? 'ring-2 ring-soboa-orange ring-offset-2' : '' }}">

                <!-- Header du match -->
                <div class="bg-gradient-to-r from-soboa-blue to-blue-600 px-6 py-3 text-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">⚽</span>
                            <div>
                                <p class="text-sm font-medium text-white">
                                    {{ \Carbon\Carbon::parse($match->match_date)->translatedFormat('l d F Y à H:i') }}
                                </p>
                            </div>
                        </div>
                        @if($match->status === 'finished')
                            <span class="bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                                Terminé
                            </span>
                        @elseif($match->status === 'live')
                            <span class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full animate-pulse">
                                🔴 En cours
                            </span>
                        @else
                            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">
                                À venir
                            </span>
                        @endif
                    </div>
                    @if($match->stadium)
                        <div class="px-6 pt-2 pb-1">
                            <p class="text-xs text-white/80 flex items-center gap-1">
                                <span>🏟️</span>
                                <span>{{ $match->stadium }}</span>
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Corps du match -->
                <div class="p-6">
                    <!-- Équipes -->
                    <div class="flex items-center justify-between mb-6">
                        <!-- Équipe domicile -->
                        <div class="flex-1 text-center">
                            @if($match->homeTeam && $match->homeTeam->iso_code)
                                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-2">
                                    <img src="https://flagicons.lipis.dev/flags/4x3/{{ strtolower($match->homeTeam->iso_code) }}.svg"
                                         alt="{{ $match->homeTeam->name }}"
                                         class="w-12 h-12 object-contain rounded"
                                         onerror="this.style.display='none'; this.parentElement.classList.add('bg-soboa-blue'); this.parentElement.innerHTML='<span class=\'text-3xl\'>\uD83C\uDFC1</span>';">
                                </div>
                            @else
                                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full mb-2 shadow-md">
                                    <span class="text-3xl">🏁</span>
                                </div>
                            @endif
                            <h3 class="font-black text-lg text-gray-800">
                                {{ $match->homeTeam ? $match->homeTeam->name : $match->team_a }}
                            </h3>
                        </div>

                        <!-- Score / VS -->
                        <div class="px-6">
                            @if($match->status === 'finished' && $match->score_home !== null && $match->score_away !== null)
                                <div class="text-center">
                                    <div class="flex items-center gap-3 text-3xl font-black text-soboa-blue">
                                        <span>{{ $match->score_home }}</span>
                                        <span class="text-gray-400">-</span>
                                        <span>{{ $match->score_away }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Score final</p>
                                </div>
                            @else
                                <div class="text-center">
                                    <span class="text-2xl font-black text-gray-400">VS</span>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ \Carbon\Carbon::parse($match->match_date)->format('H:i') }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Équipe extérieure -->
                        <div class="flex-1 text-center">
                            @if($match->awayTeam && $match->awayTeam->iso_code)
                                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-2">
                                    <img src="https://flagicons.lipis.dev/flags/4x3/{{ strtolower($match->awayTeam->iso_code) }}.svg"
                                         alt="{{ $match->awayTeam->name }}"
                                         class="w-12 h-12 object-contain rounded"
                                         onerror="this.style.display='none'; this.parentElement.classList.add('bg-soboa-blue'); this.parentElement.innerHTML='<span class=\'text-3xl\'>\uD83C\uDFC1</span>';">
                                </div>
                            @else
                                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full mb-2 shadow-md">
                                    <span class="text-3xl">🏁</span>
                                </div>
                            @endif
                            <h3 class="font-black text-lg text-gray-800">
                                {{ $match->awayTeam ? $match->awayTeam->name : $match->team_b }}
                            </h3>
                        </div>
                    </div>

                    <!-- Points de vente où le match sera diffusé -->
                    @if($match->animations && $match->animations->count() > 0)
                        <div class="mb-6 pb-6 border-b">
                            <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                <span>📍</span>
                                <span>Diffusé dans {{ $match->animations->count() }} PDV</span>
                            </h4>
                            <!-- Scroll horizontal container -->
                            <div class="overflow-x-auto scrollbar-hide -mx-6 px-6">
                                <div class="flex gap-2 pb-2" style="min-width: min-content;">
                                @foreach($match->animations as $animation)
                                    @php
                                        $bar = $animation->bar;
                                        $typeColors = [
                                            'dakar' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'border' => 'border-blue-300', 'icon' => '🏙️'],
                                            'regions' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-300', 'icon' => '🗺️'],
                                            'chr' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'border' => 'border-orange-300', 'icon' => '🍽️'],
                                            'fanzone' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'border' => 'border-purple-300', 'icon' => '🎉'],
                                        ];
                                        $colors = $typeColors[$bar->type_pdv ?? 'dakar'] ?? $typeColors['dakar'];
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border {{ $colors['bg'] }} {{ $colors['text'] }} {{ $colors['border'] }} whitespace-nowrap flex-shrink-0">
                                        <span>{{ $colors['icon'] }}</span>
                                        <span>{{ $bar->name }}</span>
                                        @if($bar->zone)
                                            <span class="opacity-75">• {{ $bar->zone }}</span>
                                        @endif
                                    </span>
                                @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Formulaire de pronostic -->
                    @if($match->status !== 'finished')
                        @php
                            $userPrediction = $userPredictions[$match->id] ?? null;
                            $isPredictionLocked = \Carbon\Carbon::parse($match->match_date)->subMinutes(15)->isPast();
                        @endphp

                        @if(session('user_id'))
                            <div class="border-t pt-6">
                                @if($userPrediction)
                                    <!-- Pronostic existant -->
                                    <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center gap-2">
                                                <span class="text-2xl">✅</span>
                                                <div>
                                                    <p class="font-bold text-green-800">Votre pronostic</p>
                                                    <p class="text-xs text-green-600">Enregistré le
                                                        {{ $userPrediction->created_at->format('d/m/Y à H:i') }}</p>
                                                </div>
                                            </div>
                                            @if($userPrediction->points_earned > 0)
                                                <span class="bg-green-600 text-white font-bold px-3 py-1 rounded-full text-sm">
                                                    +{{ $userPrediction->points_earned }} pts
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center justify-center gap-4 text-lg font-black text-green-800">
                                            <span>{{ $userPrediction->score_a }}</span>
                                            <span class="text-green-600">-</span>
                                            <span>{{ $userPrediction->score_b }}</span>
                                        </div>
                                        @if(!$isPredictionLocked)
                                            <button onclick="enableEdit({{ $match->id }})"
                                                class="mt-3 w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition">
                                                Modifier mon pronostic
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <!-- Formulaire de pronostic -->
                                    @if($isPredictionLocked)
                                        <div class="bg-gray-100 border border-gray-300 rounded-xl p-4 text-center">
                                            <span class="text-2xl">🔒</span>
                                            <p class="text-gray-600 font-medium mt-2">Les pronostics sont fermés pour ce match</p>
                                        </div>
                                    @else
                                        <form action="{{ route('predictions.store') }}" method="POST" class="space-y-4 prediction-form" data-match-id="{{ $match->id }}">
                                            @csrf
                                            <input type="hidden" name="match_id" value="{{ $match->id }}">
                                            <input type="hidden" name="venue_id" id="venue_id_{{ $match->id }}" value="">
                                            <input type="hidden" name="match_info" value="{{ ($match->homeTeam ? $match->homeTeam->name : $match->team_a) }} vs {{ ($match->awayTeam ? $match->awayTeam->name : $match->team_b) }}">

                                            @php
                                                $isKnockoutPhase = in_array($match->phase, ['round_of_16', 'quarter_final', 'semi_final', 'third_place', 'final']);
                                            @endphp

                                            <div class="flex items-center justify-center gap-4">
                                                <div class="text-center flex-1">
                                                    <label class="block text-sm font-bold text-gray-700 mb-2">
                                                        Score {{ $match->homeTeam ? $match->homeTeam->name : $match->team_a }}
                                                    </label>
                                                    <input type="number" name="score_a" min="0" max="20" required
                                                        class="w-full text-center text-2xl font-black border-2 border-gray-300 rounded-xl p-3 focus:border-soboa-orange focus:ring-0"
                                                        x-data="{}" x-on:change="checkDraw()">
                                                </div>
                                                <span class="text-2xl font-black text-gray-400 mt-6">-</span>
                                                <div class="text-center flex-1">
                                                    <label class="block text-sm font-bold text-gray-700 mb-2">
                                                        Score {{ $match->awayTeam ? $match->awayTeam->name : $match->team_b }}
                                                    </label>
                                                    <input type="number" name="score_b" min="0" max="20" required
                                                        class="w-full text-center text-2xl font-black border-2 border-gray-300 rounded-xl p-3 focus:border-soboa-orange focus:ring-0"
                                                        x-data="{}" x-on:change="checkDraw()">
                                                </div>
                                            </div>

                                            @if($isKnockoutPhase)
                                            <!-- Option égalité + tirs au but pour phases à élimination -->
                                            <div class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-4" id="penalty-section-{{ $match->id }}" style="display: none;">
                                                <div class="flex items-center gap-2 mb-3">
                                                    <span class="text-2xl">⚠️</span>
                                                    <p class="font-bold text-yellow-800">Égalité détectée - Phase à élimination</p>
                                                </div>
                                                <p class="text-sm text-yellow-700 mb-3">
                                                    En phase à élimination, il ne peut pas y avoir de match nul. Qui gagnera aux tirs au but ?
                                                </p>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <label class="cursor-pointer">
                                                        <input type="radio" name="penalty_winner" value="home" class="hidden peer" required>
                                                        <div class="border-2 border-gray-300 peer-checked:border-soboa-blue peer-checked:bg-blue-50 rounded-lg p-3 text-center transition hover:border-gray-400">
                                                            <p class="font-bold text-gray-800 peer-checked:text-soboa-blue">🏆 {{ $match->homeTeam ? $match->homeTeam->name : $match->team_a }}</p>
                                                            <p class="text-xs text-gray-600 mt-1">Vainqueur aux TAB</p>
                                                        </div>
                                                    </label>
                                                    <label class="cursor-pointer">
                                                        <input type="radio" name="penalty_winner" value="away" class="hidden peer" required>
                                                        <div class="border-2 border-gray-300 peer-checked:border-soboa-blue peer-checked:bg-blue-50 rounded-lg p-3 text-center transition hover:border-gray-400">
                                                            <p class="font-bold text-gray-800 peer-checked:text-soboa-blue">🏆 {{ $match->awayTeam ? $match->awayTeam->name : $match->team_b }}</p>
                                                            <p class="text-xs text-gray-600 mt-1">Vainqueur aux TAB</p>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                            <input type="hidden" name="predict_draw" id="predict_draw_{{ $match->id }}" value="0">
                                            @endif

                                            <button type="submit"
                                                class="w-full bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-3 px-6 rounded-xl shadow-lg transition transform active:scale-95">
                                                🎯 Valider mon pronostic
                                            </button>

                                            <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded">
                                                <p class="text-xs text-blue-800">
                                                    <strong>Points:</strong> +1 pt participation • +3 pts bon vainqueur • +3 pts score exact • 
                                                    <strong id="bonus-info-{{ $match->id }}">+4 pts bonus PDV si détecté</strong>
                                                </p>
                                            </div>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        @else
                            <div class="border-t pt-6">
                                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
                                    <span class="text-2xl">🔐</span>
                                    <p class="text-yellow-800 font-medium mt-2">Connectez-vous pour faire votre pronostic</p>
                                    <a href="/login"
                                        class="mt-3 inline-block bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-2 px-6 rounded-lg transition">
                                        Se connecter
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    @foreach($matchesByPhase[$phaseKey] as $match)
                                        <div id="match-{{ $match->id }}"
                                            class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all border-2 border-gray-100
                                                {{ isset($favoriteTeamId) && ($match->home_team_id == $favoriteTeamId || $match->away_team_id == $favoriteTeamId) ? 'ring-2 ring-soboa-orange ring-offset-2' : '' }}">

                                            <!-- Header du match -->
                                            <div class="bg-gradient-to-r from-soboa-blue to-blue-600 px-6 py-3 text-white">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-3">
                                                        <span class="text-xl">⚽</span>
                                                        <div>
                                                            <p class="text-sm font-medium text-white">
                                                                {{ \Carbon\Carbon::parse($match->match_date)->translatedFormat('l d F Y à H:i') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    @if($match->status === 'finished')
                                                        <span class="bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                                                            Terminé
                                                        </span>
                                                    @elseif($match->status === 'live')
                                                        <span class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full animate-pulse">
                                                            🔴 En cours
                                                        </span>
                                                    @else
                                                        <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">
                                                            À venir
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($match->stadium)
                                                    <div class="px-6 pt-2 pb-1">
                                                        <p class="text-xs text-white/80 flex items-center gap-1">
                                                            <span>🏟️</span>
                                                            <span>{{ $match->stadium }}</span>
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Corps du match -->
                                            <div class="p-6">
                                                <!-- Équipes -->
                                                <div class="flex items-center justify-between mb-6">
                                                    <!-- Équipe domicile -->
                                                    <div class="flex-1 text-center">
                                                        @if($match->homeTeam && $match->homeTeam->iso_code)
                                                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-2">
                                                                <img src="https://flagicons.lipis.dev/flags/4x3/{{ strtolower($match->homeTeam->iso_code) }}.svg"
                                                                     alt="{{ $match->homeTeam->name }}"
                                                                     class="w-12 h-12 object-contain rounded"
                                                                     onerror="this.style.display='none'; this.parentElement.classList.add('bg-soboa-blue'); this.parentElement.innerHTML='<span class=\'text-3xl\'>\uD83C\uDFC1</span>';">
                                                            </div>
                                                        @else
                                                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full mb-2 shadow-md">
                                                                <span class="text-3xl">🏁</span>
                                                            </div>
                                                        @endif
                                                        <h3 class="font-black text-lg text-gray-800">
                                                            {{ $match->homeTeam ? $match->homeTeam->name : $match->team_a }}
                                                        </h3>
                                                    </div>

                                                    <!-- Score / VS -->
                                                    <div class="px-6">
                                                        @if($match->status === 'finished' && $match->score_home !== null && $match->score_away !== null)
                                                            <div class="text-center">
                                                                <div class="flex items-center gap-3 text-3xl font-black text-soboa-blue">
                                                                    <span>{{ $match->score_home }}</span>
                                                                    <span class="text-gray-400">-</span>
                                                                    <span>{{ $match->score_away }}</span>
                                                                </div>
                                                                <p class="text-xs text-gray-500 mt-1">Score final</p>
                                                            </div>
                                                        @else
                                                            <div class="text-center">
                                                                <span class="text-2xl font-black text-gray-400">VS</span>
                                                                <p class="text-xs text-gray-500 mt-1">
                                                                    {{ \Carbon\Carbon::parse($match->match_date)->format('H:i') }}</p>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Équipe extérieure -->
                                                    <div class="flex-1 text-center">
                                                        @if($match->awayTeam && $match->awayTeam->iso_code)
                                                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-2">
                                                                <img src="https://flagicons.lipis.dev/flags/4x3/{{ strtolower($match->awayTeam->iso_code) }}.svg"
                                                                     alt="{{ $match->awayTeam->name }}"
                                                                     class="w-12 h-12 object-contain rounded"
                                                                     onerror="this.style.display='none'; this.parentElement.classList.add('bg-soboa-blue'); this.parentElement.innerHTML='<span class=\'text-3xl\'>\uD83C\uDFC1</span>';">
                                                            </div>
                                                        @else
                                                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full mb-2 shadow-md">
                                                                <span class="text-3xl">🏁</span>
                                                            </div>
                                                        @endif
                                                        <h3 class="font-black text-lg text-gray-800">
                                                            {{ $match->awayTeam ? $match->awayTeam->name : $match->team_b }}
                                                        </h3>
                                                    </div>
                                                </div>

                                                <!-- Points de vente où le match sera diffusé -->
                                                @if($match->animations && $match->animations->count() > 0)
                                                    <div class="mb-6 pb-6 border-b">
                                                        <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                                            <span>📍</span>
                                                            <span>Diffusé dans {{ $match->animations->count() }} PDV</span>
                                                        </h4>
                                                        <!-- Scroll horizontal container -->
                                                        <div class="overflow-x-auto scrollbar-hide -mx-6 px-6">
                                                            <div class="flex gap-2 pb-2" style="min-width: min-content;">
                                                            @foreach($match->animations as $animation)
                                                                @php
                                                                    $bar = $animation->bar;
                                                                    $typeColors = [
                                                                        'dakar' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'border' => 'border-blue-300', 'icon' => '🏙️'],
                                                                        'regions' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-300', 'icon' => '🗺️'],
                                                                        'chr' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'border' => 'border-orange-300', 'icon' => '🍽️'],
                                                                        'fanzone' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'border' => 'border-purple-300', 'icon' => '🎉'],
                                                                    ];
                                                                    $colors = $typeColors[$bar->type_pdv ?? 'dakar'] ?? $typeColors['dakar'];
                                                                @endphp
                                                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border {{ $colors['bg'] }} {{ $colors['text'] }} {{ $colors['border'] }} whitespace-nowrap flex-shrink-0">
                                                                    <span>{{ $colors['icon'] }}</span>
                                                                    <span>{{ $bar->name }}</span>
                                                                    @if($bar->zone)
                                                                        <span class="opacity-75">• {{ $bar->zone }}</span>
                                                                    @endif
                                                                </span>
                                                            @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Formulaire de pronostic -->
                                                @if($match->status !== 'finished')
                                                    @php
                                                        $userPrediction = $userPredictions[$match->id] ?? null;
                                                        $isPredictionLocked = \Carbon\Carbon::parse($match->match_date)->subMinutes(15)->isPast();
                                                    @endphp

                                                    @if(session('user_id'))
                                                        <div class="border-t pt-6">
                                                            @if($userPrediction)
                                                                <!-- Pronostic existant -->
                                                                <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                                                                    <div class="flex items-center justify-between mb-3">
                                                                        <div class="flex items-center gap-2">
                                                                            <span class="text-2xl">✅</span>
                                                                            <div>
                                                                                <p class="font-bold text-green-800">Votre pronostic</p>
                                                                                <p class="text-xs text-green-600">Enregistré le
                                                                                    {{ $userPrediction->created_at->format('d/m/Y à H:i') }}</p>
                                                                            </div>
                                                                        </div>
                                                                        @if($userPrediction->points_earned > 0)
                                                                            <span class="bg-green-600 text-white font-bold px-3 py-1 rounded-full text-sm">
                                                                                +{{ $userPrediction->points_earned }} pts
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                    <div class="flex items-center justify-center gap-4 text-lg font-black text-green-800">
                                                                        <span>{{ $userPrediction->score_a }}</span>
                                                                        <span class="text-green-600">-</span>
                                                                        <span>{{ $userPrediction->score_b }}</span>
                                                                    </div>
                                                                    @if(!$isPredictionLocked)
                                                                        <button onclick="enableEdit({{ $match->id }})"
                                                                            class="mt-3 w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition">
                                                                            Modifier mon pronostic
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <!-- Formulaire de pronostic -->
                                                                @if($isPredictionLocked)
                                                                    <div class="bg-gray-100 border border-gray-300 rounded-xl p-4 text-center">
                                                                        <span class="text-2xl">🔒</span>
                                                                        <p class="text-gray-600 font-medium mt-2">Les pronostics sont fermés pour ce match</p>
                                                                    </div>
                                                                @else
                                                                    <form action="{{ route('predictions.store') }}" method="POST" class="space-y-4 prediction-form" data-match-id="{{ $match->id }}">
                                                                        @csrf
                                                                        <input type="hidden" name="match_id" value="{{ $match->id }}">
                                                                        <input type="hidden" name="venue_id" id="venue_id_{{ $match->id }}" value="">
                                                                        <input type="hidden" name="match_info" value="{{ ($match->homeTeam ? $match->homeTeam->name : $match->team_a) }} vs {{ ($match->awayTeam ? $match->awayTeam->name : $match->team_b) }}">

                                                                        @php
                                                                            $isKnockoutPhase = in_array($match->phase, ['round_of_16', 'quarter_final', 'semi_final', 'final']);
                                                                        @endphp

                                                                        <div class="flex items-center justify-center gap-4">
                                                                            <div class="text-center flex-1">
                                                                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                                                                    Score {{ $match->homeTeam ? $match->homeTeam->name : $match->team_a }}
                                                                                </label>
                                                                                <input type="number" name="score_a" min="0" max="20" required
                                                                                    class="w-full text-center text-2xl font-black border-2 border-gray-300 rounded-xl p-3 focus:border-soboa-orange focus:ring-0"
                                                                                    x-data="{}" x-on:change="checkDraw()">
                                                                            </div>
                                                                            <span class="text-2xl font-black text-gray-400 mt-6">-</span>
                                                                            <div class="text-center flex-1">
                                                                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                                                                    Score {{ $match->awayTeam ? $match->awayTeam->name : $match->team_b }}
                                                                                </label>
                                                                                <input type="number" name="score_b" min="0" max="20" required
                                                                                    class="w-full text-center text-2xl font-black border-2 border-gray-300 rounded-xl p-3 focus:border-soboa-orange focus:ring-0"
                                                                                    x-data="{}" x-on:change="checkDraw()">
                                                                            </div>
                                                                        </div>

                                                                        @if($isKnockoutPhase)
                                                                        <!-- Option égalité + tirs au but pour phases à élimination -->
                                                                        <div class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-4" id="penalty-section-{{ $match->id }}" style="display: none;">
                                                                            <div class="flex items-center gap-2 mb-3">
                                                                                <span class="text-2xl">⚠️</span>
                                                                                <p class="font-bold text-yellow-800">Égalité détectée - Phase à élimination</p>
                                                                            </div>
                                                                            <p class="text-sm text-yellow-700 mb-3">
                                                                                En phase à élimination, il ne peut pas y avoir de match nul. Qui gagnera aux tirs au but ?
                                                                            </p>
                                                                            <div class="grid grid-cols-2 gap-3">
                                                                                <label class="cursor-pointer">
                                                                                    <input type="radio" name="penalty_winner" value="home" class="hidden peer" required>
                                                                                    <div class="border-2 border-gray-300 peer-checked:border-soboa-blue peer-checked:bg-blue-50 rounded-lg p-3 text-center transition hover:border-gray-400">
                                                                                        <p class="font-bold text-gray-800 peer-checked:text-soboa-blue">🏆 {{ $match->homeTeam ? $match->homeTeam->name : $match->team_a }}</p>
                                                                                        <p class="text-xs text-gray-600 mt-1">Vainqueur aux TAB</p>
                                                                                    </div>
                                                                                </label>
                                                                                <label class="cursor-pointer">
                                                                                    <input type="radio" name="penalty_winner" value="away" class="hidden peer" required>
                                                                                    <div class="border-2 border-gray-300 peer-checked:border-soboa-blue peer-checked:bg-blue-50 rounded-lg p-3 text-center transition hover:border-gray-400">
                                                                                        <p class="font-bold text-gray-800 peer-checked:text-soboa-blue">🏆 {{ $match->awayTeam ? $match->awayTeam->name : $match->team_b }}</p>
                                                                                        <p class="text-xs text-gray-600 mt-1">Vainqueur aux TAB</p>
                                                                                    </div>
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                        <input type="hidden" name="predict_draw" id="predict_draw_{{ $match->id }}" value="0">
                                                                        @endif

                                                                        <button type="submit"
                                                                            class="w-full bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-3 px-6 rounded-xl shadow-lg transition transform active:scale-95">
                                                                            🎯 Valider mon pronostic
                                                                        </button>

                                                                        <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded">
                                                                            <p class="text-xs text-blue-800">
                                                                                <strong>Points:</strong> +1 pt participation • +3 pts bon vainqueur • +3 pts score exact • 
                                                                                <strong id="bonus-info-{{ $match->id }}">+4 pts bonus PDV si détecté</strong>
                                                                            </p>
                                                                        </div>
                                                                    </form>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="border-t pt-6">
                                                            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
                                                                <span class="text-2xl">🔐</span>
                                                                <p class="text-yellow-800 font-medium mt-2">Connectez-vous pour faire votre pronostic</p>
                                                                <a href="/login"
                                                                    class="mt-3 inline-block bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-2 px-6 rounded-lg transition">
                                                                    Se connecter
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        @else
        <!-- Message si aucun match -->
            <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                <span class="text-6xl">📅</span>
                <h3 class="text-2xl font-black text-gray-800 mt-4">Aucun match disponible</h3>
                <p class="text-gray-600 mt-2">Revenez bientôt pour de nouveaux matchs !</p>
            </div>
        @endif
    </div>

    <script>
        // Variables globales
        let userLatitude = null;
        let userLongitude = null;
        let nearbyVenue = null;
        const activeVenues = @json($activeVenues);

        // Fonction pour détecter l'égalité et afficher les tirs au but
        function checkDraw() {
            document.querySelectorAll('.prediction-form').forEach(form => {
                const matchId = form.dataset.matchId;
                const scoreA = form.querySelector('input[name="score_a"]');
                const scoreB = form.querySelector('input[name="score_b"]');
                const penaltySection = document.getElementById('penalty-section-' + matchId);
                const predictDrawInput = document.getElementById('predict_draw_' + matchId);
                const penaltyInputs = form.querySelectorAll('input[name="penalty_winner"]');

                if (scoreA && scoreB && penaltySection) {
                    const a = parseInt(scoreA.value);
                    const b = parseInt(scoreB.value);

                    if (!isNaN(a) && !isNaN(b) && a === b) {
                        // Égalité détectée
                        penaltySection.style.display = 'block';
                        predictDrawInput.value = '1';
                        
                        // Rendre les radios required
                        penaltyInputs.forEach(input => {
                            input.setAttribute('required', 'required');
                        });
                    } else {
                        // Pas d'égalité
                        penaltySection.style.display = 'none';
                        predictDrawInput.value = '0';
                        
                        // Retirer required et décocher
                        penaltyInputs.forEach(input => {
                            input.removeAttribute('required');
                            input.checked = false;
                        });
                    }
                }
            });
        }

        // Attacher checkDraw aux changements de score
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('input[name="score_a"], input[name="score_b"]').forEach(input => {
                input.addEventListener('input', checkDraw);
                input.addEventListener('change', checkDraw);
            });
        });

        // Fonction pour calculer la distance Haversine
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Rayon de la Terre en km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        // Détecter automatiquement la géolocalisation
        function detectGeolocation() {
            if (!navigator.geolocation) {
                console.log('[GAZELLE] Géolocalisation non supportée');
                showGeoState('error');
                return;
            }

            showGeoState('detecting');

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    userLatitude = position.coords.latitude;
                    userLongitude = position.coords.longitude;

                    console.log('[GAZELLE] Position détectée:', userLatitude, userLongitude);

                    // Trouver le PDV le plus proche
                    let closestVenue = null;
                    let minDistance = Infinity;

                    activeVenues.forEach(venue => {
                        const distance = calculateDistance(
                            userLatitude,
                            userLongitude,
                            parseFloat(venue.latitude),
                            parseFloat(venue.longitude)
                        );

                        if (distance < minDistance) {
                            minDistance = distance;
                            closestVenue = venue;
                        }
                    });

                    // Si PDV dans un rayon de 200m (0.2 km)
                    if (closestVenue && minDistance <= 0.2) {
                        nearbyVenue = closestVenue;
                        console.log('[GAZELLE] PDV détecté:', nearbyVenue.name, '(', Math.round(minDistance * 1000), 'm)');
                        
                        // Mettre à jour la bulle avec PDV trouvé
                        document.getElementById('geoVenueName').textContent = nearbyVenue.name;
                        document.getElementById('geoVenueDistance').textContent = 'À ' + Math.round(minDistance * 1000) + 'm';
                        
                        // Vérifier si déjà check-in aujourd'hui
                        checkIfAlreadyCheckedIn(nearbyVenue.id);

                        // Afficher le bandeau de bonus
                        const nearbyInfo = document.getElementById('nearbyVenueInfo');
                        const nearbyName = document.getElementById('nearbyVenueName');
                        nearbyName.textContent = nearbyVenue.name;
                        nearbyInfo.classList.remove('hidden');

                        // Remplir tous les champs venue_id
                        document.querySelectorAll('input[name="venue_id"]').forEach(input => {
                            input.value = nearbyVenue.id;
                        });

                        // Mettre à jour les infos bonus
                        document.querySelectorAll('[id^="bonus-info-"]').forEach(el => {
                            el.innerHTML = '<strong class="text-green-600">+4 pts bonus PDV garantis ! 🎉</strong>';
                        });

                        // Afficher notification badge
                        document.getElementById('geoNotification').classList.remove('hidden');
                    } else {
                        console.log('[GAZELLE] Pas de PDV à proximité (distance:', Math.round(minDistance * 1000), 'm)');
                        showGeoState('noVenue');
                    }
                },
                (error) => {
                    console.log('[GAZELLE] Erreur géolocalisation:', error.message);
                    showGeoState('error');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 30000
                }
            );
        }

        // Afficher l'état de la bulle géo
        function showGeoState(state) {
            // Sauvegarder l'état actuel pour le bfcache
            window.currentGeoState = state;
            
            // Cacher tous les états
            document.getElementById('geoDetecting').classList.add('hidden');
            document.getElementById('geoVenueFound').classList.add('hidden');
            document.getElementById('geoAlreadyChecked').classList.add('hidden');
            document.getElementById('geoNoVenue').classList.add('hidden');
            document.getElementById('geoError').classList.add('hidden');

            // Afficher l'état demandé
            if (state === 'detecting') {
                document.getElementById('geoDetecting').classList.remove('hidden');
            } else if (state === 'venueFound') {
                document.getElementById('geoVenueFound').classList.remove('hidden');
            } else if (state === 'alreadyChecked') {
                document.getElementById('geoAlreadyChecked').classList.remove('hidden');
            } else if (state === 'noVenue') {
                document.getElementById('geoNoVenue').classList.remove('hidden');
            } else if (state === 'error') {
                document.getElementById('geoError').classList.remove('hidden');
            }
        }
        
        // Exposer showGeoState globalement pour le bfcache
        window.showGeoState = showGeoState;

        // Vérifier si déjà check-in aujourd'hui
        async function checkIfAlreadyCheckedIn(venueId) {
            try {
                const response = await fetch('/api/check-in/status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ venue_id: venueId })
                });

                const data = await response.json();

                if (data.already_checked_in) {
                    document.getElementById('geoCheckedVenueName').textContent = nearbyVenue.name;
                    showGeoState('alreadyChecked');
                } else {
                    showGeoState('venueFound');
                }
            } catch (error) {
                console.log('[GAZELLE] Erreur vérification check-in:', error);
                showGeoState('venueFound'); // Par défaut, afficher le bouton
            }
        }

        // Faire un check-in
        async function doCheckIn() {
            if (!nearbyVenue) {
                alert('Aucun PDV détecté');
                return;
            }

            const btn = document.getElementById('checkInBtn');
            btn.disabled = true;
            btn.innerHTML = '<div class="animate-spin w-5 h-5 border-2 border-white border-t-transparent rounded-full mx-auto"></div>';

            try {
                const response = await fetch('/api/check-in', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        venue_id: nearbyVenue.id,
                        latitude: userLatitude,
                        longitude: userLongitude
                    })
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('geoCheckedVenueName').textContent = nearbyVenue.name;
                    showGeoState('alreadyChecked');

                    // Mettre à jour les points de l'utilisateur
                    const pointsElements = document.querySelectorAll('[data-user-points]');
                    pointsElements.forEach(el => {
                        el.textContent = data.user_points_total || el.textContent;
                    });

                    // Notification de succès
                    alert('✅ Check-in réussi ! +' + data.points_awarded + ' points bonus');
                } else {
                    alert('❌ ' + (data.message || 'Erreur lors du check-in'));
                    btn.disabled = false;
                    btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Check-in (+4 pts)';
                }
            } catch (error) {
                console.log('[GAZELLE] Erreur check-in:', error);
                alert('❌ Erreur lors du check-in');
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Check-in (+4 pts)';
            }
        }

        // Intercepter les soumissions de formulaire
        document.addEventListener('DOMContentLoaded', () => {
            // Détecter la géolocalisation au chargement
            detectGeolocation();

            // Intercepter tous les formulaires de pronostic
            document.querySelectorAll('.prediction-form').forEach(form => {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const formData = new FormData(form);
                    const matchId = form.dataset.matchId;

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();

                        if (data.message || data.teams) {
                            // Afficher la popup de récap
                            showPointsModal({
                                matchInfo: formData.get('match_info'),
                                scoreA: formData.get('score_a'),
                                scoreB: formData.get('score_b'),
                                venueName: data.venue || null,
                                venueBonus: data.venue_bonus_points || 0,
                                totalPoints: data.user_points_total || {{ session('user_points', 0) }}
                            });

                            // Mettre à jour l'interface SANS recharger la page
                            updatePredictionDisplay(matchId, {
                                scoreA: formData.get('score_a'),
                                scoreB: formData.get('score_b'),
                                predictDraw: formData.get('predict_draw'),
                                penaltyWinner: formData.get('penalty_winner'),
                                createdAt: new Date().toLocaleString('fr-FR')
                            });

                            // Mettre à jour les points de l'utilisateur
                            if (data.user_points_total) {
                                document.querySelectorAll('[data-user-points]').forEach(el => {
                                    el.textContent = data.user_points_total;
                                });
                                // Mettre à jour la session
                                sessionStorage.setItem('user_points', data.user_points_total);
                            }
                        }
                    } catch (error) {
                        console.error('[GAZELLE] Erreur soumission:', error);
                        // Message d'erreur plus user-friendly
                        const errorMsg = error.message || 'Erreur lors de l\'enregistrement';
                        showErrorNotification(errorMsg);
                    }
                });
            });
        });

        // Afficher la popup de récap
        function showPointsModal(data) {
            const modal = document.getElementById('pointsRecapModal');
            const modalContent = document.getElementById('modalContent');
            
            // Remplir les données
            document.getElementById('modalMatchInfo').textContent = data.matchInfo;
            document.getElementById('modalScoreA').textContent = data.scoreA;
            document.getElementById('modalScoreB').textContent = data.scoreB;
            document.getElementById('modalTotalPoints').textContent = data.totalPoints;

            // Afficher ou cacher le bonus venue
            const venueBonus = document.getElementById('venueBonus');
            if (data.venueName && data.venueBonus > 0) {
                document.getElementById('venueName').textContent = '(' + data.venueName + ')';
                venueBonus.classList.remove('hidden');
                venueBonus.classList.add('flex');
            } else {
                venueBonus.classList.add('hidden');
            }

            // Afficher la modale avec animation
            modal.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        // Fermer la popup
        function closePointsModal() {
            const modal = document.getElementById('pointsRecapModal');
            const modalContent = document.getElementById('modalContent');
            
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Mettre à jour l'affichage du pronostic après soumission AJAX
        function updatePredictionDisplay(matchId, predictionData) {
            const matchCard = document.getElementById('match-' + matchId);
            if (!matchCard) return;

            // Trouver le formulaire et le conteneur parent
            const form = matchCard.querySelector('.prediction-form');
            if (!form) return;

            const formContainer = form.parentElement;

            // Créer l'affichage du pronostic enregistré
            const predictionHTML = `
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 prediction-success-animation">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">✅</span>
                            <div>
                                <p class="font-bold text-green-800">Votre pronostic</p>
                                <p class="text-xs text-green-600">Enregistré à l'instant</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center gap-4 text-lg font-black text-green-800">
                        <span>${predictionData.scoreA}</span>
                        <span class="text-green-600">-</span>
                        <span>${predictionData.scoreB}</span>
                    </div>
                    ${predictionData.predictDraw === '1' ? `
                        <div class="mt-2 text-center">
                            <span class="text-xs text-yellow-700 bg-yellow-100 px-2 py-1 rounded">
                                🏆 Tirs au but: ${predictionData.penaltyWinner === 'home' ? 'Équipe domicile' : 'Équipe extérieure'}
                            </span>
                        </div>
                    ` : ''}
                    <button onclick="enableEdit(${matchId})" 
                        class="mt-3 w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition">
                        Modifier mon pronostic
                    </button>
                </div>
            `;

            // Remplacer le formulaire par l'affichage du pronostic
            formContainer.innerHTML = predictionHTML;

            // Animation de succès
            setTimeout(() => {
                const successDiv = formContainer.querySelector('.prediction-success-animation');
                if (successDiv) {
                    successDiv.classList.add('animate-pulse-once');
                }
            }, 100);
        }

        // Afficher une notification d'erreur
        function showErrorNotification(message) {
            // Créer une notification toast
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-2xl z-50 animate-slide-in';
            notification.innerHTML = `
                <div class="flex items-center gap-3">
                    <span class="text-2xl">❌</span>
                    <div>
                        <p class="font-bold">Erreur</p>
                        <p class="text-sm">${message}</p>
                    </div>
                </div>
            `;
            document.body.appendChild(notification);

            // Retirer après 5 secondes
            setTimeout(() => {
                notification.classList.add('animate-slide-out');
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }

        function enableEdit(matchId) {
            // Recharger seulement le match concerné (ou toute la page si nécessaire)
            window.location.reload();
        }
    </script>

    <style>
        /* Animation pour le pronostic enregistré */
        @keyframes pulse-once {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        .animate-pulse-once {
            animation: pulse-once 0.6s ease-in-out;
        }

        /* Animations pour notifications */
        @keyframes slide-in {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slide-out {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }
        .animate-slide-out {
            animation: slide-out 0.3s ease-in;
        }

        /* Hide scrollbar for tabs navigation */
        .scrollbar-hide {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;  /* Chrome, Safari, Opera */
        }

        /* Smooth scroll for tabs */
        .overflow-x-auto {
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        /* Better touch targets for mobile */
        .touch-manipulation {
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }
    </style>
</x-layouts.app>
