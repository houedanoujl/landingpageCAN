<x-layouts.app title="Admin - Modifier Match">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-2xl mx-auto px-4">
            
            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('admin.matches') }}" class="text-soboa-orange hover:underline text-sm font-bold mb-2 inline-block">← Retour aux matchs</a>
                <h1 class="text-3xl font-black text-soboa-blue">Modifier le Match</h1>
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

            <!-- Edit Form -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <form action="{{ route('admin.update-match', $match->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Teams -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Équipe domicile *</label>
                            <select name="home_team_id" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                                @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ old('home_team_id', $match->home_team_id) == $team->id ? 'selected' : '' }}>
                                    {{ $team->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Équipe extérieur *</label>
                            <select name="away_team_id" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                                @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ old('away_team_id', $match->away_team_id) == $team->id ? 'selected' : '' }}>
                                    {{ $team->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Date et heure du match *</label>
                        <input type="datetime-local" name="match_date" value="{{ old('match_date', $match->match_date->format('Y-m-d\TH:i')) }}" required
                               class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Phase du tournoi *</label>
                        <select name="phase" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                            <option value="">Sélectionner...</option>
                            <option value="group_stage" {{ old('phase', $match->phase) === 'group_stage' ? 'selected' : '' }}>Phase de poules</option>
                            <option value="round_of_16" {{ old('phase', $match->phase) === 'round_of_16' ? 'selected' : '' }}>1/8e de finale</option>
                            <option value="quarter_final" {{ old('phase', $match->phase) === 'quarter_final' ? 'selected' : '' }}>1/4 de finale</option>
                            <option value="semi_final" {{ old('phase', $match->phase) === 'semi_final' ? 'selected' : '' }}>1/2 finale (Demi-finales)</option>
                            <option value="third_place" {{ old('phase', $match->phase) === 'third_place' ? 'selected' : '' }}>Match pour la 3e place</option>
                            <option value="final" {{ old('phase', $match->phase) === 'final' ? 'selected' : '' }}>Finale</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Groupe (pour phase de poules uniquement)</label>
                        <select name="group_name" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                            <option value="">Sélectionner un groupe...</option>
                            @foreach($groups as $group)
                            <option value="{{ $group }}" {{ old('group_name', $match->group_name) == $group ? 'selected' : '' }}>
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
                            <option value="{{ $stadium->name }}" {{ old('stadium', $match->stadium) == $stadium->name ? 'selected' : '' }}>
                                {{ $stadium->name }} - {{ $stadium->city }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Points de vente (PDV) -->
                    <div class="border-2 border-indigo-200 rounded-xl p-6 bg-indigo-50">
                        <div class="flex items-center justify-between mb-4">
                            <label class="block text-gray-800 font-bold text-lg">
                                📍 Points de Vente Assignés
                            </label>
                            <span class="text-sm text-indigo-600 font-medium">
                                {{ count($assignedBarIds) }} PDV sélectionné(s)
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">
                            Sélectionnez les points de vente où ce match sera disponible pour les pronostics.
                        </p>

                        <!-- Recherche de PDV -->
                        <div class="mb-4">
                            <input type="text" id="venueSearch" placeholder="🔍 Rechercher un PDV..."
                                   onkeyup="filterVenues()"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <!-- Liste des PDV par zone -->
                        <div class="max-h-96 overflow-y-auto space-y-4" id="venuesList">
                            @php
                                $barsByZone = $bars->groupBy('zone');
                            @endphp

                            @foreach($barsByZone as $zone => $zoneBars)
                                <div class="venue-zone-group">
                                    <div class="bg-white rounded-lg p-4 shadow-sm">
                                        <div class="flex items-center justify-between mb-3">
                                            <h3 class="font-bold text-gray-700">{{ $zone ?: 'Sans zone' }}</h3>
                                            <label class="flex items-center gap-2 text-sm text-indigo-600 cursor-pointer">
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
                                                           name="venue_ids[]"
                                                           value="{{ $bar->id }}"
                                                           {{ in_array($bar->id, $assignedBarIds) ? 'checked' : '' }}
                                                           class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500 venue-checkbox zone-{{ Str::slug($zone) }}">
                                                    <span class="text-sm font-medium text-gray-700">{{ $bar->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Scores -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-4">Score Final</label>
                        <div class="flex items-center justify-center gap-4">
                            <div class="text-center">
                                <label class="text-xs text-gray-500 block mb-2">Domicile</label>
                                <input type="number" 
                                       name="score_a" 
                                       id="score_a"
                                       value="{{ old('score_a', $match->score_a) }}"
                                       min="0" 
                                       max="20"
                                       onchange="checkForPenalties()"
                                       class="w-20 h-16 text-center text-3xl font-black border-2 border-gray-300 rounded-xl focus:border-soboa-orange focus:ring-soboa-orange">
                            </div>
                            <span class="text-3xl font-bold text-gray-400 mt-6">-</span>
                            <div class="text-center">
                                <label class="text-xs text-gray-500 block mb-2">Extérieur</label>
                                <input type="number" 
                                       name="score_b" 
                                       id="score_b"
                                       value="{{ old('score_b', $match->score_b) }}"
                                       min="0" 
                                       max="20"
                                       onchange="checkForPenalties()"
                                       class="w-20 h-16 text-center text-3xl font-black border-2 border-gray-300 rounded-xl focus:border-soboa-orange focus:ring-soboa-orange">
                            </div>
                        </div>
                    </div>

                    <!-- Tirs au But (visible uniquement si égalité) -->
                    <div id="penaltiesSection" class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-6" style="display: none;">
                        <div class="mb-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" 
                                       name="had_penalties" 
                                       id="had_penalties"
                                       value="1"
                                       {{ old('had_penalties', $match->winner ? 1 : 0) ? 'checked' : '' }}
                                       onchange="togglePenaltyWinner()"
                                       class="w-5 h-5 text-yellow-600 rounded focus:ring-yellow-500">
                                <span class="font-bold text-gray-800">⚽ Ce match a eu des tirs au but</span>
                            </label>
                            <p class="text-sm text-gray-600 ml-8 mt-1">
                                ℹ️ Si coché, aucun point ne sera attribué pour le score exact (car c'est une égalité)
                            </p>
                        </div>

                        <div id="penaltyWinnerSection" class="mt-4" style="display: none;">
                            <label class="block text-sm font-bold text-gray-700 mb-3">
                                🏆 Vainqueur aux tirs au but *
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center justify-center gap-2 p-4 border-2 rounded-xl cursor-pointer transition-all hover:bg-yellow-100 has-[:checked]:border-yellow-600 has-[:checked]:bg-yellow-100">
                                    <input type="radio" 
                                           name="winner" 
                                           value="home"
                                           {{ old('winner', $match->winner) === 'home' ? 'checked' : '' }}
                                           class="w-5 h-5 text-yellow-600">
                                    <span class="font-bold text-gray-800">🏠 Équipe Domicile</span>
                                </label>
                                <label class="flex items-center justify-center gap-2 p-4 border-2 rounded-xl cursor-pointer transition-all hover:bg-yellow-100 has-[:checked]:border-yellow-600 has-[:checked]:bg-yellow-100">
                                    <input type="radio" 
                                           name="winner" 
                                           value="away"
                                           {{ old('winner', $match->winner) === 'away' ? 'checked' : '' }}
                                           class="w-5 h-5 text-yellow-600">
                                    <span class="font-bold text-gray-800">✈️ Équipe Extérieur</span>
                                </label>
                            </div>
                            <p class="text-xs text-gray-600 mt-3">
                                💡 Les utilisateurs qui ont prédit le bon vainqueur aux TAB recevront +3 pts
                            </p>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Statut du match *</label>
                        <select name="status" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-soboa-orange focus:ring-soboa-orange font-medium">
                            <option value="scheduled" {{ old('status', $match->status) === 'scheduled' ? 'selected' : '' }}>📅 À venir</option>
                            <option value="live" {{ old('status', $match->status) === 'live' ? 'selected' : '' }}>🔴 En cours</option>
                            <option value="finished" {{ old('status', $match->status) === 'finished' ? 'selected' : '' }}>✅ Terminé</option>
                        </select>
                    </div>

                    <!-- Warning -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                        <p class="text-yellow-800 text-sm">
                            ⚠️ <strong>Important :</strong> Lorsque vous passez un match en "Terminé" avec un score, le calcul des points sera automatiquement déclenché pour tous les pronostics.
                        </p>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end items-center pt-4 border-t">
                        <div class="flex gap-4">
                            <a href="{{ route('admin.matches') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-lg transition">
                                Annuler
                            </a>
                            <button type="submit" class="bg-soboa-orange hover:bg-soboa-orange/90 text-black font-bold py-3 px-6 rounded-lg transition">
                                � Enregistrer
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Formulaire de suppression séparé -->
                <div class="mt-6 pt-6 border-t border-red-200">
                    <form action="{{ route('admin.delete-match', $match->id) }}" method="POST" onsubmit="return confirm('Supprimer ce match et tous ses pronostics ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800 hover:underline font-bold">
                            �️ Supprimer ce match
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Fonction de recherche de PDV
        function filterVenues() {
            const search = document.getElementById('venueSearch').value.toLowerCase();
            const venues = document.querySelectorAll('.venue-item');
            const zoneGroups = document.querySelectorAll('.venue-zone-group');

            venues.forEach(venue => {
                const name = venue.dataset.venueName;
                const zone = venue.dataset.venueZone;

                if (name.includes(search) || zone.includes(search)) {
                    venue.style.display = 'flex';
                } else {
                    venue.style.display = 'none';
                }
            });

            // Cacher les groupes de zones vides
            zoneGroups.forEach(group => {
                const visibleVenues = group.querySelectorAll('.venue-item[style="display: flex;"], .venue-item:not([style])');
                if (search && visibleVenues.length === 0) {
                    group.style.display = 'none';
                } else {
                    group.style.display = 'block';
                }
            });
        }

        // Fonction pour sélectionner/désélectionner tous les PDV d'une zone
        function toggleZone(checkbox, zone) {
            const zoneSlug = zone.toLowerCase().replace(/[^a-z0-9]+/g, '-');
            const checkboxes = document.querySelectorAll('.zone-' + zoneSlug);

            checkboxes.forEach(cb => {
                cb.checked = checkbox.checked;
            });

            updateCount();
        }

        // Mettre à jour le compteur de PDV sélectionnés
        function updateCount() {
            const checked = document.querySelectorAll('.venue-checkbox:checked').length;
            const countElement = document.querySelector('.text-indigo-600.font-medium');
            if (countElement) {
                countElement.textContent = checked + ' PDV sélectionné(s)';
            }
        }

        // Écouter les changements sur les checkboxes
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.venue-checkbox');
            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateCount);
            });

            // Vérifier au chargement si les tirs au but doivent être affichés
            checkForPenalties();
            togglePenaltyWinner();
        });

        // Vérifier si les scores sont égaux pour afficher la section TAB
        function checkForPenalties() {
            const scoreA = document.getElementById('score_a').value;
            const scoreB = document.getElementById('score_b').value;
            const penaltiesSection = document.getElementById('penaltiesSection');

            if (scoreA !== '' && scoreB !== '' && scoreA === scoreB) {
                penaltiesSection.style.display = 'block';
            } else {
                penaltiesSection.style.display = 'none';
                // Réinitialiser les champs si on cache la section
                document.getElementById('had_penalties').checked = false;
                togglePenaltyWinner();
            }
        }

        // Afficher/cacher la sélection du vainqueur selon la checkbox
        function togglePenaltyWinner() {
            const hadPenalties = document.getElementById('had_penalties').checked;
            const winnerSection = document.getElementById('penaltyWinnerSection');

            if (hadPenalties) {
                winnerSection.style.display = 'block';
            } else {
                winnerSection.style.display = 'none';
                // Décocher les radios si on cache la section
                document.querySelectorAll('input[name="winner"]').forEach(radio => {
                    radio.checked = false;
                });
            }
        }
    </script>
</x-layouts.app>
