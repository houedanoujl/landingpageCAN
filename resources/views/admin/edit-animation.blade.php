<x-layouts.app title="Admin - Modifier Animation">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-4xl mx-auto px-4">

            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                    <span class="text-4xl">✏️</span> Modifier l'Animation
                </h1>
                <p class="text-gray-600 mt-2">Modifiez les détails de cette animation</p>
            </div>

            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
            @endif

            @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <p class="font-bold mb-2">Erreurs de validation:</p>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Formulaire -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <form action="{{ route('admin.update-animation', $animation->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Point de vente -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2">Point de Vente *</label>
                        <select name="bar_id" required
                                class="w-full border-2 border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-soboa-orange focus:border-soboa-orange">
                            <option value="">-- Sélectionner un point de vente --</option>
                            @foreach($bars as $bar)
                                <option value="{{ $bar->id }}" {{ old('bar_id', $animation->bar_id) == $bar->id ? 'selected' : '' }}>
                                    {{ $bar->name }}
                                    @if($bar->zone)
                                        ({{ $bar->zone }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Équipe à domicile -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2">Équipe à Domicile *</label>
                        <select name="home_team_id" required
                                class="w-full border-2 border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-soboa-orange focus:border-soboa-orange">
                            <option value="">-- Sélectionner l'équipe à domicile --</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ old('home_team_id', $animation->match->home_team_id) == $team->id ? 'selected' : '' }}>
                                    {{ $team->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Équipe extérieure -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2">Équipe Extérieure *</label>
                        <select name="away_team_id" required
                                class="w-full border-2 border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-soboa-orange focus:border-soboa-orange">
                            <option value="">-- Sélectionner l'équipe extérieure --</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}" {{ old('away_team_id', $animation->match->away_team_id) == $team->id ? 'selected' : '' }}>
                                    {{ $team->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Phase du match -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2">Phase du Match</label>
                        <select name="phase"
                                class="w-full border-2 border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-soboa-orange focus:border-soboa-orange">
                            <option value="group_stage" {{ old('phase', $animation->match->phase) == 'group_stage' ? 'selected' : '' }}>Phase de poules</option>
                            <option value="round_of_16" {{ old('phase', $animation->match->phase) == 'round_of_16' ? 'selected' : '' }}>1/8e de finale</option>
                            <option value="quarter_final" {{ old('phase', $animation->match->phase) == 'quarter_final' ? 'selected' : '' }}>Quart de finale</option>
                            <option value="semi_final" {{ old('phase', $animation->match->phase) == 'semi_final' ? 'selected' : '' }}>Demi-finale</option>
                            <option value="third_place" {{ old('phase', $animation->match->phase) == 'third_place' ? 'selected' : '' }}>3e place</option>
                            <option value="final" {{ old('phase', $animation->match->phase) == 'final' ? 'selected' : '' }}>Finale</option>
                        </select>
                        <p class="text-gray-500 text-sm mt-1">Utilisé pour l'affichage si les équipes ne sont pas encore connues</p>
                    </div>

                    <!-- Date de l'animation -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2">Date de l'Animation *</label>
                        <input type="date"
                               name="animation_date"
                               value="{{ old('animation_date', \Carbon\Carbon::parse($animation->animation_date)->format('Y-m-d')) }}"
                               required
                               class="w-full border-2 border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-soboa-orange focus:border-soboa-orange">
                        <p class="text-gray-500 text-sm mt-1">Date à laquelle l'animation aura lieu</p>
                    </div>

                    <!-- Heure de l'animation -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2">Heure de l'Animation</label>
                        <input type="text"
                               name="animation_time"
                               value="{{ old('animation_time', $animation->animation_time) }}"
                               placeholder="Ex: 15H, 20H00, etc."
                               class="w-full border-2 border-gray-200 rounded-lg p-3 focus:ring-2 focus:ring-soboa-orange focus:border-soboa-orange">
                        <p class="text-gray-500 text-sm mt-1">Format libre (ex: 15H, 20H00, etc.)</p>
                    </div>

                    <!-- Statut -->
                    <div class="mb-8">
                        <label class="block text-gray-700 font-bold mb-2">Statut</label>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio"
                                       name="is_active"
                                       value="1"
                                       {{ old('is_active', $animation->is_active) == 1 ? 'checked' : '' }}
                                       class="w-4 h-4 text-green-600 focus:ring-green-500">
                                <span class="text-gray-700">Active</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio"
                                       name="is_active"
                                       value="0"
                                       {{ old('is_active', $animation->is_active) == 0 ? 'checked' : '' }}
                                       class="w-4 h-4 text-red-600 focus:ring-red-500">
                                <span class="text-gray-700">Inactive</span>
                            </label>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="flex items-center gap-4 pt-6 border-t">
                        <button type="submit"
                                class="bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-3 px-6 rounded-lg transition flex items-center gap-2">
                            <span>💾</span> Enregistrer les modifications
                        </button>
                        <a href="{{ route('admin.bar-animations', $animation->bar_id) }}"
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-lg transition">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-layouts.app>
