<x-layouts.app title="Check-ins - Admin">
    <div class="min-h-screen bg-gray-100">
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white py-6">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold">📍 Check-ins</h1>
                        <p class="text-purple-200 mt-1">Visites des lieux partenaires avec coordonnées GPS</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.export-checkins', request()->query()) }}" 
                           class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Exporter CSV
                        </a>
                        <a href="{{ route('admin.dashboard') }}" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-6">
            <!-- Statistiques globales -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-4 text-center">
                    <div class="text-3xl font-black text-purple-600">{{ number_format($stats['total_checkins']) }}</div>
                    <div class="text-sm text-gray-500">Total check-ins</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 text-center">
                    <div class="text-3xl font-black text-blue-500">{{ $stats['today'] }}</div>
                    <div class="text-sm text-gray-500">Aujourd'hui</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 text-center">
                    <div class="text-3xl font-black text-green-500">{{ $stats['this_week'] }}</div>
                    <div class="text-sm text-gray-500">Cette semaine</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 text-center">
                    <div class="text-3xl font-black text-yellow-500">{{ $stats['unique_users'] }}</div>
                    <div class="text-sm text-gray-500">Utilisateurs</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 text-center">
                    <div class="text-3xl font-black text-orange-500">{{ $stats['unique_venues'] }}</div>
                    <div class="text-sm text-gray-500">Lieux visités</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 text-center">
                    <div class="text-3xl font-black text-red-500">{{ number_format($stats['total_points']) }}</div>
                    <div class="text-sm text-gray-500">Points distribués</div>
                </div>
            </div>

            <!-- Top Lieux & Top Utilisateurs -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Top 5 Lieux -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-4">
                        <h3 class="text-white font-bold flex items-center gap-2">
                            <span>🏆</span> Top 5 Lieux les plus visités
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($topVenues as $index => $venue)
                            <div class="p-4 flex items-center justify-between hover:bg-gray-50">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-full {{ $index === 0 ? 'bg-yellow-400' : ($index === 1 ? 'bg-gray-300' : ($index === 2 ? 'bg-orange-400' : 'bg-gray-200')) }} flex items-center justify-center font-bold text-sm">
                                        {{ $index + 1 }}
                                    </span>
                                    <div>
                                        <div class="font-medium">{{ $venue->bar->name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ $venue->bar->zone ?? '' }}</div>
                                    </div>
                                </div>
                                <span class="font-bold text-purple-600">{{ $venue->visit_count }} visites</span>
                            </div>
                        @empty
                            <div class="p-4 text-center text-gray-500">Aucun check-in enregistré</div>
                        @endforelse
                    </div>
                </div>

                <!-- Top 5 Utilisateurs -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4">
                        <h3 class="text-white font-bold flex items-center gap-2">
                            <span>👤</span> Top 5 Utilisateurs actifs
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse($topUsers as $index => $userStat)
                            <div class="p-4 flex items-center justify-between hover:bg-gray-50">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-full {{ $index === 0 ? 'bg-yellow-400' : ($index === 1 ? 'bg-gray-300' : ($index === 2 ? 'bg-orange-400' : 'bg-gray-200')) }} flex items-center justify-center font-bold text-sm">
                                        {{ $index + 1 }}
                                    </span>
                                    <div>
                                        <div class="font-medium">{{ $userStat->user->name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ $userStat->user->phone ?? '' }}</div>
                                    </div>
                                </div>
                                <span class="font-bold text-blue-600">{{ $userStat->checkin_count }} check-ins</span>
                            </div>
                        @empty
                            <div class="p-4 text-center text-gray-500">Aucun check-in enregistré</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
                <form method="GET" action="{{ route('admin.checkins') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                    <!-- Utilisateur -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Utilisateur</label>
                        <select name="user_id" class="w-full border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 text-sm">
                            <option value="">Tous</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Lieu -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lieu</label>
                        <select name="bar_id" class="w-full border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 text-sm">
                            <option value="">Tous</option>
                            @foreach($bars as $bar)
                                <option value="{{ $bar->id }}" {{ request('bar_id') == $bar->id ? 'selected' : '' }}>
                                    {{ $bar->name }} ({{ $bar->zone }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Zone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Zone</label>
                        <select name="zone" class="w-full border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 text-sm">
                            <option value="">Toutes</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone }}" {{ request('zone') == $zone ? 'selected' : '' }}>
                                    {{ $zone }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Période compétition -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Période</label>
                        <select name="period" class="w-full border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 text-sm">
                            <option value="">Toutes</option>
                            @foreach($availablePeriods as $key => $period)
                                <option value="{{ $key }}" {{ request('period') == $key ? 'selected' : '' }}>
                                    {{ $period['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date de début -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" 
                               class="w-full border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 text-sm">
                    </div>

                    <!-- Date de fin -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" 
                               class="w-full border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 text-sm">
                    </div>

                    <!-- Boutons -->
                    <div class="lg:col-span-6 flex gap-2 justify-end">
                        <a href="{{ route('admin.checkins') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                            Réinitialiser
                        </a>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                            Filtrer
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tableau des check-ins -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-700">Liste des check-ins ({{ $checkins->total() }} résultats)</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date/Heure</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lieu</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Adresse</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Zone</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Coordonnées GPS</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Points</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($checkins as $checkin)
                                @php
                                    $bar = $checkin->bar;
                                    $hasCoords = $bar && $bar->latitude && $bar->longitude;
                                    $mapsUrl = $hasCoords 
                                        ? "https://www.openstreetmap.org/?mlat={$bar->latitude}&mlon={$bar->longitude}&zoom=17" 
                                        : null;
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $checkin->created_at->format('d/m/Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $checkin->created_at->format('H:i:s') }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($checkin->user)
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-sm">
                                                    {{ substr($checkin->user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $checkin->user->name }}</div>
                                                    <div class="text-xs text-gray-500">{{ $checkin->user->phone }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($bar)
                                            <div class="text-sm font-medium text-gray-900">{{ $bar->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $bar->type_pdv ?? 'PDV' }}</div>
                                        @else
                                            <span class="text-gray-400">Lieu supprimé</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm text-gray-600 max-w-xs truncate" title="{{ $bar->address ?? '' }}">
                                            {{ $bar->address ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($bar && $bar->zone)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $bar->zone }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($hasCoords)
                                            <div class="text-xs font-mono bg-gray-100 rounded p-1">
                                                <div class="text-green-600">{{ number_format($bar->latitude, 6) }}</div>
                                                <div class="text-blue-600">{{ number_format($bar->longitude, 6) }}</div>
                                            </div>
                                        @else
                                            <span class="text-gray-400 text-xs">Non défini</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800">
                                            +{{ $checkin->points }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            @if($mapsUrl)
                                                <a href="{{ $mapsUrl }}" target="_blank" 
                                                   class="inline-flex items-center px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 transition text-xs"
                                                   title="Voir sur OpenStreetMap">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    </svg>
                                                    OSM
                                                </a>
                                            @endif
                                            @if($checkin->user)
                                                <a href="{{ route('admin.edit-user', $checkin->user->id) }}" 
                                                   class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition text-xs"
                                                   title="Voir l'utilisateur">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center">
                                        <div class="text-gray-400">
                                            <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <p class="text-lg font-medium">Aucun check-in trouvé</p>
                                            <p class="text-sm mt-1">Les utilisateurs n'ont pas encore effectué de visites ou les filtres sont trop restrictifs.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($checkins->hasPages())
                    <div class="px-4 py-3 border-t bg-gray-50">
                        {{ $checkins->links() }}
                    </div>
                @endif
            </div>

            <!-- Carte des check-ins (si des coordonnées existent) -->
            @php
                $checkinsWithCoords = $checkins->filter(function($c) {
                    return $c->bar && $c->bar->latitude && $c->bar->longitude;
                });
            @endphp
            @if($checkinsWithCoords->count() > 0)
                <div class="mt-6 bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b bg-gray-50">
                        <h3 class="font-bold text-gray-700 flex items-center gap-2">
                            <span>🗺️</span> Carte des check-ins récents
                        </h3>
                    </div>
                    <div class="p-4">
                        <div id="checkins-map" class="w-full h-96 rounded-lg bg-gray-200"></div>
                    </div>
                </div>

                <!-- Script pour la carte Leaflet -->
                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
                @php
                    $mapCheckins = $checkins->filter(function($c) {
                        return $c->bar && $c->bar->latitude && $c->bar->longitude;
                    })->map(function($c) {
                        return [
                            'lat' => $c->bar->latitude,
                            'lng' => $c->bar->longitude,
                            'name' => $c->bar->name,
                            'user' => $c->user ? $c->user->name : 'N/A',
                            'date' => $c->created_at->format('d/m/Y H:i'),
                        ];
                    })->values();
                @endphp
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const map = L.map('checkins-map').setView([14.6937, -17.4441], 12); // Dakar par défaut
                        
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors'
                        }).addTo(map);

                        const checkins = @json($mapCheckins);

                        const bounds = [];
                        checkins.forEach(function(checkin) {
                            const marker = L.marker([checkin.lat, checkin.lng]).addTo(map);
                            marker.bindPopup(
                                '<strong>' + checkin.name + '</strong><br>' +
                                '<small>Visite par: ' + checkin.user + '</small><br>' +
                                '<small>' + checkin.date + '</small>'
                            );
                            bounds.push([checkin.lat, checkin.lng]);
                        });

                        if (bounds.length > 0) {
                            map.fitBounds(bounds, { padding: [50, 50] });
                        }
                    });
                </script>
            @endif
        </div>
    </div>
</x-layouts.app>
