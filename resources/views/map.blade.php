<x-layouts.app title="Animations Gazelle">

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
                'type_pdv' => $venue->type_pdv ?? 'dakar',
                'type_pdv_name' => $venue->type_pdv_name ?? 'Points de vente Dakar',
                'animations' => $venue->animations->filter(fn($a) => $a->match)->map(function ($animation) {
                    $match = $animation->match;

                    // Use the model's display_label attribute
                    $matchLabel = $match->display_label;
                    $isADeterminer = $match->is_tbd;

                    // Parser l'heure qui peut être en format "15 H", "15:00", "15H00", etc.
                    $displayTime = $animation->animation_time;
                    if ($displayTime) {
                        if (preg_match('/^(\d{1,2})\s*[Hh]?\s*(\d{0,2})?$/', trim($displayTime), $matches)) {
                            $hour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                            $minute = isset($matches[2]) && $matches[2] !== '' ? str_pad($matches[2], 2, '0', STR_PAD_LEFT) : '00';
                            $displayTime = $hour . ':' . $minute;
                        } elseif (preg_match('/^(\d{1,2}):(\d{2})/', $displayTime, $matches)) {
                            $displayTime = str_pad($matches[1], 2, '0', STR_PAD_LEFT) . ':' . $matches[2];
                        }
                    }

                    return [
                        'id' => $animation->id,
                        'match_label' => $matchLabel,
                        'home_flag' => (!$isADeterminer && $match->homeTeam) ? $match->homeTeam->flag_url : null,
                        'away_flag' => (!$isADeterminer && $match->awayTeam) ? $match->awayTeam->flag_url : null,
                        'score_a' => $match->score_a,
                        'score_b' => $match->score_b,
                        'status' => $match->status,
                        'is_tbd' => $isADeterminer,
                        'date' => \Carbon\Carbon::parse($animation->animation_date)->format('d/m'),
                        'time' => $displayTime,
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
                <span class="text-soboa-orange font-black text-sm uppercase tracking-widest drop-shadow-md">🎉 Le goût de notre victoire</span>
                <h1 class="text-4xl md:text-5xl font-black text-white mt-2 drop-shadow-2xl">Animations Gazelle</h1>
                <p class="text-white/80 mt-4 max-w-2xl mx-auto font-medium drop-shadow-lg">
                    Découvrez les lieux partenaires et vivez les matchs avec nous ! Gagnez 4 points bonus par visite.
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
                                            <span class="text-2xl" x-text="
                                                venue.type_pdv === 'dakar' ? '🏙️' :
                                                venue.type_pdv === 'regions' ? '🗺️' :
                                                venue.type_pdv === 'chr' ? '🍽️' :
                                                venue.type_pdv === 'fanzone' ? '🎉' :
                                                venue.type_pdv === 'fanzone_public' ? '🎪' :
                                                venue.type_pdv === 'fanzone_hotel' ? '🏨' : '📍'
                                            "></span>
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                                    <h3 class="font-bold text-soboa-blue" x-text="venue.name"></h3>
                                                    <span 
                                                        class="text-xs font-bold px-2 py-0.5 rounded-full"
                                                        :class="{
                                                            'bg-blue-100 text-blue-800': venue.type_pdv === 'dakar',
                                                            'bg-green-100 text-green-800': venue.type_pdv === 'regions',
                                                            'bg-orange-100 text-orange-800': venue.type_pdv === 'chr',
                                                            'bg-purple-100 text-purple-800': venue.type_pdv === 'fanzone',
                                                            'bg-yellow-100 text-yellow-800': venue.type_pdv === 'fanzone_public',
                                                            'bg-pink-100 text-pink-800': venue.type_pdv === 'fanzone_hotel'
                                                        }"
                                                        x-text="venue.type_pdv_name">
                                                    </span>
                                                </div>
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
                <div id="map" class="h-[500px] w-full bg-gray-100 rounded-t-lg"></div>

                <!-- Légende -->
                @php
                    // Compter le nombre de PDV par type
                    $typeCounts = $venues->groupBy('type_pdv')->map->count();
                    
                    $legendItems = [
                        'dakar' => ['emoji' => '🏙️', 'color' => '#3b82f6', 'label' => 'Points de vente Dakar'],
                        'regions' => ['emoji' => '🗺️', 'color' => '#22c55e', 'label' => 'Points de vente Régions'],
                        'chr' => ['emoji' => '🍽️', 'color' => '#f97316', 'label' => 'Cafés-Hôtel-Restaurants'],
                        'fanzone' => ['emoji' => '🎉', 'color' => '#a855f7', 'label' => 'Fanzones'],
                        'fanzone_public' => ['emoji' => '🎪', 'color' => '#eab308', 'label' => 'Fanzone tout public'],
                        'fanzone_hotel' => ['emoji' => '🏨', 'color' => '#ec4899', 'label' => 'Fanzone hôtel'],
                    ];
                @endphp
                <div class="bg-white border-t-2 border-gray-200 rounded-b-lg p-4 shadow-lg">
                    <div class="flex items-center justify-center gap-6 flex-wrap">
                        <div class="text-sm font-bold text-gray-700 mr-2">Légende:</div>

                        @foreach($legendItems as $type => $item)
                            @if(isset($typeCounts[$type]) && $typeCounts[$type] > 0)
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: {{ $item['color'] }}; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                    <span class="text-base">{{ $item['emoji'] }}</span>
                                </div>
                                <span class="text-sm font-medium text-gray-700">{{ $item['label'] }} ({{ $typeCounts[$type] }})</span>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Calendrier des Animations - GRILLE CLASSIQUE -->
        <div class="max-w-7xl mx-auto px-4 py-12" x-data="{
            selectedDay: null,
            selectedAnimations: [],
            showModal: false,
            
            openDayModal(day, animations) {
                this.selectedDay = day;
                this.selectedAnimations = animations;
                this.showModal = true;
            },
            
            closeDayModal() {
                this.showModal = false;
                this.selectedDay = null;
                this.selectedAnimations = [];
            }
        }">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-soboa-blue to-soboa-blue-dark">
                    <h3 class="text-2xl font-black text-white flex items-center gap-3">
                        <span>📅</span> Calendrier des animations
                    </h3>
                    <p class="text-white/80 mt-1">Cliquez sur un jour pour voir toutes les animations</p>
                </div>

                @if(isset($animations) && $animations->count() > 0)
                @php
                    // Décembre 2025 - premier mois
                    $currentMonth = \Carbon\Carbon::create(2025, 12, 1);
                    $endMonth = $currentMonth->copy()->endOfMonth();
                    $startOfWeek = $currentMonth->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                    $endOfWeek = $endMonth->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
                    
                    // Créer un tableau des animations par date pour un accès rapide
                    $animationsByDate = [];
                    foreach ($animations as $date => $dayAnimations) {
                        $animationsByDate[$date] = $dayAnimations;
                    }
                @endphp
                
                <div class="p-4 md:p-6">
                    <!-- Mois affiché -->
                    <div class="flex items-center justify-center mb-6">
                        <h4 class="text-2xl font-black text-soboa-blue capitalize">
                            {{ $currentMonth->locale('fr')->isoFormat('MMMM YYYY') }}
                        </h4>
                    </div>
                    
                    <!-- En-têtes des jours de la semaine -->
                    <div style="display: grid; grid-template-columns: repeat(7, 1fr);" class="border-b-2 border-soboa-blue mb-1">
                        <div class="py-3 text-center font-bold text-soboa-blue bg-soboa-blue/5">Lun</div>
                        <div class="py-3 text-center font-bold text-soboa-blue bg-soboa-blue/5">Mar</div>
                        <div class="py-3 text-center font-bold text-soboa-blue bg-soboa-blue/5">Mer</div>
                        <div class="py-3 text-center font-bold text-soboa-blue bg-soboa-blue/5">Jeu</div>
                        <div class="py-3 text-center font-bold text-soboa-blue bg-soboa-blue/5">Ven</div>
                        <div class="py-3 text-center font-bold text-soboa-orange bg-soboa-orange/5">Sam</div>
                        <div class="py-3 text-center font-bold text-soboa-orange bg-soboa-orange/5">Dim</div>
                    </div>
                    
                    <!-- GRILLE CALENDRIER -->
                    <div style="display: grid; grid-template-columns: repeat(7, 1fr);">
                        @php
                            $currentDate = $startOfWeek->copy();
                            $today = \Carbon\Carbon::today();
                        @endphp
                        
                        @while($currentDate <= $endOfWeek)
                            @php
                                $dateKey = $currentDate->format('Y-m-d');
                                $isCurrentMonth = $currentDate->month === 12; // Décembre
                                $isToday = $currentDate->isSameDay($today);
                                $hasAnimations = isset($animationsByDate[$dateKey]);
                                $dayAnimations = $hasAnimations ? $animationsByDate[$dateKey] : collect();
                                $isWeekend = $currentDate->isWeekend();
                            @endphp
                            
                            <div class="border border-gray-200 {{ $isCurrentMonth ? '' : 'bg-gray-100' }} {{ $isToday ? 'bg-yellow-50 ring-2 ring-soboa-orange ring-inset' : '' }} {{ $isWeekend && $isCurrentMonth ? 'bg-gray-50' : '' }} {{ $hasAnimations && $isCurrentMonth ? 'cursor-pointer hover:bg-soboa-blue/5 transition-colors' : '' }}" style="min-height: 90px;"
                                @if($hasAnimations && $isCurrentMonth)
                                @php
                                    $animationsJson = $dayAnimations->map(function($anim) {
                                        $displayTime = '';
                                        if ($anim->animation_time) {
                                            $timeStr = $anim->animation_time;
                                            if (preg_match('/^(\d{1,2})\s*[Hh]?\s*(\d{0,2})?$/', trim($timeStr), $matches)) {
                                                $hour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                                                $minute = isset($matches[2]) && $matches[2] !== '' ? str_pad($matches[2], 2, '0', STR_PAD_LEFT) : '00';
                                                $displayTime = $hour . ':' . $minute;
                                            } elseif (preg_match('/^(\d{1,2}):(\d{2})/', $timeStr, $matches)) {
                                                $displayTime = str_pad($matches[1], 2, '0', STR_PAD_LEFT) . ':' . $matches[2];
                                            }
                                        }
                                        return [
                                            'id' => $anim->id,
                                            'time' => $displayTime,
                                            'match_label' => $anim->match ? $anim->match->display_label : 'À définir',
                                            'bar_name' => $anim->bar ? $anim->bar->name : '',
                                            'bar_address' => $anim->bar ? $anim->bar->address : '',
                                            'home_flag' => ($anim->match && $anim->match->homeTeam && !$anim->match->is_tbd) ? $anim->match->homeTeam->flag_url : null,
                                            'away_flag' => ($anim->match && $anim->match->awayTeam && !$anim->match->is_tbd) ? $anim->match->awayTeam->flag_url : null,
                                        ];
                                    })->toJson();
                                    $dayLabel = $currentDate->locale('fr')->isoFormat('dddd D MMMM YYYY');
                                @endphp
                                @click="openDayModal('{{ $dayLabel }}', {{ $animationsJson }})"
                                @endif
                            >
                                <!-- Numéro du jour -->
                                <div class="p-1 {{ $isToday ? 'bg-soboa-orange' : ($hasAnimations && $isCurrentMonth ? 'bg-soboa-blue' : '') }}">
                                    <div class="flex items-center justify-between">
                                        <span class="text-lg font-black {{ $isToday ? 'text-black' : ($hasAnimations && $isCurrentMonth ? 'text-white' : ($isCurrentMonth ? 'text-gray-800' : 'text-gray-400')) }}">
                                            {{ $currentDate->day }}
                                        </span>
                                        @if($isToday)
                                            <span class="text-[10px] font-bold text-black">AUJOURD'HUI</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Animations du jour -->
                                @if($hasAnimations && $isCurrentMonth)
                                    <div class="p-1 space-y-1">
                                        @foreach($dayAnimations->take(2) as $animation)
                                            @php
                                                $displayTime = '';
                                                if ($animation->animation_time) {
                                                    $timeStr = $animation->animation_time;
                                                    if (preg_match('/^(\d{1,2})\s*[Hh]?\s*(\d{0,2})?$/', trim($timeStr), $matches)) {
                                                        $hour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                                                        $displayTime = $hour . 'h';
                                                    } elseif (preg_match('/^(\d{1,2}):(\d{2})/', $timeStr, $matches)) {
                                                        $displayTime = $matches[1] . 'h';
                                                    }
                                                }
                                            @endphp
                                            <div class="bg-soboa-blue/10 rounded px-1 py-0.5 text-[10px] md:text-xs truncate" title="{{ $animation->match ? $animation->match->display_label : '' }}">
                                                <span class="font-bold text-soboa-blue">{{ $displayTime }}</span>
                                                @if($animation->match && $animation->match->homeTeam && !$animation->match->is_tbd)
                                                    <img src="{{ $animation->match->homeTeam->flag_url }}" class="w-3 h-2 inline-block ml-1" alt="">
                                                @endif
                                                @if($animation->match && $animation->match->awayTeam && !$animation->match->is_tbd)
                                                    <img src="{{ $animation->match->awayTeam->flag_url }}" class="w-3 h-2 inline-block" alt="">
                                                @endif
                                            </div>
                                        @endforeach
                                        @if($dayAnimations->count() > 2)
                                            <div class="text-[10px] text-soboa-orange font-bold px-1">
                                                +{{ $dayAnimations->count() - 2 }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            
                            @php
                                $currentDate->addDay();
                            @endphp
                        @endwhile
                    </div>
                    
                    <!-- Janvier 2026 -->
                    @php
                        $currentMonth2 = \Carbon\Carbon::create(2026, 1, 1);
                        $endMonth2 = $currentMonth2->copy()->endOfMonth();
                        $startOfWeek2 = $currentMonth2->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                        $endOfWeek2 = $endMonth2->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
                    @endphp
                    
                    <!-- Titre Janvier 2026 -->
                    <div class="flex items-center justify-center my-6 pt-6 border-t-2 border-gray-200">
                        <h4 class="text-2xl font-black text-soboa-blue capitalize">
                            {{ $currentMonth2->locale('fr')->isoFormat('MMMM YYYY') }}
                        </h4>
                    </div>
                    
                    <!-- En-têtes des jours Janvier 2026 -->
                    <div style="display: grid; grid-template-columns: repeat(7, 1fr);" class="border-b-2 border-soboa-blue mb-1">
                        <div class="py-3 text-center font-bold text-soboa-blue bg-soboa-blue/5">Lun</div>
                        <div class="py-3 text-center font-bold text-soboa-blue bg-soboa-blue/5">Mar</div>
                        <div class="py-3 text-center font-bold text-soboa-blue bg-soboa-blue/5">Mer</div>
                        <div class="py-3 text-center font-bold text-soboa-blue bg-soboa-blue/5">Jeu</div>
                        <div class="py-3 text-center font-bold text-soboa-blue bg-soboa-blue/5">Ven</div>
                        <div class="py-3 text-center font-bold text-soboa-orange bg-soboa-orange/5">Sam</div>
                        <div class="py-3 text-center font-bold text-soboa-orange bg-soboa-orange/5">Dim</div>
                    </div>
                    
                    <!-- GRILLE JANVIER 2026 -->
                    <div style="display: grid; grid-template-columns: repeat(7, 1fr);">
                        @php
                            $currentDate2 = $startOfWeek2->copy();
                        @endphp
                        
                        @while($currentDate2 <= $endOfWeek2)
                            @php
                                $dateKey2 = $currentDate2->format('Y-m-d');
                                $isCurrentMonth2 = $currentDate2->month === 1 && $currentDate2->year === 2026; // Janvier 2026
                                $isToday2 = $currentDate2->isSameDay($today);
                                $hasAnimations2 = isset($animationsByDate[$dateKey2]);
                                $dayAnimations2 = $hasAnimations2 ? $animationsByDate[$dateKey2] : collect();
                                $isWeekend2 = $currentDate2->isWeekend();
                            @endphp
                            
                            <div class="border border-gray-200 {{ $isCurrentMonth2 ? '' : 'bg-gray-100' }} {{ $isToday2 ? 'bg-yellow-50 ring-2 ring-soboa-orange ring-inset' : '' }} {{ $isWeekend2 && $isCurrentMonth2 ? 'bg-gray-50' : '' }} {{ $hasAnimations2 && $isCurrentMonth2 ? 'cursor-pointer hover:bg-soboa-blue/5 transition-colors' : '' }}" style="min-height: 90px;"
                                @if($hasAnimations2 && $isCurrentMonth2)
                                @php
                                    $animationsJson2 = $dayAnimations2->map(function($anim) {
                                        $displayTime = '';
                                        if ($anim->animation_time) {
                                            $timeStr = $anim->animation_time;
                                            if (preg_match('/^(\d{1,2})\s*[Hh]?\s*(\d{0,2})?$/', trim($timeStr), $matches)) {
                                                $hour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                                                $minute = isset($matches[2]) && $matches[2] !== '' ? str_pad($matches[2], 2, '0', STR_PAD_LEFT) : '00';
                                                $displayTime = $hour . ':' . $minute;
                                            } elseif (preg_match('/^(\d{1,2}):(\d{2})/', $timeStr, $matches)) {
                                                $displayTime = str_pad($matches[1], 2, '0', STR_PAD_LEFT) . ':' . $matches[2];
                                            }
                                        }
                                        return [
                                            'id' => $anim->id,
                                            'time' => $displayTime,
                                            'match_label' => $anim->match ? $anim->match->display_label : 'À définir',
                                            'bar_name' => $anim->bar ? $anim->bar->name : '',
                                            'bar_address' => $anim->bar ? $anim->bar->address : '',
                                            'home_flag' => ($anim->match && $anim->match->homeTeam && !$anim->match->is_tbd) ? $anim->match->homeTeam->flag_url : null,
                                            'away_flag' => ($anim->match && $anim->match->awayTeam && !$anim->match->is_tbd) ? $anim->match->awayTeam->flag_url : null,
                                        ];
                                    })->toJson();
                                    $dayLabel2 = $currentDate2->locale('fr')->isoFormat('dddd D MMMM YYYY');
                                @endphp
                                @click="openDayModal('{{ $dayLabel2 }}', {{ $animationsJson2 }})"
                                @endif
                            >
                                <!-- Numéro du jour -->
                                <div class="p-1 {{ $isToday2 ? 'bg-soboa-orange' : ($hasAnimations2 && $isCurrentMonth2 ? 'bg-soboa-blue' : '') }}">
                                    <div class="flex items-center justify-between">
                                        <span class="text-lg font-black {{ $isToday2 ? 'text-black' : ($hasAnimations2 && $isCurrentMonth2 ? 'text-white' : ($isCurrentMonth2 ? 'text-gray-800' : 'text-gray-400')) }}">
                                            {{ $currentDate2->day }}
                                        </span>
                                        @if($isToday2)
                                            <span class="text-[10px] font-bold text-black">AUJOURD'HUI</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Animations du jour -->
                                @if($hasAnimations2 && $isCurrentMonth2)
                                    <div class="p-1 space-y-1">
                                        @foreach($dayAnimations2->take(2) as $animation)
                                            @php
                                                $displayTime = '';
                                                if ($animation->animation_time) {
                                                    $timeStr = $animation->animation_time;
                                                    if (preg_match('/^(\d{1,2})\s*[Hh]?\s*(\d{0,2})?$/', trim($timeStr), $matches)) {
                                                        $hour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                                                        $displayTime = $hour . 'h';
                                                    } elseif (preg_match('/^(\d{1,2}):(\d{2})/', $timeStr, $matches)) {
                                                        $displayTime = $matches[1] . 'h';
                                                    }
                                                }
                                            @endphp
                                            <div class="bg-soboa-blue/10 rounded px-1 py-0.5 text-[10px] md:text-xs truncate" title="{{ $animation->match ? $animation->match->display_label : '' }}">
                                                <span class="font-bold text-soboa-blue">{{ $displayTime }}</span>
                                                @if($animation->match && $animation->match->homeTeam && !$animation->match->is_tbd)
                                                    <img src="{{ $animation->match->homeTeam->flag_url }}" class="w-3 h-2 inline-block ml-1" alt="">
                                                @endif
                                                @if($animation->match && $animation->match->awayTeam && !$animation->match->is_tbd)
                                                    <img src="{{ $animation->match->awayTeam->flag_url }}" class="w-3 h-2 inline-block" alt="">
                                                @endif
                                            </div>
                                        @endforeach
                                        @if($dayAnimations2->count() > 2)
                                            <div class="text-[10px] text-soboa-orange font-bold px-1">
                                                +{{ $dayAnimations2->count() - 2 }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            
                            @php
                                $currentDate2->addDay();
                            @endphp
                        @endwhile
                    </div>
                    
                    <!-- Légende -->
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <div class="flex flex-wrap items-center justify-center gap-6 text-sm">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 bg-soboa-blue rounded"></div>
                                <span class="text-gray-700 font-medium">Jour avec animation(s)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 bg-soboa-orange rounded"></div>
                                <span class="text-gray-700 font-medium">Aujourd'hui</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 bg-gray-100 border border-gray-300 rounded"></div>
                                <span class="text-gray-700 font-medium">Hors mois</span>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="p-8 text-center">
                    <span class="text-4xl mb-4 block">📅</span>
                    <h4 class="text-xl font-bold text-gray-700 mb-2">Aucune animation programmée</h4>
                    <p class="text-gray-500">Les prochaines animations seront bientôt annoncées !</p>
                </div>
                @endif
            </div>

            <!-- Modal Popup pour les animations du jour -->
            <div x-show="showModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                 style="z-index: 9999;"
                 @click.self="closeDayModal()"
                 @keydown.escape.window="closeDayModal()"
                 x-cloak>
                <div x-show="showModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[80vh] overflow-hidden relative"
                     @click.stop>
                    <!-- Bouton fermer en haut à droite -->
                    <button @click="closeDayModal()" class="absolute top-3 right-3 z-10 bg-white/90 hover:bg-white text-gray-600 hover:text-gray-900 p-2 rounded-full shadow-lg transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <!-- Header du modal -->
                    <div class="bg-gradient-to-r from-soboa-blue to-soboa-blue-dark p-4 pr-14">
                        <h3 class="text-xl font-bold text-white capitalize" x-text="selectedDay"></h3>
                        <p class="text-white/80 text-sm">
                            Points de vente avec animations
                        </p>
                    </div>

                    <!-- Liste des PDV uniquement -->
                    <div class="p-4 overflow-y-auto max-h-[55vh] space-y-3">
                        <template x-for="(anim, index) in selectedAnimations" :key="index">
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 hover:shadow-md hover:border-soboa-blue/30 transition">
                                <div class="flex items-start gap-3">
                                    <div class="bg-soboa-blue/10 rounded-full p-2 flex-shrink-0">
                                        <svg class="w-5 h-5 text-soboa-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-gray-800 text-lg" x-text="anim.bar_name"></div>
                                        <div class="text-sm text-gray-500 mt-1" x-text="anim.bar_address"></div>
                                        <div class="mt-2 flex items-center gap-2 text-xs text-soboa-blue font-medium">
                                            <span class="bg-soboa-orange/20 text-soboa-orange px-2 py-1 rounded-full font-bold" x-text="anim.time"></span>
                                            <span x-text="anim.match_label"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Message si aucune animation -->
                        <template x-if="selectedAnimations.length === 0">
                            <div class="text-center text-gray-500 py-8">
                                <span class="text-4xl mb-2 block">📅</span>
                                <p>Aucun point de vente avec animation ce jour.</p>
                            </div>
                        </template>
                    </div>

                    <!-- Footer du modal -->
                    <div class="p-4 border-t border-gray-100 bg-gray-50">
                        <button @click="closeDayModal()" class="w-full bg-soboa-blue text-white font-bold py-3 rounded-xl hover:bg-soboa-blue/90 transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Temps Forts des Animations -->
        <div class="max-w-7xl mx-auto px-4 mt-12 pb-12">
            <h2 class="text-3xl font-black text-soboa-blue text-center mb-8">
                🎬 Temps forts des animations
            </h2>

            <!-- Highlights (Photos) Carousel -->
            @if(isset($highlights) && $highlights->count() > 0)
            <div class="mb-12">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-2xl">📸</span>
                    <h3 class="text-xl font-bold text-gray-800">Highlights</h3>
                </div>
                
                <div class="swiper highlights-swiper">
                    <div class="swiper-wrapper">
                        @foreach($highlights as $highlight)
                        <div class="swiper-slide">
                            <div class="relative rounded-2xl overflow-hidden shadow-xl aspect-video group">
                                <img src="{{ $highlight->file_url }}" 
                                     alt="{{ $highlight->title }}"
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                                <div class="absolute bottom-0 left-0 right-0 p-4">
                                    <h4 class="text-white font-bold text-lg">{{ $highlight->title }}</h4>
                                    @if($highlight->description)
                                    <p class="text-white/80 text-sm mt-1">{{ $highlight->description }}</p>
                                    @endif
                                    @if($highlight->bar)
                                    <span class="inline-block mt-2 bg-soboa-orange/90 text-black px-3 py-1 rounded-full text-xs font-bold">
                                        📍 {{ $highlight->bar->name }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination mt-4"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                </div>
            </div>
            @else
            <div class="mb-12 bg-gray-100 rounded-2xl p-8 text-center">
                <span class="text-4xl mb-4 block">📸</span>
                <h3 class="text-xl font-bold text-gray-700 mb-2">Highlights</h3>
                <p class="text-gray-500">Les photos des animations seront bientôt disponibles !</p>
            </div>
            @endif

            <!-- Videos Carousel -->
            @if(isset($videos) && $videos->count() > 0)
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-2xl">🎥</span>
                    <h3 class="text-xl font-bold text-gray-800">Vidéos</h3>
                </div>
                
                <div class="swiper videos-swiper">
                    <div class="swiper-wrapper">
                        @foreach($videos as $video)
                        <div class="swiper-slide">
                            <div class="relative rounded-2xl overflow-hidden shadow-xl aspect-video bg-black">
                                @if($video->is_youtube)
                                    <iframe 
                                        src="https://www.youtube.com/embed/{{ $video->youtube_id }}"
                                        class="w-full h-full"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen>
                                    </iframe>
                                @elseif($video->video_url)
                                    <video controls class="w-full h-full object-cover" poster="{{ $video->thumbnail_url }}">
                                        <source src="{{ $video->video_url }}" type="video/mp4">
                                        Votre navigateur ne supporte pas la lecture de vidéos.
                                    </video>
                                @else
                                    <video controls class="w-full h-full object-cover" poster="{{ $video->thumbnail_url }}">
                                        <source src="{{ $video->file_url }}" type="video/mp4">
                                        Votre navigateur ne supporte pas la lecture de vidéos.
                                    </video>
                                @endif
                            </div>
                            <div class="mt-3">
                                <h4 class="font-bold text-gray-800">{{ $video->title }}</h4>
                                @if($video->description)
                                <p class="text-gray-600 text-sm mt-1">{{ $video->description }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination mt-4"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                </div>
            </div>
            @else
            <div class="bg-gray-100 rounded-2xl p-8 text-center">
                <span class="text-4xl mb-4 block">🎥</span>
                <h3 class="text-xl font-bold text-gray-700 mb-2">Vidéos</h3>
                <p class="text-gray-500">Les vidéos des animations seront bientôt disponibles !</p>
            </div>
            @endif
        </div>

    </div>

    <!-- Swiper JS for Carousels -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Highlights Swiper
            new Swiper('.highlights-swiper', {
                slidesPerView: 1,
                spaceBetween: 20,
                loop: true,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.highlights-swiper .swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.highlights-swiper .swiper-button-next',
                    prevEl: '.highlights-swiper .swiper-button-prev',
                },
                breakpoints: {
                    640: { slidesPerView: 2 },
                    1024: { slidesPerView: 3 },
                }
            });

            // Videos Swiper
            new Swiper('.videos-swiper', {
                slidesPerView: 1,
                spaceBetween: 20,
                loop: true,
                pagination: {
                    el: '.videos-swiper .swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.videos-swiper .swiper-button-next',
                    prevEl: '.videos-swiper .swiper-button-prev',
                },
                breakpoints: {
                    640: { slidesPerView: 2 },
                    1024: { slidesPerView: 2 },
                }
            });
        });
    </script>

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

            // Function to create custom marker icon based on PDV type
            function getVenueIcon(type) {
                const iconConfig = {
                    'dakar': { emoji: '🏙️', color: '#3b82f6' },           // Blue
                    'regions': { emoji: '🗺️', color: '#22c55e' },        // Green
                    'chr': { emoji: '🍽️', color: '#f97316' },            // Orange
                    'fanzone': { emoji: '🎉', color: '#a855f7' },         // Purple
                    'fanzone_public': { emoji: '🎪', color: '#eab308' },  // Yellow
                    'fanzone_hotel': { emoji: '🏨', color: '#ec4899' }    // Pink
                };

                const config = iconConfig[type] || iconConfig['dakar'];

                return L.divIcon({
                    html: `
                        <div style="background: ${config.color};
                                    width: 40px;
                                    height: 40px;
                                    border-radius: 50% 50% 50% 0;
                                    transform: rotate(-45deg);
                                    border: 3px solid white;
                                    box-shadow: 0 4px 6px rgba(0,0,0,0.3);
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;">
                            <span style="transform: rotate(45deg); font-size: 20px;">${config.emoji}</span>
                        </div>
                    `,
                    className: 'custom-venue-marker',
                    iconSize: [40, 40],
                    iconAnchor: [20, 40],
                    popupAnchor: [0, -40]
                });
            }

            // Add venue markers
            @foreach($venues as $venue)
                @php
                    $typeBadges = [
                        'dakar' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'icon' => '🏙️', 'label' => 'Dakar'],
                        'regions' => ['bg' => '#dcfce7', 'text' => '#166534', 'icon' => '🗺️', 'label' => 'Régions'],
                        'chr' => ['bg' => '#ffedd5', 'text' => '#9a3412', 'icon' => '🍽️', 'label' => 'CHR'],
                        'fanzone' => ['bg' => '#f3e8ff', 'text' => '#6b21a8', 'icon' => '🎉', 'label' => 'Fanzone'],
                        'fanzone_public' => ['bg' => '#fef9c3', 'text' => '#a16207', 'icon' => '🎪', 'label' => 'Fanzone tout public'],
                        'fanzone_hotel' => ['bg' => '#fce7f3', 'text' => '#be185d', 'icon' => '🏨', 'label' => 'Fanzone hôtel'],
                    ];
                    $badge = $typeBadges[$venue->type_pdv ?? 'dakar'] ?? $typeBadges['dakar'];
                @endphp
                L.marker([{{ $venue->latitude }}, {{ $venue->longitude }}], {
                    icon: getVenueIcon('{{ $venue->type_pdv ?? "dakar" }}')
                })
                    .addTo(map)
                    .bindPopup(`
                        <div style="min-width: 200px;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                <span style="font-size: 20px;">{{ $badge['icon'] }}</span>
                                <strong style="font-size: 16px; color: #003399;">{{ $venue->name }}</strong>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <span style="display: inline-block; background-color: {{ $badge['bg'] }}; 
                                       color: {{ $badge['text'] }}; 
                                       padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold;">
                                    {{ $badge['label'] }}
                                </span>
                            </div>
                            @if($venue->zone)
                                <p style="color: #666; font-size: 13px; margin: 4px 0;">📍 {{ $venue->zone }}</p>
                            @endif
                            <p style="color: #666; font-size: 13px; margin: 4px 0;">{{ $venue->address }}</p>
                        </div>
                    `);
            @endforeach
        });
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Style for custom venue markers */
        .custom-venue-marker {
            background: transparent !important;
            border: none !important;
        }

        .custom-venue-marker div {
            transition: transform 0.2s ease;
        }

        .custom-venue-marker:hover div {
            transform: rotate(-45deg) scale(1.1);
        }

        /* Old style for Gazelle logo markers (kept for compatibility) */
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