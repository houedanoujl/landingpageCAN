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

                    <!-- Scores -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-4">Score Final</label>
                        <div class="flex items-center justify-center gap-4">
                            <div class="text-center">
                                <label class="text-xs text-gray-500 block mb-2">Domicile</label>
                                <input type="number" 
                                       name="score_a" 
                                       value="{{ old('score_a', $match->score_a) }}"
                                       min="0" 
                                       max="20"
                                       class="w-20 h-16 text-center text-3xl font-black border-2 border-gray-300 rounded-xl focus:border-soboa-orange focus:ring-soboa-orange">
                            </div>
                            <span class="text-3xl font-bold text-gray-400 mt-6">-</span>
                            <div class="text-center">
                                <label class="text-xs text-gray-500 block mb-2">Extérieur</label>
                                <input type="number" 
                                       name="score_b" 
                                       value="{{ old('score_b', $match->score_b) }}"
                                       min="0" 
                                       max="20"
                                       class="w-20 h-16 text-center text-3xl font-black border-2 border-gray-300 rounded-xl focus:border-soboa-orange focus:ring-soboa-orange">
                            </div>
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
</x-layouts.app>
