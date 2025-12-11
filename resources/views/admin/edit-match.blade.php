<x-layouts.app title="Admin - Modifier Match">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-2xl mx-auto px-4">
            
            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('admin.matches') }}" class="text-soboa-orange hover:underline text-sm font-bold mb-2 inline-block">← Retour aux matchs</a>
                <h1 class="text-3xl font-black text-soboa-blue">Modifier le Match</h1>
            </div>

            <!-- Match Preview -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <div class="text-center">
                    <span class="text-sm text-gray-500">{{ $match->match_date->translatedFormat('l d F Y à H:i') }}</span>
                    <div class="flex items-center justify-center gap-6 mt-4">
                        <div class="text-center">
                            @if($match->homeTeam)
                            <img src="https://flagcdn.com/w80/{{ $match->homeTeam->iso_code }}.png" class="w-16 h-12 rounded shadow mx-auto mb-2">
                            @endif
                            <span class="font-bold text-lg">{{ $match->team_a }}</span>
                        </div>
                        <span class="text-3xl font-black text-gray-300">VS</span>
                        <div class="text-center">
                            @if($match->awayTeam)
                            <img src="https://flagcdn.com/w80/{{ $match->awayTeam->iso_code }}.png" class="w-16 h-12 rounded shadow mx-auto mb-2">
                            @endif
                            <span class="font-bold text-lg">{{ $match->team_b }}</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-4">📍 {{ $match->stadium }}</p>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <form action="{{ route('admin.update-match', $match->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Scores -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-4">Score Final</label>
                        <div class="flex items-center justify-center gap-4">
                            <div class="text-center">
                                <label class="text-xs text-gray-500 block mb-2">{{ $match->team_a }}</label>
                                <input type="number" 
                                       name="score_a" 
                                       value="{{ $match->score_a }}"
                                       min="0" 
                                       max="20"
                                       class="w-20 h-16 text-center text-3xl font-black border-2 border-gray-300 rounded-xl focus:border-soboa-orange focus:ring-soboa-orange">
                            </div>
                            <span class="text-3xl font-bold text-gray-400 mt-6">-</span>
                            <div class="text-center">
                                <label class="text-xs text-gray-500 block mb-2">{{ $match->team_b }}</label>
                                <input type="number" 
                                       name="score_b" 
                                       value="{{ $match->score_b }}"
                                       min="0" 
                                       max="20"
                                       class="w-20 h-16 text-center text-3xl font-black border-2 border-gray-300 rounded-xl focus:border-soboa-orange focus:ring-soboa-orange">
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Statut du match</label>
                        <select name="status" 
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-soboa-orange focus:ring-soboa-orange font-medium">
                            <option value="scheduled" {{ $match->status === 'scheduled' ? 'selected' : '' }}>📅 À venir (scheduled)</option>
                            <option value="finished" {{ $match->status === 'finished' ? 'selected' : '' }}>✅ Terminé (finished)</option>
                        </select>
                    </div>

                    <!-- Warning -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                        <p class="text-yellow-800 text-sm">
                            ⚠️ <strong>Important :</strong> Lorsque vous passez un match en "Terminé" avec un score, le calcul des points sera automatiquement déclenché pour tous les pronostics.
                        </p>
                    </div>

                    <!-- Submit -->
                    <div class="flex gap-4">
                        <a href="{{ route('admin.matches') }}" 
                           class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-4 px-4 rounded-xl text-center transition-colors">
                            Annuler
                        </a>
                        <button type="submit" 
                                class="flex-1 bg-soboa-orange hover:bg-soboa-orange-dark text-white font-bold py-4 px-4 rounded-xl transition-colors">
                            💾 Enregistrer
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-layouts.app>
