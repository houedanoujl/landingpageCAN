<x-layouts.app title="Admin - Nouveau Point de Vente">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-4xl mx-auto px-4">

            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('admin.bars') }}" class="text-soboa-orange hover:underline font-bold mb-2 inline-block">
                    ← Retour aux points de vente
                </a>
                <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                    <span class="text-4xl"></span> Nouveau Point de Vente
                </h1>
            </div>

            @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Formulaire -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-lg p-6 sticky top-8">
                        <form action="{{ route('admin.store-bar') }}" method="POST" id="barForm">
                            @csrf

                            <div class="space-y-6">
                                <div>
                                    <label class="block text-gray-700 font-bold mb-2">Nom du point de vente *</label>
                                    <input type="text" name="name" value="{{ old('name') }}" required
                                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue"
                                           placeholder="Ex: Maquis Chez Tantie">
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2">Type de PDV *</label>
                                    <select name="type_pdv" required
                                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                                        <option value="dakar" {{ old('type_pdv', 'dakar') === 'dakar' ? 'selected' : '' }}>
                                            Points de vente Dakar
                                        </option>
                                        <option value="regions" {{ old('type_pdv') === 'regions' ? 'selected' : '' }}>
                                            Points de vente Régions
                                        </option>
                                        <option value="chr" {{ old('type_pdv') === 'chr' ? 'selected' : '' }}>
                                            Cafés-Hôtel-Restaurants (CHR)
                                        </option>
                                        <option value="fanzone" {{ old('type_pdv') === 'fanzone' ? 'selected' : '' }}>
                                            Fanzones
                                        </option>
                                        <option value="fanzone_public" {{ old('type_pdv') === 'fanzone_public' ? 'selected' : '' }}>
                                            Fanzone tout public
                                        </option>
                                        <option value="fanzone_hotel" {{ old('type_pdv') === 'fanzone_hotel' ? 'selected' : '' }}>
                                            Fanzone hôtel
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2">Zone géographique</label>
                                    <input type="text" name="zone" value="{{ old('zone') }}"
                                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue"
                                           placeholder="Ex: Plateau, Almadies, Thiès...">
                                    <p class="text-gray-500 text-xs mt-2">Quartier, ville ou région spécifique</p>
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2">Adresse complète *</label>
                                    <div class="flex gap-2">
                                        <input type="text" name="address" value="{{ old('address') }}" required
                                               class="flex-1 border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue"
                                               placeholder="Ex: Cocody, Rue des Jardins, Abidjan">
                                        <button type="button" onclick="searchAddress()" class="bg-soboa-blue hover:bg-soboa-blue/90 text-white font-bold py-3 px-4 rounded-lg transition">
                                            
                                        </button>
                                    </div>
                                    <p class="text-gray-500 text-xs mt-2">Cliquez sur pour localiser automatiquement ou cliquez directement sur la carte</p>
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2">Latitude *</label>
                                    <input type="number" id="latitude" name="latitude" value="{{ old('latitude') }}" required
                                           step="0.00000001" min="-90" max="90"
                                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue"
                                           placeholder="Cliquez sur la carte">
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2">Longitude *</label>
                                    <input type="number" id="longitude" name="longitude" value="{{ old('longitude') }}" required
                                           step="0.00000001" min="-180" max="180"
                                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue"
                                           placeholder="Cliquez sur la carte">
                                </div>

                                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                    <p class="text-green-700 text-sm">
                                        <strong>Cliquez sur la carte</strong> pour sélectionner les coordonnées GPS automatiquement!
                                    </p>
                                </div>

                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="is_active" id="is_active" value="1" checked
                                           class="w-5 h-5 text-soboa-blue border-gray-300 rounded focus:ring-soboa-blue">
                                    <label for="is_active" class="text-gray-700 font-medium">Point de vente actif</label>
                                </div>

                                <div class="flex justify-end gap-4 pt-4 border-t">
                                    <a href="{{ route('admin.bars') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-lg transition">
                                        Annuler
                                    </a>
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition">
                                        Créer
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Carte -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden" style="height: 600px;">
                        <div id="map" style="height: 100%; width: 100%;"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Initialiser la carte Leaflet
        const map = L.map('map').setView([14.6928, -17.0469], 12); // Dakar, Sénégal par défaut

        // Ajouter la couche OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        let marker = null;

        // Récupérer les coordonnées existantes si disponibles
        const existingLat = document.getElementById('latitude').value;
        const existingLng = document.getElementById('longitude').value;

        if (existingLat && existingLng) {
            const lat = parseFloat(existingLat);
            const lng = parseFloat(existingLng);
            map.setView([lat, lng], 13);
            marker = L.marker([lat, lng]).addTo(map).bindPopup('Sélectionné');
        }

        // Ajouter un marqueur au clic
        map.on('click', function(e) {
            const lat = e.latlng.lat.toFixed(8);
            const lng = e.latlng.lng.toFixed(8);

            // Mettre à jour les champs
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            // Mettre à jour le marqueur
            if (marker) {
                map.removeLayer(marker);
            }
            marker = L.marker([lat, lng]).addTo(map).bindPopup(`<strong>Coordonnées:</strong><br/>Lat: ${lat}<br/>Lng: ${lng}`).openPopup();
        });

        // Fonction de recherche par adresse (utiliser Nominatim d'OpenStreetMap)
        async function searchAddress() {
            const address = document.querySelector('input[name="address"]').value;
            if (!address) {
                alert('Veuillez entrer une adresse');
                return;
            }

            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`, {
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) {
                    alert('Erreur de connexion à Nominatim. Cliquez sur la carte pour définir manuellement les coordonnées.');
                    return;
                }

                const results = await response.json();

                if (results.length > 0) {
                    const result = results[0];
                    const lat = parseFloat(result.lat).toFixed(8);
                    const lng = parseFloat(result.lon).toFixed(8);

                    // Mettre à jour les champs
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;

                    // Centrer la carte et ajouter le marqueur
                    map.setView([lat, lng], 13);
                    if (marker) {
                        map.removeLayer(marker);
                    }
                    marker = L.marker([lat, lng]).addTo(map).bindPopup(`<strong>${address}</strong><br/>Lat: ${lat}<br/>Lng: ${lng}`).openPopup();
                } else {
                    alert('Adresse non trouvée. Cliquez directement sur la carte pour sélectionner manuellement.');
                }
            } catch (error) {
                console.error('Erreur de recherche:', error);
                alert('Impossible de localiser l\'adresse. Cliquez directement sur la carte pour sélectionner manuellement.');
            }
        }
    </script>
</x-layouts.app>
