<x-layouts.app title="Admin - Nouveau Match">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-2xl mx-auto px-4">
            
            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('admin.matches') }}" class="text-soboa-orange hover:underline font-bold mb-2 inline-block">
                    ← Retour aux matchs
                </a>
                <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                    <span class="text-4xl"></span> Nouveau Match
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

            <div class="bg-white rounded-xl shadow-lg p-6">
                <form action="{{ route('admin.store-match') }}" method="POST">
                    @csrf

                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 font-bold mb-2">Équipe domicile *</label>
                                <select name="home_team_id" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                                    <option value="">Sélectionner...</option>
                                    @foreach($teams as $team)
                                    <option value="{{ $team->id }}" {{ old('home_team_id') == $team->id ? 'selected' : '' }}>
                                        {{ $team->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 font-bold mb-2">Équipe extérieur *</label>
                                <select name="away_team_id" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                                    <option value="">Sélectionner...</option>
                                    @foreach($teams as $team)
                                    <option value="{{ $team->id }}" {{ old('away_team_id') == $team->id ? 'selected' : '' }}>
                                        {{ $team->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Date et heure du match *</label>
                            <input type="datetime-local" name="match_date" value="{{ old('match_date') }}" required
                                   class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Phase du tournoi *</label>
                            <select name="phase" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                                <option value="">Sélectionner...</option>
                                <option value="group_stage" {{ old('phase') === 'group_stage' ? 'selected' : '' }}>Phase de poules</option>
                                <option value="round_of_16" {{ old('phase') === 'round_of_16' ? 'selected' : '' }}>1/8e de finale</option>
                                <option value="quarter_final" {{ old('phase') === 'quarter_final' ? 'selected' : '' }}>1/4 de finale</option>
                                <option value="semi_final" {{ old('phase') === 'semi_final' ? 'selected' : '' }}>1/2 finale (Demi-finales)</option>
                                <option value="third_place" {{ old('phase') === 'third_place' ? 'selected' : '' }}>Match pour la 3e place</option>
                                <option value="final" {{ old('phase') === 'final' ? 'selected' : '' }}>Finale</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Groupe (pour phase de poules uniquement)</label>
                            <select name="group_name" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                                <option value="">Sélectionner un groupe...</option>
                                @foreach($groups as $group)
                                <option value="{{ $group }}" {{ old('group_name') == $group ? 'selected' : '' }}>
                                    Groupe {{ $group }}
                                </option>
                                @endforeach
                            </select>
                            <p class="text-sm text-gray-500 mt-1">Laisser vide si ce n'est pas un match de poule</p>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Stade</label>
                            <select name="stadium" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                                <option value="">Sélectionner un stade...</option>
                                @foreach($stadiums as $stadium)
                                <option value="{{ $stadium->name }}" {{ old('stadium') == $stadium->name ? 'selected' : '' }}>
                                    {{ $stadium->name }} - {{ $stadium->city }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Statut *</label>
                            <select name="status" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                                <option value="scheduled" {{ old('status') === 'scheduled' ? 'selected' : '' }}>Programmé</option>
                                <option value="live" {{ old('status') === 'live' ? 'selected' : '' }}>En cours</option>
                                <option value="finished" {{ old('status') === 'finished' ? 'selected' : '' }}>Terminé</option>
                            </select>
                        </div>

                        <!-- Points de Vente (Venues) -->
                        <div>
                            <label class="block text-gray-700 font-bold mb-3">Points de Vente pour la diffusion</label>
                            <div class="border border-gray-300 rounded-lg p-4 bg-gray-50 max-h-96 overflow-y-auto">
                                <div class="mb-3">
                                    <input type="text" id="venueSearch" onkeyup="filterVenues()"
                                           placeholder="Rechercher un point de vente..."
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                                </div>

                                @php
                                    $barsByZone = $bars->groupBy('zone')->sortKeys();
                                @endphp

                                <div class="space-y-4">
                                    @foreach($barsByZone as $zone => $zoneBars)
                                        <div class="venue-zone-group">
                                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                                <div class="flex items-center justify-between mb-2">
                                                    <h3 class="font-bold text-gray-700 text-sm">{{ $zone ?: 'Sans zone' }}</h3>
                                                    <label class="flex items-center gap-2 text-xs text-indigo-600 cursor-pointer">
                                                        <input type="checkbox" onchange="toggleZone(this, '{{ $zone }}')"
                                                               class="w-4 h-4 text-indigo-600 rounded">
                                                        <span class="font-medium">Tout sélectionner</span>
                                                    </label>
                                                </div>
                                                <div class="grid grid-cols-2 gap-2">
                                                    @foreach($zoneBars as $bar)
                                                        <label class="venue-item flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer transition"
                                                               data-venue-name="{{ strtolower($bar->name) }}"
                                                               data-venue-zone="{{ strtolower($zone) }}">
                                                            <input type="checkbox"
                                                                   name="venues[]"
                                                                   value="{{ $bar->id }}"
                                                                   {{ is_array(old('venues')) && in_array($bar->id, old('venues')) ? 'checked' : '' }}
                                                                   class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500 venue-checkbox zone-{{ Str::slug($zone) }}">
                                                            <span class="text-sm text-gray-700">{{ $bar->name }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">Sélectionnez les bars où le match sera diffusé</p>
                        </div>

                        <div class="flex justify-end gap-4 pt-4 border-t">
                            <a href="{{ route('admin.matches') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-lg transition">
                                Annuler
                            </a>
                            <button type="submit" class="bg-soboa-blue hover:bg-soboa-blue/90 text-white font-bold py-3 px-6 rounded-lg transition">
                                Créer le match
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        // Filter venues by search term
        function filterVenues() {
            const search = document.getElementById('venueSearch').value.toLowerCase();
            const venues = document.querySelectorAll('.venue-item');

            venues.forEach(venue => {
                const name = venue.dataset.venueName;
                const zone = venue.dataset.venueZone;

                if (name.includes(search) || zone.includes(search)) {
                    venue.style.display = 'flex';
                } else {
                    venue.style.display = 'none';
                }
            });
        }

        // Toggle all venues in a zone
        function toggleZone(checkbox, zone) {
            const zoneSlug = zone.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
            const checkboxes = document.querySelectorAll('.zone-' + zoneSlug);
            checkboxes.forEach(cb => {
                cb.checked = checkbox.checked;
            });
        }
    </script>
</x-layouts.app>
