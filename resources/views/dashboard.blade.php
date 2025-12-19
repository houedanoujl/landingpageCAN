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
                    <span class="text-3xl">🏆</span>
                    <p class="text-2xl font-black text-soboa-blue mt-2">{{ $rank ?? '--' }}</p>
                    <p class="text-gray-500 text-sm">Classement</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-lg text-center">
                    <span class="text-3xl">⚽</span>
                    <p class="text-2xl font-black text-soboa-blue mt-2">{{ $predictionCount ?? 0 }}</p>
                    <p class="text-gray-500 text-sm">Pronostics</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-lg text-center">
                    <span class="text-3xl">🎯</span>
                    <p class="text-2xl font-black text-soboa-blue mt-2">{{ $correctPredictions ?? 0 }}</p>
                    <p class="text-gray-500 text-sm">Bons résultats</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-lg text-center">
                    <span class="text-3xl">📍</span>
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
                        <span>📅</span> Prochain match
                    </h2>

                    @if($nextMatch)
                        <x-match-card :match="$nextMatch" />
                    @else
                        <div class="bg-white rounded-2xl p-8 shadow-lg text-center">
                            <span class="text-5xl block mb-4">⚽</span>
                            <p class="text-gray-600 font-medium">Aucun match à venir</p>
                            <p class="text-gray-400 text-sm">Revenez bientôt!</p>
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
                        <span>⚡</span> Actions rapides
                    </h2>

                    <div class="space-y-3">
                        <a href="/matches"
                            class="block bg-soboa-orange text-black rounded-xl p-4 font-bold hover:bg-soboa-orange-dark transition-colors shadow-lg">
                            ⚽ Faire un pronostic
                        </a>
                        <a href="/map"
                            class="block bg-soboa-blue text-white rounded-xl p-4 font-bold hover:bg-soboa-blue-dark transition-colors shadow-lg">
                            📍 Lieux partenaires (+4 pts)
                        </a>
                        <a href="/leaderboard"
                            class="block bg-white text-soboa-blue rounded-xl p-4 font-bold hover:bg-gray-50 transition-colors shadow-lg border border-gray-200">
                            🏆 Voir le classement
                        </a>
                        <a href="/mes-pronostics"
                            class="block bg-white text-soboa-blue rounded-xl p-4 font-bold hover:bg-gray-50 transition-colors shadow-lg border border-gray-200">
                            📊 Historique pronostics
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Predictions -->
        <div class="max-w-7xl mx-auto px-4 pb-8">
            <h2 class="text-xl font-bold text-soboa-blue mb-4 flex items-center gap-2">
                <span>📜</span> Derniers pronostics
            </h2>

            @if($recentPredictions && $recentPredictions->count() > 0)
                <div class="space-y-3">
                    @foreach($recentPredictions as $prediction)
                        <div class="bg-white rounded-xl p-4 shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="text-center">
                                    <span
                                        class="text-sm font-bold text-gray-800">{{ $prediction->match->team_a ?? 'Équipe A' }}</span>
                                    <span class="text-lg font-black text-soboa-orange mx-2">{{ $prediction->score_a }} -
                                        {{ $prediction->score_b }}</span>
                                    <span
                                        class="text-sm font-bold text-gray-800">{{ $prediction->match->team_b ?? 'Équipe B' }}</span>
                                </div>
                            </div>
                            <div>
                                @if($prediction->points_earned !== null)
                                    @if($prediction->points_earned >= 6)
                                        <span
                                            class="bg-green-100 text-green-700 font-bold px-3 py-1 rounded-full text-sm">+{{ $prediction->points_earned }}
                                            pts 🏆</span>
                                    @elseif($prediction->points_earned > 0)
                                        <span
                                            class="bg-yellow-100 text-yellow-700 font-bold px-3 py-1 rounded-full text-sm">+{{ $prediction->points_earned }}
                                            pts</span>
                                    @else
                                        <span class="bg-gray-100 text-gray-500 font-bold px-3 py-1 rounded-full text-sm">+0 pts</span>
                                    @endif
                                @else
                                    <span class="bg-soboa-blue/10 text-soboa-blue font-bold px-3 py-1 rounded-full text-sm">En
                                        attente</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <a href="/mes-pronostics"
                    class="mt-4 inline-flex items-center gap-2 text-soboa-orange font-bold hover:underline">
                    Voir tout l'historique
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            @else
                <div class="bg-white rounded-xl p-8 shadow-lg text-center">
                    <span class="text-5xl block mb-4">📝</span>
                    <p class="text-gray-600 font-medium">Aucun pronostic pour le moment</p>
                    <a href="/matches" class="text-soboa-orange font-bold hover:underline">Faites votre premier
                        pronostic!</a>
                </div>
            @endif
        </div>

        <!-- Points Details -->
        <div class="max-w-7xl mx-auto px-4 pb-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- Points by Date -->
                <div>
                    <h2 class="text-xl font-bold text-soboa-blue mb-4 flex items-center gap-2">
                        <span>📅</span> Points par date
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
                        <div class="bg-white rounded-xl p-8 shadow-lg text-center">
                            <span class="text-5xl block mb-4">📊</span>
                            <p class="text-gray-600 font-medium">Aucun point gagné récemment</p>
                        </div>
                    @endif
                </div>

                <!-- Points by Venue -->
                <div>
                    <h2 class="text-xl font-bold text-soboa-blue mb-4 flex items-center gap-2">
                        <span>📍</span> Points par lieu
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
                        <div class="bg-white rounded-xl p-8 shadow-lg text-center">
                            <span class="text-5xl block mb-4">🏪</span>
                            <p class="text-gray-600 font-medium">Aucun lieu visité</p>
                            <a href="/map" class="text-soboa-orange font-bold hover:underline">Visitez un lieu
                                partenaire!</a>
                        </div>
                    @endif
                </div>

            </div>
        </div>

        <!-- Visited Venues Map -->
        @if($visitedVenues && $visitedVenues->count() > 0)
            <div class="max-w-7xl mx-auto px-4 pb-12">
                <h2 class="text-xl font-bold text-soboa-blue mb-4 flex items-center gap-2">
                    <span>🗺️</span> Carte de mes lieux visités
                </h2>

                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                    <div id="visited-map" class="h-[400px] w-full"></div>
                </div>

                <div class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach($visitedVenues as $venue)
                        <div class="bg-white rounded-lg p-3 shadow border border-gray-100">
                            <p class="font-bold text-soboa-blue text-sm">📍 {{ $venue->name }}</p>
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
                                .bindPopup(`<strong>✅ ${venue.name}</strong><br>${venue.address}`);
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
                <span>📋</span> Historique détaillé des actions
            </h2>

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
                                                <span class="text-2xl">🔐</span>
                                                <span class="font-bold text-gray-800">Connexion quotidienne</span>
                                            @elseif($log->source === 'bar_visit')
                                                <span class="text-2xl">📍</span>
                                                <span class="font-bold text-gray-800">Check-in au lieu</span>
                                            @elseif($log->source === 'venue_visit')
                                                <span class="text-2xl">🎯</span>
                                                <span class="font-bold text-gray-800">Pronostic depuis un lieu</span>
                                            @elseif($log->source === 'prediction_participation')
                                                <span class="text-2xl">🎲</span>
                                                <span class="font-bold text-gray-800">Participation au pronostic</span>
                                            @elseif($log->source === 'prediction_winner' || $log->source === 'prediction_correct_winner')
                                                <span class="text-2xl">🎯</span>
                                                <span class="font-bold text-gray-800">Bon vainqueur</span>
                                            @elseif($log->source === 'prediction_exact' || $log->source === 'prediction_exact_score')
                                                <span class="text-2xl">🎊</span>
                                                <span class="font-bold text-gray-800">Score exact</span>
                                            @else
                                                <span class="text-2xl">⭐</span>
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
                                                    <span class="text-gray-400">📍 Lieu:</span>
                                                    <span class="font-semibold text-soboa-blue">{{ $log->bar->name }}</span>
                                                </p>
                                                @if($log->bar->address)
                                                    <p class="text-xs text-gray-400">{{ $log->bar->address }}</p>
                                                @endif
                                            @endif

                                            <!-- Match if available -->
                                            @if($log->match)
                                                <p class="text-sm text-gray-600">
                                                    <span class="text-gray-400">⚽ Match:</span>
                                                    <span class="font-semibold">
                                                        @if($log->match->homeTeam && $log->match->awayTeam)
                                                            {{ $log->match->homeTeam->name }} vs {{ $log->match->awayTeam->name }}
                                                        @else
                                                            {{ $log->match->team_a ?? 'Équipe A' }} vs
                                                            {{ $log->match->team_b ?? 'Équipe B' }}
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
                <div class="bg-white rounded-2xl shadow-xl p-8 text-center border border-gray-100">
                    <div class="text-6xl mb-4">📭</div>
                    <p class="text-gray-500 text-lg">Aucune activité pour le moment</p>
                    <p class="text-gray-400 text-sm mt-2">Commencez à gagner des points en faisant des pronostics et en
                        visitant nos lieux partenaires !</p>
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>