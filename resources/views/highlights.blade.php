<x-layouts.app title="Temps Forts">
    <div class="space-y-6">
        <!-- Header -->
        <div class="relative py-12 px-8 rounded-2xl overflow-hidden mb-8 shadow-2xl">
            <div class="absolute inset-0 z-0">
                <img src="/images/sen.webp" class="w-full h-full object-cover" alt="Background">
                <div class="absolute inset-0 bg-gradient-to-r from-purple-900/80 to-indigo-900/80 backdrop-blur-[1px]"></div>
            </div>
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h1 class="text-4xl font-black text-white drop-shadow-2xl">🎉 Temps Forts</h1>
                    <p class="text-white/80 font-bold uppercase tracking-widest text-xs mt-1 drop-shadow-lg">
                        Filtrez les animations par point de vente
                    </p>
                </div>
                <div class="bg-white/10 backdrop-blur-md border border-white/20 px-4 py-2 rounded-xl shadow-xl">
                    <span class="text-xs text-white/70 font-black uppercase tracking-wider block">Résultats</span>
                    <span class="text-soboa-orange font-black drop-shadow-md">{{ $animations->count() }}</span>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span>🔍</span> Filtrer les animations
            </h2>
            
            <form method="GET" action="{{ route('highlights') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Filtre par PDV -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Point de vente</label>
                        <select name="venue_id" class="w-full border-2 border-gray-300 rounded-lg p-3 focus:border-soboa-orange focus:ring-0">
                            <option value="">Tous les PDV</option>
                            @foreach($venues as $v)
                                <option value="{{ $v->id }}" {{ $venueId == $v->id ? 'selected' : '' }}>
                                    {{ $v->name }} {{ $v->zone ? "({$v->zone})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtre par zone -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Zone</label>
                        <select name="zone" class="w-full border-2 border-gray-300 rounded-lg p-3 focus:border-soboa-orange focus:ring-0">
                            <option value="">Toutes les zones</option>
                            @foreach($zones as $z)
                                <option value="{{ $z }}" {{ $zone == $z ? 'selected' : '' }}>{{ $z }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtre par type -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Type de PDV</label>
                        <select name="type" class="w-full border-2 border-gray-300 rounded-lg p-3 focus:border-soboa-orange focus:ring-0">
                            <option value="">Tous les types</option>
                            @foreach($types as $t)
                                <option value="{{ $t }}" {{ $type == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="bg-soboa-orange hover:bg-orange-600 text-black font-bold py-3 px-6 rounded-xl shadow-lg transition">
                        🔍 Filtrer
                    </button>
                    @if($venueId || $zone || $type)
                        <a href="{{ route('highlights') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-xl transition">
                            ✖️ Réinitialiser
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Bouton localisation -->
        <div class="flex justify-center">
            <button id="locate-btn" type="button"
                class="bg-soboa-blue hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span id="locate-btn-text">📍 Trier par distance</span>
            </button>
        </div>

        <!-- Mention 18+ -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
            <div class="flex items-center justify-center gap-3">
                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-black text-xs">18+</span>
                </div>
                <p class="text-red-700 text-sm font-medium">
                    Ce jeu est réservé aux plus de 18 ans. 
                    <a href="{{ route('terms') }}" class="underline hover:text-red-900">Conditions de participation</a>
                </p>
            </div>
        </div>

        <!-- Résultats -->
        @if($animations->count() > 0)
            <div id="animations-container" class="space-y-8">
                @foreach($animationsByDate as $date => $dayAnimations)
                    @php
                        $carbonDate = \Carbon\Carbon::parse($date);
                        $isToday = $carbonDate->isToday();
                        $isTomorrow = $carbonDate->isTomorrow();
                    @endphp
                    <div class="animation-day" data-date="{{ $date }}">
                        <!-- Date header -->
                        <div class="sticky top-20 z-10 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl px-6 py-4 shadow-lg mb-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-3xl">📅</span>
                                    <div>
                                        <h2 class="text-xl font-black text-white">
                                            @if($isToday)
                                                Aujourd'hui
                                            @elseif($isTomorrow)
                                                Demain
                                            @else
                                                {{ $carbonDate->translatedFormat('l d F Y') }}
                                            @endif
                                        </h2>
                                        <p class="text-white/70 text-sm">{{ $dayAnimations->count() }} animation(s)</p>
                                    </div>
                                </div>
                                @if($isToday)
                                    <span class="bg-soboa-orange text-black font-bold px-3 py-1 rounded-full text-sm animate-pulse">
                                        🔴 EN DIRECT
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Animations -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($dayAnimations as $animation)
                                @php
                                    $bar = $animation->bar;
                                    $match = $animation->match;
                                    $typeColors = [
                                        'dakar' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'border' => 'border-blue-300'],
                                        'regions' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-300'],
                                        'chr' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'border' => 'border-orange-300'],
                                        'fanzone' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'border' => 'border-purple-300'],
                                    ];
                                    $colors = $typeColors[$bar->type_pdv ?? 'dakar'] ?? $typeColors['dakar'];
                                @endphp
                                <div class="animation-card bg-white rounded-xl shadow-md overflow-hidden border-2 border-transparent hover:border-purple-400 hover:shadow-xl transition-all"
                                     data-lat="{{ $bar->latitude }}" 
                                     data-lng="{{ $bar->longitude }}"
                                     data-venue-name="{{ $bar->name }}">
                                    
                                    <!-- Header match -->
                                    <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-4 py-3 text-white">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                @if($match && $match->homeTeam && $match->homeTeam->iso_code)
                                                    <img src="https://flagicons.lipis.dev/flags/4x3/{{ strtolower($match->homeTeam->iso_code) }}.svg"
                                                         alt="{{ $match->homeTeam->name }}" class="w-6 h-4 object-contain rounded">
                                                @endif
                                                <span class="font-bold text-sm">
                                                    {{ $match->homeTeam ? $match->homeTeam->name : $match->team_a }}
                                                </span>
                                            </div>
                                            <span class="text-white/60 text-xs">VS</span>
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-sm">
                                                    {{ $match->awayTeam ? $match->awayTeam->name : $match->team_b }}
                                                </span>
                                                @if($match && $match->awayTeam && $match->awayTeam->iso_code)
                                                    <img src="https://flagicons.lipis.dev/flags/4x3/{{ strtolower($match->awayTeam->iso_code) }}.svg"
                                                         alt="{{ $match->awayTeam->name }}" class="w-6 h-4 object-contain rounded">
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-center text-white/70 text-xs mt-1">
                                            {{ \Carbon\Carbon::parse($match->match_date)->format('H:i') }}
                                        </p>
                                    </div>

                                    <!-- Corps -->
                                    <div class="p-4">
                                        <div class="flex items-center gap-2 mb-3">
                                            <div class="w-10 h-10 {{ $colors['bg'] }} rounded-full flex items-center justify-center">
                                                <span class="text-lg">📍</span>
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-gray-800">{{ $bar->name }}</h3>
                                                @if($bar->zone)
                                                    <p class="text-xs text-gray-500">{{ $bar->zone }}</p>
                                                @endif
                                            </div>
                                        </div>

                                        @if($bar->type_pdv)
                                            <span class="inline-block px-2 py-1 rounded-full text-xs font-medium {{ $colors['bg'] }} {{ $colors['text'] }} {{ $colors['border'] }} border mb-3">
                                                {{ ucfirst($bar->type_pdv) }}
                                            </span>
                                        @endif

                                        <div class="distance-info hidden text-sm text-gray-600 mb-3">
                                            <span class="font-medium">📏 Distance : </span>
                                            <span class="distance-value font-bold text-purple-600">--</span>
                                        </div>

                                        <div class="flex gap-2">
                                            @if($bar->latitude && $bar->longitude)
                                                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $bar->latitude }},{{ $bar->longitude }}"
                                                   target="_blank"
                                                   class="flex-1 bg-purple-600 hover:bg-purple-700 text-white text-center font-bold py-2 px-3 rounded-lg text-sm transition">
                                                    🗺️ Itinéraire
                                                </a>
                                            @endif
                                            <a href="{{ route('matches') }}#match-{{ $match->id }}"
                                               class="flex-1 bg-soboa-orange hover:bg-orange-600 text-black text-center font-bold py-2 px-3 rounded-lg text-sm transition">
                                                🎯 Pronostiquer
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 bg-white rounded-xl shadow">
                <span class="text-6xl">🔍</span>
                <h3 class="text-xl font-bold text-gray-700 mt-4">Aucun résultat</h3>
                <p class="text-gray-500 mt-2">Modifiez vos critères de recherche</p>
                <a href="{{ route('highlights') }}" class="inline-block mt-4 bg-soboa-orange hover:bg-orange-600 text-black font-bold py-2 px-6 rounded-lg transition">
                    Voir toutes les animations
                </a>
            </div>
        @endif

        <!-- Retour au calendrier -->
        <div class="text-center">
            <a href="{{ route('animations') }}" class="inline-flex items-center gap-2 text-soboa-blue hover:text-blue-700 font-bold transition">
                ← Retour au calendrier complet
            </a>
        </div>
    </div>

    <script>
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        function formatDistance(distanceKm) {
            if (distanceKm < 1) {
                return Math.round(distanceKm * 1000) + 'm';
            }
            return distanceKm.toFixed(1) + ' km';
        }

        document.getElementById('locate-btn').addEventListener('click', function() {
            const btn = this;
            const btnText = document.getElementById('locate-btn-text');
            
            btnText.textContent = '⏳ Détection...';
            btn.disabled = true;

            if (!navigator.geolocation) {
                btnText.textContent = '❌ Non supporté';
                btn.disabled = false;
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;

                    const cards = document.querySelectorAll('.animation-card');
                    cards.forEach(card => {
                        const lat = parseFloat(card.dataset.lat);
                        const lng = parseFloat(card.dataset.lng);

                        if (lat && lng) {
                            const distance = calculateDistance(userLat, userLng, lat, lng);
                            const distanceInfo = card.querySelector('.distance-info');
                            const distanceValue = card.querySelector('.distance-value');
                            if (distanceInfo && distanceValue) {
                                distanceInfo.classList.remove('hidden');
                                distanceValue.textContent = formatDistance(distance);
                            }
                        }
                    });

                    btnText.textContent = '✅ Distances affichées';
                    btn.classList.add('bg-green-500');
                    btn.classList.remove('bg-soboa-blue');
                },
                (error) => {
                    btnText.textContent = '❌ Position refusée';
                    btn.disabled = false;
                },
                { enableHighAccuracy: false, timeout: 15000, maximumAge: 60000 }
            );
        });
    </script>
</x-layouts.app>
