<x-layouts.app title="Points de Vente">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-soboa-blue">Points de Vente</h1>
            <span class="text-sm text-gray-500">{{ $venues->count() }} points de vente au Sénégal</span>
        </div>

        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg" role="alert">
            <span class="font-medium">{{ session('error') }}</span>
        </div>
        @endif

        <!-- Bannière d'information -->
        <div class="bg-gradient-to-r from-soboa-blue to-soboa-blue/80 rounded-xl p-6 text-white">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold mb-2">Comment ça marche ?</h2>
                    <ol class="list-decimal list-inside space-y-2 text-white/90">
                        <li class="flex flex-col sm:flex-row sm:items-center gap-2">
                            <span>Activez votre GPS pour voir les points de vente les plus proches</span>
                            <button id="activate-gps-btn"
                                    onclick="document.getElementById('locate-me-btn').click()"
                                    class="bg-white/20 hover:bg-white/30 text-white font-bold py-1.5 px-3 rounded-lg text-sm transition flex items-center gap-1 w-fit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Activer GPS
                            </button>
                            <span id="gps-active-badge" class="hidden bg-green-500/20 text-white text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1 w-fit">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                GPS activé
                            </span>
                        </li>
                        <li>Rendez-vous dans un point de vente partenaire</li>
                        <li>Vérifiez votre position (rayon de 200m)</li>
                        <li>Faites vos pronostics sur les matchs !</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Bouton de localisation -->
        <div class="flex justify-center">
            <button id="locate-me-btn" type="button" 
                    class="bg-soboa-orange hover:bg-orange-600 text-black font-bold py-3 px-6 rounded-xl shadow-lg transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span id="locate-btn-text">📍 Trouver les plus proches de moi</span>
            </button>
        </div>

        <!-- Statut de géolocalisation -->
        <div id="geolocation-status" class="hidden">
            <div id="geolocation-loading" class="bg-blue-50 border border-blue-200 rounded-xl p-4 hidden">
                <div class="flex items-center gap-3">
                    <div class="animate-spin rounded-full h-6 w-6 border-2 border-soboa-blue border-t-transparent"></div>
                    <span class="text-soboa-blue font-medium">Récupération de votre position...</span>
                </div>
            </div>
            <div id="geolocation-success" class="bg-green-50 border border-green-200 rounded-xl p-4 hidden">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span id="success-message" class="text-green-700 font-medium">Position détectée ! Points de vente triés par distance.</span>
                </div>
            </div>
            <div id="geolocation-error" class="bg-red-50 border border-red-200 rounded-xl p-4 hidden">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span id="error-message" class="text-red-700 font-medium">Erreur de géolocalisation</span>
                </div>
            </div>
        </div>

        <!-- Suggestion des plus proches -->
        <div id="nearest-venues" class="hidden">
            <h3 class="text-lg font-bold text-soboa-blue mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Points de vente les plus proches
            </h3>
            <div id="nearest-list" class="grid grid-cols-1 md:grid-cols-3 gap-3"></div>
        </div>

        <!-- Liste de tous les points de vente -->
        <div>
            <h3 class="text-lg font-bold text-gray-700 mb-4">Tous les points de vente</h3>
            <div id="venues-container" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
                @forelse($venues as $venue)
                <div class="venue-card bg-white rounded-xl shadow-md overflow-hidden border-2 border-transparent hover:border-soboa-orange hover:shadow-xl transition-all duration-300 flex flex-col"
                     data-venue-id="{{ $venue->id }}"
                     data-venue-lat="{{ $venue->latitude }}"
                     data-venue-lng="{{ $venue->longitude }}"
                     data-venue-name="{{ $venue->name }}"
                     data-venue-address="{{ $venue->address }}">
                    
                    <!-- Header de la carte avec icône -->
                    <div class="bg-gradient-to-r from-soboa-blue/10 to-soboa-orange/10 p-4 border-b">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-soboa-blue rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div class="venue-status flex-shrink-0">
                                <span class="status-badge px-2 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded-full">
                                    Non vérifié
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Corps de la carte -->
                    <div class="p-4 flex-1 flex flex-col">
                        <h3 class="text-lg font-bold text-gray-800 mb-2 line-clamp-2">{{ $venue->name }}</h3>
                        <p class="text-gray-500 text-sm flex items-start gap-1 mb-3 line-clamp-2 flex-1">
                            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            </svg>
                            <span>{{ $venue->address }}</span>
                        </p>
                        
                        <div class="venue-distance text-sm text-gray-500 mb-3 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            <span class="distance-text">Distance inconnue</span>
                        </div>
                        
                        <button type="button" 
                                class="check-location-btn w-full bg-soboa-blue hover:bg-soboa-blue/90 text-white font-semibold py-2 px-3 rounded-lg transition flex items-center justify-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            </svg>
                            Vérifier position
                        </button>

                        <!-- Bouton pour accéder aux matchs (caché par défaut) -->
                        <div class="venue-access mt-3 hidden">
                            <a href="/matches?venue={{ $venue->id }}"
                               class="block w-full bg-gradient-to-r from-soboa-orange to-orange-500 hover:from-orange-500 hover:to-soboa-orange text-white font-bold py-2.5 px-3 rounded-lg shadow-md transition text-center text-sm">
                                <span class="flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Voir les matchs
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full bg-white rounded-xl shadow p-8 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Aucun point de vente</h2>
                    <p class="text-gray-600">Aucun point de vente n'est disponible pour le moment.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusContainer = document.getElementById('geolocation-status');
        const loadingEl = document.getElementById('geolocation-loading');
        const successEl = document.getElementById('geolocation-success');
        const errorEl = document.getElementById('geolocation-error');
        const errorMsgEl = document.getElementById('error-message');
        const locateMeBtn = document.getElementById('locate-me-btn');
        const locateBtnText = document.getElementById('locate-btn-text');
        const nearestVenuesSection = document.getElementById('nearest-venues');
        const nearestList = document.getElementById('nearest-list');
        const venuesContainer = document.getElementById('venues-container');
        
        let userLatitude = null;
        let userLongitude = null;

        // Rayon de geofencing en mètres (défini dans les paramètres admin)
        const GEOFENCING_RADIUS = {{ $geofencingRadius ?? 200 }};

        // Calculer la distance entre deux points (formule de Haversine)
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000; // Rayon de la Terre en mètres
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        // Formater la distance pour l'affichage
        function formatDistance(meters) {
            if (meters < 1000) {
                return Math.round(meters) + ' m';
            }
            return (meters / 1000).toFixed(1) + ' km';
        }

        // Afficher le statut
        function showStatus(type, message = '') {
            statusContainer.classList.remove('hidden');
            loadingEl.classList.add('hidden');
            errorEl.classList.add('hidden');
            
            if (type === 'loading') {
                loadingEl.classList.remove('hidden');
            } else if (type === 'error') {
                errorEl.classList.remove('hidden');
                if (message) errorMsgEl.textContent = message;
            } else if (type === 'hide') {
                statusContainer.classList.add('hidden');
            }
        }

        // Mettre à jour l'état d'une carte de point de vente
        function updateVenueCard(card, distance) {
            const statusEl = card.querySelector('.venue-status');
            const distanceEl = card.querySelector('.distance-text');
            const accessEl = card.querySelector('.venue-access');
            const checkBtn = card.querySelector('.check-location-btn');

            distanceEl.textContent = formatDistance(distance);

            if (distance <= GEOFENCING_RADIUS) {
                // L'utilisateur est dans la zone
                statusEl.innerHTML = `
                    <span class="px-3 py-1 bg-green-100 text-green-700 text-sm font-bold rounded-full flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Dans la zone
                    </span>
                `;
                card.classList.add('border-green-500', 'bg-green-50/30');
                card.classList.remove('border-transparent');
                accessEl.classList.remove('hidden');
                checkBtn.classList.add('hidden');

                // Sauvegarder dans la session
                const venueId = card.dataset.venueId;
                fetch('/api/venue/select', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        venue_id: venueId,
                        latitude: userLatitude,
                        longitude: userLongitude
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Venue selected successfully:', data.message);
                    } else {
                        console.warn('Failed to select venue:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error selecting venue:', error);
                });
            } else {
                // L'utilisateur est trop loin
                statusEl.innerHTML = `
                    <span class="px-3 py-1 bg-red-100 text-red-700 text-sm font-medium rounded-full">
                        Trop loin (${formatDistance(distance)})
                    </span>
                `;
                card.classList.remove('border-green-500', 'bg-green-50/30');
                accessEl.classList.add('hidden');
            }
        }

        // Vérifier la position pour un point de vente spécifique
        function checkVenueLocation(card) {
            const venueLat = parseFloat(card.dataset.venueLat);
            const venueLng = parseFloat(card.dataset.venueLng);

            if (!navigator.geolocation) {
                showStatus('error', 'La géolocalisation n\'est pas supportée par votre navigateur.');
                return;
            }

            showStatus('loading');

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    userLatitude = position.coords.latitude;
                    userLongitude = position.coords.longitude;

                    const distance = calculateDistance(userLatitude, userLongitude, venueLat, venueLng);
                    updateVenueCard(card, distance);
                    showStatus('hide');
                },
                function(error) {
                    handleGeolocationError(error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0
                }
            );
        }

        // Gérer les erreurs de géolocalisation
        function handleGeolocationError(error) {
            let message = 'Impossible de récupérer votre position.';
            let instructions = '';

            switch (error.code) {
                case error.PERMISSION_DENIED:
                    message = '❌ Accès à la géolocalisation refusé';
                    instructions = 'Pour utiliser cette fonctionnalité, veuillez autoriser l\'accès à votre position dans les paramètres de votre navigateur.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    message = '📍 Position non disponible';
                    instructions = 'Vérifiez que le GPS est activé sur votre appareil.';
                    break;
                case error.TIMEOUT:
                    message = '⏱️ Délai d\'attente dépassé';
                    instructions = 'La récupération de votre position prend trop de temps. Réessayez.';
                    break;
            }

            showStatus('error', message + (instructions ? '\n' + instructions : ''));

            // Afficher une alerte détaillée pour l'erreur de permission
            if (error.code === error.PERMISSION_DENIED) {
                showPermissionInstructions();
            }
        }

        // Afficher les instructions pour autoriser la géolocalisation
        function showPermissionInstructions() {
            const instructionsDiv = document.createElement('div');
            instructionsDiv.className = 'mt-4 bg-yellow-50 border border-yellow-300 rounded-xl p-4';
            instructionsDiv.innerHTML = `
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="flex-1">
                        <h4 class="font-bold text-yellow-800 mb-2">Comment activer la géolocalisation ?</h4>
                        <ul class="list-disc list-inside space-y-1 text-sm text-yellow-700">
                            <li>Cliquez sur l'icône de cadenas 🔒 dans la barre d'adresse</li>
                            <li>Recherchez "Localisation" ou "Position"</li>
                            <li>Sélectionnez "Autoriser"</li>
                            <li>Rechargez la page</li>
                        </ul>
                    </div>
                </div>
            `;

            const errorEl = document.getElementById('geolocation-error');
            if (errorEl && !document.getElementById('permission-instructions')) {
                instructionsDiv.id = 'permission-instructions';
                errorEl.after(instructionsDiv);
            }
        }

        // Calculer et afficher les distances pour tous les points de vente
        function updateAllVenuesDistances() {
            if (!userLatitude || !userLongitude) return;

            const cards = document.querySelectorAll('.venue-card');
            const venuesWithDistance = [];

            cards.forEach(card => {
                const venueLat = parseFloat(card.dataset.venueLat);
                const venueLng = parseFloat(card.dataset.venueLng);
                const distance = calculateDistance(userLatitude, userLongitude, venueLat, venueLng);
                
                card.dataset.distance = distance;
                updateVenueCard(card, distance);
                
                venuesWithDistance.push({
                    card: card,
                    distance: distance,
                    name: card.querySelector('h3').textContent,
                    id: card.dataset.venueId
                });
            });

            // Trier par distance
            venuesWithDistance.sort((a, b) => a.distance - b.distance);

            // Réorganiser les cartes dans le DOM
            venuesWithDistance.forEach(venue => {
                venuesContainer.appendChild(venue.card);
            });

            // Afficher les venues les plus proches
            displayNearestVenues(venuesWithDistance);
        }

        // Afficher les points de vente les plus proches
        function displayNearestVenues(sortedVenues) {
            nearestList.innerHTML = '';
            
            // Afficher les 3 plus proches
            const nearest = sortedVenues.slice(0, 3);
            
            if (nearest.length === 0) return;

            nearest.forEach((venue, index) => {
                const isAccessible = venue.distance <= GEOFENCING_RADIUS;
                const bgClass = isAccessible ? 'bg-green-100 border-green-300' : 'bg-gray-100 border-gray-200';
                const textClass = isAccessible ? 'text-green-700' : 'text-gray-600';
                const icon = isAccessible 
                    ? '<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>'
                    : '<svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>';
                
                const item = document.createElement('div');
                item.className = `flex items-center justify-between p-3 ${bgClass} rounded-lg border cursor-pointer hover:shadow-md transition-all`;
                item.innerHTML = `
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 bg-white rounded-full text-sm font-bold ${textClass}">${index + 1}</span>
                        <div>
                            <p class="font-semibold text-gray-800">${venue.name}</p>
                            <p class="text-sm ${textClass}">${formatDistance(venue.distance)}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        ${isAccessible ? '<span class="text-xs bg-green-500 text-white px-2 py-1 rounded-full">Accessible</span>' : ''}
                        ${icon}
                    </div>
                `;
                
                // Clic pour scroller vers la carte
                item.addEventListener('click', () => {
                    venue.card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    venue.card.classList.add('ring-4', 'ring-green-400');
                    setTimeout(() => venue.card.classList.remove('ring-4', 'ring-green-400'), 2000);
                });
                
                nearestList.appendChild(item);
            });

            nearestVenuesSection.classList.remove('hidden');
        }

        // Vérifier les permissions de géolocalisation
        async function checkGeolocationPermission() {
            if (!navigator.permissions) {
                return 'unknown'; // L'API Permissions n'est pas supportée
            }

            try {
                const result = await navigator.permissions.query({ name: 'geolocation' });
                return result.state; // 'granted', 'denied', or 'prompt'
            } catch (error) {
                console.log('Permissions API non disponible:', error);
                return 'unknown';
            }
        }

        // Fonction pour localiser l'utilisateur
        async function locateUser() {
            if (!navigator.geolocation) {
                showStatus('error', 'La géolocalisation n\'est pas supportée par votre navigateur.');
                return;
            }

            // Vérifier les permissions avant de demander la position
            const permissionState = await checkGeolocationPermission();

            if (permissionState === 'denied') {
                showStatus('error', '❌ Accès à la géolocalisation refusé');
                showPermissionInstructions();
                return;
            }

            showStatus('loading');
            locateMeBtn.disabled = true;
            locateBtnText.textContent = 'Localisation...';

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    userLatitude = position.coords.latitude;
                    userLongitude = position.coords.longitude;

                    showStatus('hide');
                    locateMeBtn.disabled = false;
                    locateBtnText.textContent = 'Actualiser ma position';
                    locateMeBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                    locateMeBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');

                    // Masquer le bouton "Activer GPS" et afficher le badge
                    const activateGpsBtn = document.getElementById('activate-gps-btn');
                    const gpsActiveBadge = document.getElementById('gps-active-badge');
                    if (activateGpsBtn) activateGpsBtn.classList.add('hidden');
                    if (gpsActiveBadge) gpsActiveBadge.classList.remove('hidden');

                    updateAllVenuesDistances();
                },
                function(error) {
                    handleGeolocationError(error);
                    locateMeBtn.disabled = false;
                    locateBtnText.textContent = 'Me localiser';

                    // Afficher le bouton "Activer GPS" et masquer le badge en cas d'erreur
                    const activateGpsBtn = document.getElementById('activate-gps-btn');
                    const gpsActiveBadge = document.getElementById('gps-active-badge');
                    if (activateGpsBtn) activateGpsBtn.classList.remove('hidden');
                    if (gpsActiveBadge) gpsActiveBadge.classList.add('hidden');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0
                }
            );
        }

        // Événement sur le bouton "Me localiser"
        locateMeBtn.addEventListener('click', locateUser);

        // Ajouter les événements de clic sur les boutons
        document.querySelectorAll('.check-location-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const card = this.closest('.venue-card');
                checkVenueLocation(card);
            });
        });

        // Vérifier les permissions avant de lancer automatiquement la localisation
        async function autoLocateIfAllowed() {
            const permissionState = await checkGeolocationPermission();

            // Ne pas demander automatiquement si déjà refusé
            if (permissionState === 'denied') {
                console.log('Géolocalisation refusée. Activation manuelle requise.');
                return;
            }

            // Lancer la localisation si autorisé ou si le navigateur va demander
            if (permissionState === 'granted' || permissionState === 'prompt' || permissionState === 'unknown') {
                setTimeout(locateUser, 500);
            }
        }

        // Lancer la vérification au chargement
        autoLocateIfAllowed();
    });
    </script>
</x-layouts.app>
