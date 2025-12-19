<x-layouts.app title="Admin - Points de Vente">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Gestion des Points de Vente</h1>
                <p class="text-gray-600 mt-1">Gérer la segmentation et les zones des PDV partenaires</p>
            </div>
            <a href="{{ route('admin.venues.create') }}" 
               class="bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-3 px-6 rounded-lg shadow-lg transition">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nouveau PDV
            </a>
        </div>

        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Total PDV</p>
                        <p class="text-3xl font-black text-gray-900 mt-2">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-green-600 font-medium">{{ $stats['active'] }} actifs</span>
                    <span class="text-gray-400 mx-2">•</span>
                    <span class="text-red-600 font-medium">{{ $stats['inactive'] }} inactifs</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Dakar</p>
                        <p class="text-3xl font-black text-soboa-blue mt-2">{{ $stats['by_type']['dakar'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-2xl">🏙️</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Régions</p>
                        <p class="text-3xl font-black text-green-600 mt-2">{{ $stats['by_type']['regions'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <span class="text-2xl">🗺️</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">CHR + Fanzones</p>
                        <p class="text-3xl font-black text-orange-600 mt-2">{{ $stats['by_type']['chr'] + $stats['by_type']['fanzone'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                        <span class="text-2xl">🍽️</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres et Recherche -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <form method="GET" action="{{ route('admin.venues.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom du PDV..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-blue focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Type de PDV</label>
                    <select name="type_pdv" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-blue focus:border-transparent">
                        <option value="">Tous les types</option>
                        @foreach($typePdvOptions as $key => $label)
                            <option value="{{ $key }}" {{ request('type_pdv') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Zone</label>
                    <input type="text" name="zone" value="{{ request('zone') }}" placeholder="Ex: Plateau, Thiès..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-blue focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Statut</label>
                    <select name="is_active" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-blue focus:border-transparent">
                        <option value="">Tous</option>
                        <option value="active" {{ request('is_active') === 'active' ? 'selected' : '' }}>Actifs</option>
                        <option value="inactive" {{ request('is_active') === 'inactive' ? 'selected' : '' }}>Inactifs</option>
                    </select>
                </div>

                <div class="md:col-span-4 flex gap-3">
                    <button type="submit" class="bg-soboa-blue hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Filtrer
                    </button>
                    <a href="{{ route('admin.venues.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded-lg transition">
                        Réinitialiser
                    </a>
                </div>
            </form>
        </div>

        <!-- Actions Groupées -->
        <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl shadow p-6 mb-6" id="bulkActionsPanel" style="display: none;">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-bold text-gray-900">
                        <span id="selectedCount">0</span> PDV sélectionné(s)
                    </p>
                    <p class="text-sm text-gray-600">Appliquer une action à tous les éléments sélectionnés</p>
                </div>
                <div class="flex gap-3">
                    <form method="POST" action="{{ route('admin.venues.bulk-update-type') }}" id="bulkTypeForm" class="flex gap-2">
                        @csrf
                        <input type="hidden" name="venue_ids" id="bulkTypeVenueIds">
                        <select name="type_pdv" required class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-blue">
                            <option value="">Changer le type...</option>
                            @foreach($typePdvOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-soboa-blue hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">
                            Appliquer Type
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.venues.bulk-update-zone') }}" id="bulkZoneForm" class="flex gap-2">
                        @csrf
                        <input type="hidden" name="venue_ids" id="bulkZoneVenueIds">
                        <input type="text" name="zone" placeholder="Nouvelle zone..." required class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-blue">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition">
                            Appliquer Zone
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Messages -->
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6">
                <p class="font-bold">Succès!</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <!-- Tableau des PDV -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left">
                                <input type="checkbox" id="selectAll" class="w-5 h-5 rounded border-gray-300 text-soboa-blue focus:ring-soboa-blue">
                            </th>
                            <th class="px-6 py-4 text-left text-sm font-black text-gray-700 uppercase">Nom</th>
                            <th class="px-6 py-4 text-left text-sm font-black text-gray-700 uppercase">Type PDV</th>
                            <th class="px-6 py-4 text-left text-sm font-black text-gray-700 uppercase">Zone</th>
                            <th class="px-6 py-4 text-left text-sm font-black text-gray-700 uppercase">Adresse</th>
                            <th class="px-6 py-4 text-left text-sm font-black text-gray-700 uppercase">Statut</th>
                            <th class="px-6 py-4 text-center text-sm font-black text-gray-700 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($venues as $venue)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <input type="checkbox" name="venue_checkbox" value="{{ $venue->id }}" class="venue-checkbox w-5 h-5 rounded border-gray-300 text-soboa-blue focus:ring-soboa-blue">
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-900">{{ $venue->name }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $typeBadges = [
                                            'dakar' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => '🏙️'],
                                            'regions' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => '🗺️'],
                                            'chr' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'icon' => '🍽️'],
                                            'fanzone' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'icon' => '🎉'],
                                        ];
                                        $badge = $typeBadges[$venue->type_pdv] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => '📍'];
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-bold {{ $badge['bg'] }} {{ $badge['text'] }}">
                                        <span>{{ $badge['icon'] }}</span>
                                        {{ $venue->type_pdv_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-gray-700">{{ $venue->zone ?: '-' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600">{{ Str::limit($venue->address, 40) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $venue->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $venue->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.venues.edit', $venue) }}" 
                                           class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition">
                                            Modifier
                                        </a>
                                        <form method="POST" action="{{ route('admin.venues.destroy', $venue) }}" 
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce PDV ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        <p class="text-xl font-bold text-gray-600">Aucun point de vente trouvé</p>
                                        <p class="text-gray-500 mt-2">Essayez de modifier vos filtres ou créez un nouveau PDV</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $venues->links() }}
            </div>
        </div>
    </div>

    <script>
        // Gestion de la sélection multiple
        const selectAllCheckbox = document.getElementById('selectAll');
        const venueCheckboxes = document.querySelectorAll('.venue-checkbox');
        const bulkPanel = document.getElementById('bulkActionsPanel');
        const selectedCountSpan = document.getElementById('selectedCount');

        function updateBulkPanel() {
            const selectedVenues = Array.from(venueCheckboxes).filter(cb => cb.checked);
            const count = selectedVenues.length;
            
            selectedCountSpan.textContent = count;
            bulkPanel.style.display = count > 0 ? 'block' : 'none';

            // Mettre à jour les IDs dans les formulaires
            const venueIds = selectedVenues.map(cb => cb.value);
            document.getElementById('bulkTypeVenueIds').value = JSON.stringify(venueIds);
            document.getElementById('bulkZoneVenueIds').value = JSON.stringify(venueIds);
        }

        selectAllCheckbox.addEventListener('change', function() {
            venueCheckboxes.forEach(cb => cb.checked = this.checked);
            updateBulkPanel();
        });

        venueCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkPanel);
        });
    </script>
</x-layouts.app>
