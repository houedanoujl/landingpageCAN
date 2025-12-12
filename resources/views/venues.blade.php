<x-layouts.app title="Points de Vente">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-soboa-blue">Points de Vente</h1>
            <span class="text-sm text-gray-500">Sélectionnez votre point de vente</span>
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
                    <ol class="list-decimal list-inside space-y-1 text-white/90">
                        <li>Sélectionnez un point de vente ci-dessous</li>
                        <li>Rendez-vous physiquement à ce point de vente</li>
                        <li>Activez la géolocalisation sur votre téléphone</li>
                        <li>Faites vos pronostics sur les matchs !</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Statut de géolocalisation -->
        <div id="geolocation-status" class="hidden">
            <div id="geolocation-loading" class="bg-blue-50 border border-blue-200 rounded-xl p-4 hidden">
                <div class="flex items-center gap-3">
                    <div class="animate-spin rounded-full h-6 w-6 border-2 border-soboa-blue border-t-transparent"></div>
                    <span class="text-soboa-blue font-medium">Récupération de votre position...</span>
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

        <!-- Liste des points de vente -->
        <div class="grid gap-4">
            @forelse($venues as $venue)
            <div class="venue-card bg-white rounded-xl shadow-lg overflow-hidden border-2 border-transparent hover:border-soboa-orange transition-all duration-300"
                 data-venue-id="{{ $venue->id }}"
                 data-venue-lat="{{ $venue->latitude }}"
                 data-venue-lng="{{ $venue->longitude }}"
                 data-venue-name="{{ $venue->name }}">
                <div class="p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-800 mb-1">{{ $venue->name }}</h3>
                            <p class="text-gray-500 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                </svg>
                                {{ $venue->address }}
                            </p>
                        </div>
                        <div class="venue-status flex-shrink-0">
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 text-sm font-medium rounded-full">
                                Non vérifié
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-4 flex items-center justify-between">
                        <div class="venue-distance text-sm text-gray-500">
                            <span class="distance-text">Distance inconnue</span>
                        </div>
                        <button type="button" 
                                class="check-location-btn bg-soboa-blue hover:bg-soboa-blue/90 text-white font-bold py-2 px-4 rounded-lg transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            </svg>
                            Vérifier ma position
                        </button>
                    </div>

                    <!-- Bouton pour accéder aux matchs (caché par défaut) -->
                    <div class="venue-access mt-4 hidden">
                        <a href="/matches?venue={{ $venue->id }}" 
                           class="block w-full bg-gradient-to-r from-soboa-orange to-orange-500 hover:from-orange-500 hover:to-soboa-orange text-white font-bold py-3 px-4 rounded-lg shadow-lg transition text-center">
                            <span class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Voir les matchs et parier
                            </span>
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl shadow p-8 text-center">
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusContainer = document.getElementById('geolocation-status');
        const loadingEl = document.getElementById('geolocation-loading');
        const errorEl = document.getElementById('geolocation-error');
        const errorMsgEl = document.getElementById('error-message');
        
        let userLatitude = null;
        let userLongitude = null;

        // Rayon de geofencing en mètres
        const GEOFENCING_RADIUS = 200;

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
                    },
                    body: JSON.stringify({
                        venue_id: venueId,
                        latitude: userLatitude,
                        longitude: userLongitude
                    })
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
                    let message = 'Impossible de récupérer votre position.';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            message = 'Vous avez refusé l\'accès à la géolocalisation. Veuillez l\'autoriser dans les paramètres.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            message = 'Position non disponible. Vérifiez que le GPS est activé.';
                            break;
                        case error.TIMEOUT:
                            message = 'Délai d\'attente dépassé. Réessayez.';
                            break;
                    }
                    showStatus('error', message);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0
                }
            );
        }

        // Ajouter les événements de clic sur les boutons
        document.querySelectorAll('.check-location-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const card = this.closest('.venue-card');
                checkVenueLocation(card);
            });
        });
    });
    </script>
</x-layouts.app>
