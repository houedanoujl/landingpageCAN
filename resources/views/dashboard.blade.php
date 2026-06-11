<x-layouts.app title="Tableau de bord">
    <div class="bg-gray-50 min-h-screen">

        <!-- Header with Points -->
        <div class="relative py-12 px-4 overflow-hidden border-b border-white/10">
            <!-- Background Image with Overlay -->
            <div class="absolute inset-0 z-0">
                <img src="/images/sen.webp" class="w-full h-full object-cover" alt="Background">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-[2px]"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <!-- User Info -->
                    <div class="text-center md:text-left">
                        <p class="text-white/80 text-sm uppercase tracking-widest mb-1 drop-shadow-md font-bold">
                            Bienvenue</p>
                        <h1 class="text-4xl md:text-5xl font-black text-white drop-shadow-2xl">
                            {{ $user->name ?? 'Joueur' }}</h1>
                    </div>

                    <!-- Points Display -->
                    <div
                        class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 text-center min-w-[220px] shadow-2xl transition-transform hover:scale-105">
                        <p class="text-white/80 text-sm uppercase tracking-widest mb-1 drop-shadow-md font-bold">Total
                            points</p>
                        <p class="text-6xl font-black text-soboa-orange drop-shadow-2xl">{{ $user->points_total ?? 0 }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="max-w-7xl mx-auto px-4 -mt-6">
            <div class="grid grid-cols-2 fold:grid-cols-2 md:grid-cols-4 gap-3 fold:gap-4">
                <div class="bg-white rounded-xl p-4 shadow-lg text-center">
                    <span class="text-3xl"></span>
                    <p class="text-2xl font-black text-soboa-blue mt-2">{{ $rank ?? '--' }}</p>
                    <p class="text-gray-500 text-sm">Classement</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-lg text-center">
                    <span class="text-3xl"></span>
                    <p class="text-2xl font-black text-soboa-blue mt-2">{{ $predictionCount ?? 0 }}</p>
                    <p class="text-gray-500 text-sm">Pronostics</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-lg text-center">
                    <span class="text-3xl"></span>
                    <p class="text-2xl font-black text-soboa-blue mt-2">{{ $correctPredictions ?? 0 }}</p>
                    <p class="text-gray-500 text-sm">Bons résultats</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-lg text-center">
                    <span class="text-3xl"></span>
                    <p class="text-2xl font-black text-soboa-blue mt-2">{{ $venueVisits ?? 0 }}</p>
                    <p class="text-gray-500 text-sm">Visites lieux</p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Next Match -->
                <div class="lg:col-span-2">
                    <h2 class="text-xl font-bold text-soboa-blue mb-4 flex items-center gap-2">
                        <span></span> Prochain match
                    </h2>

                    @if($nextMatch)
                        <x-match-card :match="$nextMatch" />
                    @else
                        <div class="bg-white rounded-2xl p-section-md shadow-elev-1 text-center">
                            <div class="w-16 h-16 mx-auto bg-soboa-orange/10 rounded-full flex items-center justify-center mb-3">
                                <i data-lucide="calendar-x" class="w-8 h-8 text-soboa-orange"></i>
                            </div>
                            <p class="text-soboa-text-dark font-bold">Aucun match à venir</p>
                            <p class="text-gray-500 text-sm mt-1">Le calendrier se met à jour bientôt.</p>
                        </div>
                    @endif

                    <a href="/matches"
                        class="mt-4 inline-flex items-center gap-2 text-soboa-orange font-bold hover:underline">
                        Voir tous les matchs
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </a>
                </div>

                <!-- Quick Actions -->
                <div>
                    <h2 class="text-xl font-bold text-soboa-blue mb-4 flex items-center gap-2">
                        <span></span> Actions rapides
                    </h2>

                    <div class="space-y-3">
                        <a href="/matches"
                            class="block bg-soboa-orange text-black rounded-xl p-4 font-bold hover:bg-soboa-orange-dark transition-colors shadow-lg">
                            Faire un pronostic
                        </a>
                        <a href="/map"
                            class="block bg-soboa-blue text-white rounded-xl p-4 font-bold hover:bg-soboa-blue-dark transition-colors shadow-lg">
                            Lieux partenaires (+4 pts)
                        </a>
                        <a href="/leaderboard"
                            class="block bg-white text-soboa-blue rounded-xl p-4 font-bold hover:bg-gray-50 transition-colors shadow-lg border border-gray-200">
                            Voir le classement
                        </a>
                        <a href="/mes-pronostics"
                            class="block bg-white text-soboa-blue rounded-xl p-4 font-bold hover:bg-gray-50 transition-colors shadow-lg border border-gray-200">
                            Historique pronostics
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Predictions -->
        <div class="max-w-7xl mx-auto px-4 pb-8">
            <h2 class="text-xl font-bold text-soboa-blue mb-4 flex items-center gap-2">
                <span></span> Derniers pronostics
            </h2>

            @if($recentPredictions && $recentPredictions->count() > 0)
                <div class="space-y-2.5">
                    @foreach($recentPredictions as $prediction)
                        @php
                            $match = $prediction->match;
                            $matchDate = $match ? \Carbon\Carbon::parse($match->match_date) : null;
                            $matchFinished = $match && $match->status === 'finished';
                            $isLive = $match && $match->status === 'live';
                            $now = now();
                            $relativeDate = $matchDate ? $matchDate->locale('fr')->diffForHumans(['parts' => 1, 'short' => false]) : '—';
                            $pe = $prediction->points_earned;
                        @endphp
                        <article class="bg-white rounded-xl p-4 shadow-elev-1 hover:shadow-elev-2 transition-shadow duration-base">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    {{-- Date row --}}
                                    <div class="flex items-center gap-2 text-[11px] text-gray-500 mb-1.5">
                                        <i data-lucide="calendar" class="w-3 h-3"></i>
                                        <span class="capitalize">{{ $matchDate ? $matchDate->translatedFormat('D d M · H\hi') : '—' }}</span>
                                        @if($isLive)
                                            <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 px-1.5 py-0.5 rounded-full font-bold uppercase tracking-wide">
                                                <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span>Live
                                            </span>
                                        @elseif($matchFinished)
                                            <span class="bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded-full font-bold uppercase tracking-wide">Terminé</span>
                                        @else
                                            <span class="text-soboa-blue font-semibold">· {{ $relativeDate }}</span>
                                        @endif
                                    </div>
                                    {{-- Teams + score row --}}
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-sm font-bold text-soboa-text-dark truncate">{{ $match->team_a ?? 'Équipe A' }}</span>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-soboa-orange/10 text-soboa-orange font-black text-sm tabular-nums">
                                            {{ $prediction->score_a }}<span class="opacity-50">-</span>{{ $prediction->score_b }}
                                        </span>
                                        <span class="text-sm font-bold text-soboa-text-dark truncate">{{ $match->team_b ?? 'Équipe B' }}</span>
                                    </div>
                                    {{-- Final score (if finished) --}}
                                    @if($matchFinished && $match->score_a !== null)
                                        <p class="mt-1 text-[11px] text-gray-500">
                                            Résultat final :
                                            <span class="font-bold text-soboa-text-dark tabular-nums">{{ $match->score_a }} - {{ $match->score_b }}</span>
                                        </p>
                                    @endif
                                </div>
                                {{-- Points badge --}}
                                <div class="flex-shrink-0">
                                    @if($pe === null)
                                        <span class="inline-flex items-center gap-1 bg-soboa-blue/10 text-soboa-blue font-bold px-2.5 py-1 rounded-full text-xs">
                                            <i data-lucide="hourglass" class="w-3 h-3"></i>En attente
                                        </span>
                                    @elseif($pe >= 6)
                                        <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-700 font-bold px-2.5 py-1 rounded-full text-xs">
                                            <i data-lucide="trophy" class="w-3 h-3"></i>+{{ $pe }} pts
                                        </span>
                                    @elseif($pe > 0)
                                        <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-700 font-bold px-2.5 py-1 rounded-full text-xs">
                                            <i data-lucide="check" class="w-3 h-3"></i>+{{ $pe }} pts
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-500 font-bold px-2.5 py-1 rounded-full text-xs">
                                            <i data-lucide="x" class="w-3 h-3"></i>0 pt
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <a href="/mes-pronostics"
                    class="mt-4 inline-flex items-center gap-2 text-soboa-orange font-bold hover:underline focus:outline-none focus:ring-2 focus:ring-soboa-orange focus:ring-offset-2 rounded">
                    Voir tout l'historique
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            @else
                <div class="bg-white rounded-xl p-section-md shadow-elev-1 text-center">
                    <div class="w-16 h-16 mx-auto bg-soboa-orange/10 rounded-full flex items-center justify-center mb-3">
                        <i data-lucide="target" class="w-8 h-8 text-soboa-orange"></i>
                    </div>
                    <p class="text-soboa-text-dark font-bold">Aucun pronostic pour le moment</p>
                    <p class="text-gray-500 text-sm mt-1 mb-4">Démarrez votre course aux points.</p>
                    <a href="/matches" class="btn btn-primary btn-md btn-pill">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        Faire mon premier pronostic
                    </a>
                </div>
            @endif
        </div>

        <!-- Points Details -->
        <div class="max-w-7xl mx-auto px-4 pb-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- Points by Date -->
                <div>
                    <h2 class="text-xl font-bold text-soboa-blue mb-4 flex items-center gap-2">
                        <span></span> Points par date
                    </h2>

                    @if($pointsByDate && $pointsByDate->count() > 0)
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                            <div class="max-h-96 overflow-y-auto">
                                @foreach($pointsByDate as $dayPoints)
                                    <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-bold text-gray-800 capitalize">
                                                    {{ \Carbon\Carbon::parse($dayPoints->date)->translatedFormat('l d F Y') }}
                                                </p>
                                                <p class="text-sm text-gray-500">{{ $dayPoints->count }} action(s)</p>
                                            </div>
                                            <span
                                                class="text-2xl font-black text-soboa-orange">+{{ $dayPoints->total_points }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-xl p-section-md shadow-elev-1 text-center">
                            <div class="w-14 h-14 mx-auto bg-soboa-blue/10 rounded-full flex items-center justify-center mb-3">
                                <i data-lucide="trending-up" class="w-7 h-7 text-soboa-blue"></i>
                            </div>
                            <p class="text-soboa-text-dark font-bold">Aucun point gagné récemment</p>
                            <p class="text-gray-500 text-sm mt-1">Pronostiquez pour gagner des points.</p>
                        </div>
                    @endif
                </div>

                <!-- Points by Venue -->
                <div>
                    <h2 class="text-xl font-bold text-soboa-blue mb-4 flex items-center gap-2">
                        <span></span> Points par lieu
                    </h2>

                    @if($pointsByVenue && $pointsByVenue->count() > 0)
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                            <div class="max-h-96 overflow-y-auto">
                                @foreach($pointsByVenue as $venuePoints)
                                    <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <p class="font-bold text-gray-800">
                                                    {{ $venuePoints->bar->name ?? 'Lieu inconnu' }}</p>
                                                <p class="text-sm text-gray-500">{{ $venuePoints->visit_count }} visite(s)</p>
                                                <p class="text-xs text-gray-400 mt-1">{{ $venuePoints->bar->address ?? '' }}</p>
                                            </div>
                                            <span
                                                class="text-2xl font-black text-soboa-orange ml-4">+{{ $venuePoints->total_points }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-xl p-section-md shadow-elev-1 text-center">
                            <div class="w-14 h-14 mx-auto bg-soboa-orange/10 rounded-full flex items-center justify-center mb-3">
                                <i data-lucide="map-pin" class="w-7 h-7 text-soboa-orange"></i>
                            </div>
                            <p class="text-soboa-text-dark font-bold">Aucun lieu visité</p>
                            <p class="text-gray-500 text-sm mt-1 mb-4">+4 pts bonus à chaque visite PDV partenaire.</p>
                            <a href="/map" class="btn btn-blue btn-md btn-pill">
                                <i data-lucide="map" class="w-4 h-4"></i>
                                Voir la carte
                            </a>
                        </div>
                    @endif
                </div>

            </div>
        </div>

        <!-- Visited Venues Map -->
        @if($visitedVenues && $visitedVenues->count() > 0)
            <div class="max-w-7xl mx-auto px-4 pb-12">
                <h2 class="text-xl font-bold text-soboa-blue mb-4 flex items-center gap-2">
                    <span></span> Carte de mes lieux visités
                </h2>

                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                    <div id="visited-map" class="h-[400px] w-full"></div>
                </div>

                <div class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($visitedVenues as $venue)
                        <div class="bg-white rounded-lg p-3 shadow border border-gray-100">
                            <p class="font-bold text-soboa-blue text-sm">{{ $venue->name }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $venue->address }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Leaflet CSS & JS -->
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const venues = @json($visitedVenues);

                    if (venues.length > 0) {
                        // Initialize map
                        const map = L.map('visited-map').setView([venues[0].latitude, venues[0].longitude], 12);

                        // Add tile layer
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors'
                        }).addTo(map);

                        // Custom marker icon
                        const visitedIcon = L.divIcon({
                            className: 'custom-marker',
                            html: `<div style="background: #10B981; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 2px 10px rgba(0,0,0,0.3); border: 3px solid white;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                   </div>`,
                            iconSize: [32, 32],
                            iconAnchor: [16, 16]
                        });

                        // Add markers and fit bounds
                        const bounds = [];
                        venues.forEach(venue => {
                            const marker = L.marker([venue.latitude, venue.longitude], { icon: visitedIcon })
                                .addTo(map)
                                .bindPopup(`<strong>${venue.name}</strong><br>${venue.address}`);
                            bounds.push([venue.latitude, venue.longitude]);
                        });

                        // Fit map to show all markers
                        if (bounds.length > 1) {
                            map.fitBounds(bounds, { padding: [50, 50] });
                        }
                    }
                });
            </script>
        @endif

        <!-- Detailed Activity Log -->
        <div class="max-w-7xl mx-auto px-4 pb-12">
            <h2 class="text-xl font-bold text-soboa-blue mb-4 flex items-center gap-2">
                <span></span> Historique détaillé des actions
            </h2>

            <!-- Légende des points -->
            <div class="bg-gradient-to-r from-soboa-blue/5 to-soboa-orange/5 rounded-xl p-4 mb-4 border border-gray-200">
                <h3 class="text-sm font-bold text-gray-700 mb-2">Système de points :</h3>
                <div class="flex flex-wrap gap-3 text-xs">
                    <span class="bg-white px-2 py-1 rounded-full border border-gray-200">+1 pt/connexion/jour</span>
                    <span class="bg-white px-2 py-1 rounded-full border border-gray-200">+1 pt/pronostic</span>
                    <span class="bg-white px-2 py-1 rounded-full border border-gray-200">+3 pts/bon vainqueur</span>
                    <span class="bg-white px-2 py-1 rounded-full border border-gray-200">+3 pts/score exact</span>
                    <span class="bg-white px-2 py-1 rounded-full border border-gray-200">+4 pts/visite lieu</span>
                </div>
            </div>

            @if($activityLog && $activityLog->count() > 0)
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                    <div class="max-h-[600px] overflow-y-auto">
                        @foreach($activityLog as $log)
                            <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition">
                                <div class="flex items-start justify-between gap-4">
                                    <!-- Icon & Details -->
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <!-- Action Type Icon -->
                                            @if($log->source === 'login')
                                                <span class="text-2xl"></span>
                                                <span class="font-bold text-gray-800">Connexion quotidienne</span>
                                            @elseif($log->source === 'bar_visit')
                                                <span class="text-2xl"></span>
                                                <span class="font-bold text-gray-800">Check-in au lieu</span>
                                            @elseif($log->source === 'venue_visit')
                                                <span class="text-2xl"></span>
                                                <span class="font-bold text-gray-800">Pronostic depuis un lieu</span>
                                            @elseif($log->source === 'prediction_participation')
                                                <span class="text-2xl"></span>
                                                <span class="font-bold text-gray-800">Participation au pronostic</span>
                                            @elseif($log->source === 'prediction_winner' || $log->source === 'prediction_correct_winner')
                                                <span class="text-2xl"></span>
                                                <span class="font-bold text-gray-800">Bon vainqueur</span>
                                            @elseif($log->source === 'prediction_exact' || $log->source === 'prediction_exact_score')
                                                <span class="text-2xl"></span>
                                                <span class="font-bold text-gray-800">Score exact</span>
                                            @else
                                                <span class="text-2xl"></span>
                                                <span
                                                    class="font-bold text-gray-800">{{ ucfirst(str_replace('_', ' ', $log->source)) }}</span>
                                            @endif
                                        </div>

                                        <!-- Additional Details -->
                                        <div class="ml-9 space-y-1">
                                            <!-- Date & Time -->
                                            <p class="text-sm text-gray-600">
                                                <span
                                                    class="font-semibold capitalize">{{ $log->created_at->translatedFormat('l d F Y') }}</span>
                                                <span class="text-gray-400">à</span>
                                                <span class="font-semibold">{{ $log->created_at->format('H:i') }}</span>
                                            </p>

                                            <!-- Venue if available -->
                                            @if($log->bar)
                                                <p class="text-sm text-gray-600">
                                                    <span class="text-gray-400">Lieu:</span>
                                                    <span class="font-semibold text-soboa-blue">{{ $log->bar->name }}</span>
                                                </p>
                                                @if($log->bar->address)
                                                    <p class="text-xs text-gray-400">{{ $log->bar->address }}</p>
                                                @endif
                                            @endif

                                            <!-- Match if available -->
                                            @if($log->match)
                                                <p class="text-sm text-gray-600">
                                                    <span class="text-gray-400">Match:</span>
                                                    <span class="font-semibold">
                                                        @if($log->match->homeTeam && $log->match->awayTeam)
                                                            {{ \App\Models\Team::fr($log->match->homeTeam->name) }} vs {{ \App\Models\Team::fr($log->match->awayTeam->name) }}
                                                        @else
                                                            {{ \App\Models\Team::fr($log->match->team_a) ?? 'Équipe A' }} vs
                                                            {{ \App\Models\Team::fr($log->match->team_b) ?? 'Équipe B' }}
                                                        @endif
                                                    </span>
                                                </p>
                                                @if($log->match->match_date)
                                                    <p class="text-xs text-gray-400 capitalize">
                                                        {{ $log->match->match_date->translatedFormat('l d F Y à H:i') }}
                                                    </p>
                                                @endif
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Points Badge -->
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center justify-center px-4 py-2 rounded-full font-black text-xl
                                                @if($log->points >= 5)
                                                    bg-gradient-to-r from-yellow-400 to-orange-500 text-white
                                                @elseif($log->points >= 3)
                                                    bg-gradient-to-r from-green-400 to-blue-500 text-white
                                                @else
                                                    bg-soboa-orange text-black
                                                @endif
                                            ">
                                            +{{ $log->points }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl shadow-elev-1 p-section-md text-center border border-gray-100">
                    <div class="w-20 h-20 mx-auto bg-soboa-orange/10 rounded-full flex items-center justify-center mb-4">
                        <i data-lucide="activity" class="w-10 h-10 text-soboa-orange"></i>
                    </div>
                    <p class="text-soboa-text-dark font-bold text-lg">Aucune activité pour le moment</p>
                    <p class="text-gray-500 text-sm mt-1 mb-4 max-w-sm mx-auto">Gagnez des points via pronostics + visites PDV partenaires.</p>
                    <div class="inline-flex flex-wrap items-center justify-center gap-2">
                        <a href="/matches" class="btn btn-primary btn-md btn-pill">
                            <i data-lucide="target" class="w-4 h-4"></i>
                            Pronostiquer
                        </a>
                        <a href="/map" class="btn btn-secondary btn-md btn-pill">
                            <i data-lucide="map" class="w-4 h-4"></i>
                            Trouver un lieu
                        </a>
                    </div>
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>