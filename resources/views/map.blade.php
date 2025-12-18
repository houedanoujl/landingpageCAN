<x-layouts.app title="Points de Vente">

    @php
        $venuesData = $venues->map(function ($venue) {
            return [
                'id' => $venue->id,
                'name' => $venue->name,
                'address' => $venue->address,
                'zone' => $venue->zone,
                'latitude' => $venue->latitude,
                'longitude' => $venue->longitude,
                'is_active' => $venue->is_active,
                'animations' => $venue->animations->filter(fn($a) => $a->match)->map(function ($animation) {
                    $match = $animation->match;
                    $homeTeam = $match->homeTeam;
                    $awayTeam = $match->awayTeam;

                    $teamA = $homeTeam ? $homeTeam->name : $match->team_a;
                    $teamB = $awayTeam ? $awayTeam->name : $match->team_b;

                    $isTBD = str_contains(strtolower($teamA), 'déterminer') || str_contains(strtolower($teamB), 'déterminer');

                    $matchLabel = $isTBD ? $match->phase_name : ($teamA . ' vs ' . $teamB);

                    return [
                        'id' => $animation->id,
                        'match_label' => $matchLabel,
                        'home_flag' => $homeTeam ? $homeTeam->flag_url : null,
                        'away_flag' => $awayTeam ? $awayTeam->flag_url : null,
                        'score_a' => $match->score_a,
                        'score_b' => $match->score_b,
                        'status' => $match->status,
                        'is_tbd' => $isTBD,
                        'date' => \Carbon\Carbon::parse($animation->animation_date)->format('d/m'),
                        'time' => $animation->animation_time,
                    ];
                })->values()->toArray()
            ];
        });
    @endphp

    <script>
        // Données des venues passées depuis le serveur avec leurs animations
        window.venuesData = {!! json_encode($venuesData) !!};
    </script>

    <div class="min-h-screen bg-gray-50" x-data="{
        userLocation: null,
        locationError: null,
        isChecking: false,
        nearbyVenues: null,
        checkInResult: null,
        venues: window.venuesData || [],
        permissionDenied: false,

        calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        },

        async checkGeolocationPermission() {
            if (!navigator.permissions) {
                return 'unknown';
            }
            try {
                const result = await navigator.permissions.query({ name: 'geolocation' });
                return result.state;
            } catch (error) {
                return 'unknown';
            }
        },

        async performCheckIn(lat, lng) {
            try {
                const csrfToken = document.querySelector('meta[name=csrf-token]').content;
                const response = await fetch('/check-in', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        latitude: lat,
                        longitude: lng
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    this.checkInResult = {
                        success: true,
                        message: data.message,
                        points_awarded: data.points_awarded,
                        total_points: data.total_points
                    };

                    // Update header points
                    if (data.total_points !== undefined) {
                        window.dispatchEvent(new CustomEvent('update-points', {
                            detail: { points: data.total_points }
                        }));
                    }
                } else {
                    this.checkInResult = {
                        success: false,
                        message: data.message || 'Aucun lieu partenaire détecté.'
                    };
                }
            } catch (error) {
                this.checkInResult = {
                    success: false,
                    message: 'Erreur de connexion. Réessayez.'
                };
            }
        },

        async getLocation() {
            this.isChecking = true;
            this.locationError = null;
            this.nearbyVenues = null;
            this.checkInResult = null;

            if (!navigator.geolocation) {
                this.locationError = 'La géolocalisation n\'est pas supportée par votre navigateur.';
                this.isChecking = false;
                return;
            }

            // Vérifier les permissions avant de demander la position
            const permissionState = await this.checkGeolocationPermission();

            // Si déjà refusé, afficher le message mais ne pas redemander
            if (permissionState === 'denied') {
                this.locationError = 'Vous avez refusé l\'accès à votre position.';
                this.permissionDenied = true;
                this.isChecking = false;
                return;
            }

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    this.userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    // Perform check-in
                    await this.performCheckIn(this.userLocation.lat, this.userLocation.lng);

                    // Calculate distances to all venues
                    const venuesWithDistance = this.venues.map(venue => ({
                        ...venue,
                        distance: this.calculateDistance(
                            this.userLocation.lat,
                            this.userLocation.lng,
                            venue.latitude,
                            venue.longitude
                        )
                    }));

                    // Filter venues within 10km and sort by distance
                    this.nearbyVenues = venuesWithDistance
                        .filter(v => v.distance <= 10)
                        .sort((a, b) => a.distance - b.distance);

                    if (this.nearbyVenues.length === 0) {
                        this.nearbyVenues = [];
                    }

                    this.isChecking = false;
                },
                (error) => {
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            this.locationError = 'Vous avez refusé l\'accès à votre position.';
                            this.permissionDenied = true;
                            break;
                        case error.POSITION_UNAVAILABLE:
                            this.locationError = 'Position indisponible.';
                            break;
                        case error.TIMEOUT:
                            this.locationError = 'Délai dépassé.';
                            break;
                        default:
                            this.locationError = 'Erreur lors de la géolocalisation.';
                    }
                    this.isChecking = false;
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }
    }">

        <!-- Location Permission Popup Modal -->
        <div x-show="locationError" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak
            class="fixed inset-0 z-[100] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
            @click.self="locationError = null">

            <div x-show="locationError" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 text-center">

                <!-- Icon -->
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>

                <!-- Title -->
                <h2 class="text-2xl font-black text-soboa-blue mb-4">
                    Localisation requise
                </h2>

                <!-- Error Message -->
                <div class="border rounded-xl p-4 mb-6"
                    :class="permissionDenied ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200'">
                    <p class="font-medium" :class="permissionDenied ? 'text-red-800' : 'text-yellow-800'"
                        x-text="locationError"></p>
                </div>

                <!-- Instructions -->
                <div class="text-left bg-gray-50 rounded-xl p-4 mb-6">
                    <p class="font-bold text-gray-700 mb-3">📱 Pour activer la localisation :</p>
                    <ul class="text-sm text-gray-600 space-y-2">
                        <li class="flex items-start gap-2">
                            <span class="text-soboa-orange font-bold">•</span>
                            <span><strong>iPhone/iPad :</strong> Réglages → Confidentialité → Service de localisation →
                                Safari</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-soboa-orange font-bold">•</span>
                            <span><strong>Android :</strong> Paramètres → Applications → Navigateur → Autorisations →
                                Position</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-soboa-orange font-bold">•</span>
                            <span><strong>Navigateur :</strong> Cliquez sur l'icône 🔒 dans la barre d'adresse →
                                Autorisations</span>
                        </li>
                    </ul>
                </div>

                <!-- Buttons -->
                <div class="flex gap-4">
                    <button @click="locationError = null"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-4 px-6 rounded-xl transition">
                        Fermer
                    </button>
                    <button @click="locationError = null; getLocation()"
                        class="flex-1 bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-4 px-6 rounded-xl shadow-lg transition transform hover:scale-105">
                        Réessayer
                    </button>
                </div>
            </div>
        </div>

        <!-- Header -->
        <div class="relative py-12 px-4 overflow-hidden mb-8 shadow-2xl">
            <!-- Background Image with Overlay -->
            <div class="absolute inset-0 z-0">
                <img src="/images/sen.webp" class="w-full h-full object-cover" alt="Background">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-[1px]"></div>
            </div>
            <div class="relative z-10 max-w-7xl mx-auto text-center">
                <span class="text-soboa-orange font-black text-sm uppercase tracking-widest drop-shadow-md">Gagnez +4
                    points</span>
                <h1 class="text-4xl md:text-5xl font-black text-white mt-2 drop-shadow-2xl">Points de vente partenaires
                </h1>
                <p class="text-white/80 mt-4 max-w-2xl mx-auto font-medium drop-shadow-lg">
                    Visitez nos lieux partenaires et gagnez 4 points bonus par jour !
                </p>
            </div>
        </div>

        <!-- Nearby Venues Section -->
        <div class="max-w-7xl mx-auto px-4 -mt-8">
            <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8 border border-gray-100">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6 mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-soboa-orange/10 rounded-full flex items-center justify-center">
                            <span class="text-3xl">📍</span>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-soboa-blue">Lieux partenaires à proximité</h2>
                            <p class="text-gray-600 text-sm">Trouvez les points de vente près de vous!</p>
                        </div>
                    </div>

                    @if(session('user_id'))
                        <button @click="getLocation()" :disabled="isChecking"
                            class="w-full md:w-auto bg-soboa-orange hover:bg-soboa-orange-dark disabled:bg-gray-400 text-black font-bold py-4 px-8 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                            <svg x-show="isChecking" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span x-text="isChecking ? 'Vérification...' : 'Voir les lieux proches'">Voir les lieux
                                proches</span>
                        </button>
                    @else
                        <a href="/login"
                            class="w-full md:w-auto bg-soboa-blue hover:bg-soboa-blue-dark text-white font-bold py-4 px-8 rounded-xl shadow-lg transition-all text-center">
                            Se connecter
                        </a>
                    @endif
                </div>

                <!-- Check-in Result -->
                <div x-show="checkInResult" x-cloak class="mb-6">
                    <div x-show="checkInResult?.success"
                        class="bg-green-50 border-2 border-green-200 text-green-700 px-6 py-4 rounded-xl">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-3xl">🎉</span>
                            <div>
                                <p class="font-bold text-lg" x-text="checkInResult?.message"></p>
                                <p class="text-sm text-green-600 mt-1">
                                    +<span x-text="checkInResult?.points_awarded"></span> points • Total: <span
                                        x-text="checkInResult?.total_points"></span> pts
                                </p>
                            </div>
                        </div>
                    </div>
                    <div x-show="!checkInResult?.success"
                        class="bg-yellow-50 border-2 border-yellow-200 text-yellow-700 px-6 py-4 rounded-xl flex items-center gap-3">
                        <span class="text-3xl">📍</span>
                        <p x-text="checkInResult?.message" class="font-medium"></p>
                    </div>
                </div>

                <!-- Nearby Venues List -->
                <div x-show="nearbyVenues !== null" x-cloak>
                    <template x-if="nearbyVenues.length > 0">
                        <div class="space-y-4">
                            <p class="text-soboa-blue font-bold text-lg">
                                <span x-text="nearbyVenues.length"></span> lieu(x) trouvé(s) à proximité:
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <template x-for="venue in nearbyVenues" :key="venue.id">
                                    <div
                                        class="bg-gradient-to-br from-soboa-orange/5 to-soboa-blue/5 rounded-xl p-4 border border-soboa-orange/20 hover:border-soboa-orange/50 transition">
                                        <div class="flex items-start gap-3 mb-3">
                                            <span class="text-2xl">📍</span>
                                            <div class="flex-1">
                                                <h3 class="font-bold text-soboa-blue" x-text="venue.name"></h3>
                                                <p class="text-gray-600 text-sm" x-text="venue.zone || venue.address">
                                                </p>

                                                <!-- Afficher les matchs -->
                                                <template x-if="venue.animations && venue.animations.length > 0">
                                                    <div class="mt-4 space-y-3">
                                                        <template x-for="animation in venue.animations.slice(0, 2)"
                                                            :key="animation.id">
                                                            <div
                                                                class="flex items-center gap-2 text-sm bg-white/50 p-2 rounded-lg border border-gray-100">
                                                                <div class="flex items-center gap-1">
                                                                    <template x-if="animation.home_flag">
                                                                        <img :src="animation.home_flag"
                                                                            class="w-5 h-4 object-contain rounded-sm" />
                                                                    </template>
                                                                    <template x-if="!animation.home_flag">
                                                                        <span>⚽</span>
                                                                    </template>
                                                                </div>
                                                                <div class="flex-1 min-w-0">
                                                                    <span
                                                                        class="font-medium text-gray-700 truncate block"
                                                                        x-text="animation.match_label"></span>
                                                                    <template x-if="animation.status === 'finished'">
                                                                        <span
                                                                            class="text-[10px] bg-gray-200 text-gray-700 px-1.5 py-0.5 rounded font-bold">TERMINE</span>
                                                                    </template>
                                                                    <template x-if="animation.status === 'live'">
                                                                        <span
                                                                            class="text-[10px] bg-red-500 text-white px-1.5 py-0.5 rounded font-bold animate-pulse">LIVE</span>
                                                                    </template>
                                                                </div>

                                                                <template x-if="animation.status === 'finished'">
                                                                    <span
                                                                        class="text-soboa-orange font-black text-sm bg-soboa-orange/10 px-2 py-1 rounded"
                                                                        x-text="animation.score_a + ' - ' + animation.score_b"></span>
                                                                </template>
                                                                <div class="flex items-center gap-1">
                                                                    <template x-if="animation.away_flag">
                                                                        <img :src="animation.away_flag"
                                                                            class="w-5 h-4 object-contain rounded-sm" />
                                                                    </template>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <template x-if="venue.animations.length > 2">
                                                            <div class="text-xs text-soboa-blue font-bold mt-1 ml-2">
                                                                +<span x-text="venue.animations.length - 2"></span>
                                                                autre(s) match(s)
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-soboa-orange font-bold text-lg">
                                                <span x-text="venue.distance.toFixed(1)"></span> km
                                            </span>
                                            <a href="#"
                                                @click.prevent="document.querySelector('[id=map]').scrollIntoView({ behavior: 'smooth' })"
                                                class="bg-soboa-blue hover:bg-soboa-blue-dark text-white font-bold py-2 px-4 rounded-lg transition text-sm">
                                                Voir sur la carte →
                                            </a>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <template x-if="nearbyVenues.length === 0">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
                            <p class="text-yellow-700 font-medium">
                                <span class="text-2xl">📍</span> Aucun lieu partenaire à proximité (rayon: 10 km)
                            </p>
                            <p class="text-yellow-600 text-sm mt-2">Consultez la carte ou la liste complète ci-dessous
                                pour voir tous les lieux partenaires.</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Map Section -->
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-xl font-bold text-soboa-blue flex items-center gap-2">
                        <span>🗺️</span> Carte des points de vente
                    </h3>
                </div>

                <!-- Leaflet Map -->
                <div id="map" class="h-[500px] w-full bg-gray-100"></div>
            </div>
        </div>

    </div>

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize map centered on Dakar, Senegal
            const map = L.map('map').setView([14.7167, -17.4677], 12);

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Custom marker icon using Gazelle logo
            const venueIcon = L.icon({
                iconUrl: '/images/logoGazelle.jpeg',
                iconSize: [40, 40],
                iconAnchor: [20, 40],
                popupAnchor: [0, -40],
                className: 'gazelle-marker'
            });

            // Add venue markers
            @foreach($venues as $venue)
                L.marker([{{ $venue->latitude }}, {{ $venue->longitude }}], { icon: venueIcon })
                    .addTo(map)
                    .bindPopup('<strong>{{ $venue->name }}</strong><br>{{ $venue->address }}');
            @endforeach
        });
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Style for Gazelle logo markers */
        .gazelle-marker {
            border-radius: 50%;
            border: 3px solid #E96611;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            background: white;
        }

        .gazelle-marker img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</x-layouts.app>