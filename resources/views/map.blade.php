<x-layouts.app title="Points de Vente">
    
    <div class="min-h-screen bg-gray-50" x-data="{
        userLocation: null,
        locationError: null,
        isChecking: false,
        checkInResult: null,
        
        async getLocation() {
            this.isChecking = true;
            this.locationError = null;
            this.checkInResult = null;
            
            if (!navigator.geolocation) {
                this.locationError = 'La géolocalisation n\'est pas supportée par votre navigateur.';
                this.isChecking = false;
                return;
            }
            
            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    this.userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    
                    // Check-in API call
                    try {
                        const response = await fetch('/api/check-in', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                latitude: this.userLocation.lat,
                                longitude: this.userLocation.lng
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (response.ok) {
                            this.checkInResult = { success: true, message: data.message };
                        } else {
                            this.checkInResult = { success: false, message: data.message || 'Aucun lieu partenaire à proximité.' };
                        }
                    } catch (error) {
                        this.checkInResult = { success: false, message: 'Erreur de connexion. Réessayez.' };
                    }
                    
                    this.isChecking = false;
                },
                (error) => {
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            this.locationError = 'Vous avez refusé l\'accès à votre position.';
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
        
        <!-- Header -->
        <div class="bg-soboa-blue py-12 px-4">
            <div class="max-w-7xl mx-auto text-center">
                <span class="text-soboa-orange font-bold text-sm uppercase tracking-widest">Gagnez +4 points</span>
                <h1 class="text-3xl md:text-5xl font-black text-white mt-2">Points de Vente Partenaires</h1>
                <p class="text-white/70 mt-4 max-w-2xl mx-auto">
                    Visitez nos lieux partenaires pendant la CAN et gagnez 4 points bonus par jour!
                </p>
            </div>
        </div>
        
        <!-- Check-in Section -->
        <div class="max-w-7xl mx-auto px-4 -mt-8">
            <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8 border border-gray-100">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-soboa-orange/10 rounded-full flex items-center justify-center">
                            <span class="text-3xl">📍</span>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-soboa-blue">Vous êtes dans un lieu partenaire?</h2>
                            <p class="text-gray-600 text-sm">Activez votre position pour gagner vos points bonus!</p>
                        </div>
                    </div>
                    
                    @if(session('user_id'))
                    <button @click="getLocation()" 
                            :disabled="isChecking"
                            class="w-full md:w-auto bg-soboa-orange hover:bg-soboa-orange-dark disabled:bg-gray-400 text-white font-bold py-4 px-8 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                        <svg x-show="isChecking" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isChecking ? 'Vérification...' : 'Je suis ici !'"></span>
                    </button>
                    @else
                    <a href="/login" class="w-full md:w-auto bg-soboa-blue hover:bg-soboa-blue-dark text-white font-bold py-4 px-8 rounded-xl shadow-lg transition-all text-center">
                        Se connecter pour valider
                    </a>
                    @endif
                </div>
                
                <!-- Location Error -->
                <div x-show="locationError" x-cloak class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <span x-text="locationError"></span>
                </div>
                
                <!-- Check-in Result -->
                <div x-show="checkInResult" x-cloak class="mt-4">
                    <div x-show="checkInResult?.success" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
                        <span class="text-2xl">🎉</span>
                        <span x-text="checkInResult?.message"></span>
                        <span class="font-bold">+4 points!</span>
                    </div>
                    <div x-show="!checkInResult?.success" class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg flex items-center gap-2">
                        <span class="text-2xl">📍</span>
                        <span x-text="checkInResult?.message"></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Map Section -->
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-xl font-bold text-soboa-blue flex items-center gap-2">
                        <span>🗺️</span> Carte des Points de Vente
                    </h3>
                </div>
                
                <!-- Leaflet Map -->
                <div id="map" class="h-[500px] w-full bg-gray-100"></div>
            </div>
        </div>
        
        <!-- Venues List -->
        <div class="max-w-7xl mx-auto px-4 pb-16">
            <h3 class="text-2xl font-bold text-soboa-blue mb-6">Nos Lieux Partenaires</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($venues as $venue)
                <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 hover:border-soboa-orange/30 transition-colors">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-soboa-orange/10 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-xl">🍺</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-soboa-blue text-lg">{{ $venue->name }}</h4>
                            <p class="text-gray-500 text-sm">{{ $venue->address }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-3 text-center py-10 bg-white rounded-xl">
                    <p class="text-gray-500">Aucun lieu partenaire pour le moment.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize map centered on Côte d'Ivoire
            const map = L.map('map').setView([5.3484, -4.0167], 12);
            
            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            // Custom marker icon
            const venueIcon = L.divIcon({
                className: 'custom-marker',
                html: '<div style="background: #E96611; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">🍺</div>',
                iconSize: [30, 30],
                iconAnchor: [15, 15]
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
        [x-cloak] { display: none !important; }
    </style>
</x-layouts.app>
