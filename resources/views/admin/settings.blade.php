<x-layouts.app title="Admin - Paramètres">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-3xl mx-auto px-4">

            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('admin.dashboard') }}" class="text-soboa-orange hover:underline font-bold mb-2 inline-block">
                    ← Retour au dashboard
                </a>
                <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                    <span class="text-4xl">⚙️</span> Paramètres du Site
                </h1>
                <p class="text-gray-600 mt-2">Configurez l'apparence et les informations générales du site</p>
            </div>

            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                ✅ {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('admin.update-settings') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Paramètres Généraux -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span>🌐</span> Informations Générales
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Nom du site *</label>
                            <input type="text" name="site_name" value="{{ old('site_name', $settings->site_name) }}"
                                   required
                                   class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                            <p class="text-gray-500 text-sm mt-1">Le nom affiché partout sur le site</p>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Logo du site</label>

                            @if($settings->logo_path)
                                <div class="mb-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <p class="text-sm text-gray-600 mb-2">Logo actuel :</p>
                                    <img src="{{ asset('storage/' . $settings->logo_path) }}"
                                         alt="Logo actuel"
                                         class="h-20 object-contain bg-white p-2 rounded border border-gray-300"
                                         id="current-logo">
                                </div>
                            @endif

                            <div class="flex items-center gap-3">
                                <input type="file"
                                       name="logo"
                                       id="logo-input"
                                       accept="image/png,image/jpeg,image/jpg,image/svg+xml"
                                       class="flex-1 border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">

                                @if($settings->logo_path)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="remove_logo" value="1" class="w-4 h-4 text-red-600">
                                        <span class="text-sm text-red-600">Supprimer</span>
                                    </label>
                                @endif
                            </div>

                            <p class="text-gray-500 text-sm mt-1">Format accepté : PNG, JPG, SVG (max 2 Mo)</p>

                            <!-- Preview du nouveau logo -->
                            <div id="logo-preview" class="hidden mt-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-sm text-gray-600 mb-2">Aperçu du nouveau logo :</p>
                                <img id="logo-preview-img" src="" alt="Aperçu" class="h-20 object-contain bg-white p-2 rounded border border-gray-300">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paramètres de Couleur -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span>🎨</span> Couleurs du Thème
                    </h2>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Couleur Primaire *</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="primary_color" value="{{ old('primary_color', $settings->primary_color) }}"
                                       required
                                       class="h-12 w-20 border border-gray-300 rounded-lg cursor-pointer">
                                <input type="text" value="{{ old('primary_color', $settings->primary_color) }}"
                                       readonly
                                       class="flex-1 border border-gray-300 rounded-lg p-3 bg-gray-50 font-mono text-sm">
                            </div>
                            <p class="text-gray-500 text-sm mt-1">Couleur principale du site (bleu SOBOA)</p>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Couleur Secondaire *</label>
                            <div class="flex items-center gap-3">
                                <input type="color" name="secondary_color" value="{{ old('secondary_color', $settings->secondary_color) }}"
                                       required
                                       class="h-12 w-20 border border-gray-300 rounded-lg cursor-pointer">
                                <input type="text" value="{{ old('secondary_color', $settings->secondary_color) }}"
                                       readonly
                                       class="flex-1 border border-gray-300 rounded-lg p-3 bg-gray-50 font-mono text-sm">
                            </div>
                            <p class="text-gray-500 text-sm mt-1">Couleur d'accentuation (orange SOBOA)</p>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <h3 class="font-bold text-gray-700 mb-3 text-sm">Aperçu des couleurs :</h3>
                        <div class="flex gap-3">
                            <div class="flex-1 text-center">
                                <div id="preview-primary" style="background-color: {{ $settings->primary_color }}" class="h-16 rounded-lg mb-2 shadow-inner"></div>
                                <span class="text-xs text-gray-600">Primaire</span>
                            </div>
                            <div class="flex-1 text-center">
                                <div id="preview-secondary" style="background-color: {{ $settings->secondary_color }}" class="h-16 rounded-lg mb-2 shadow-inner"></div>
                                <span class="text-xs text-gray-600">Secondaire</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Équipe Favorite -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span>⭐</span> Équipe à Mettre en Avant
                    </h2>

                    <div>
                        <label for="favorite_team_id" class="block text-gray-700 font-bold mb-2">
                            Équipe favorite
                        </label>
                        <select id="favorite_team_id" name="favorite_team_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-blue focus:border-transparent">
                            <option value="">Aucune équipe</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}"
                                        {{ $settings->favorite_team_id == $team->id ? 'selected' : '' }}>
                                    {{ $team->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-gray-500 text-sm mt-2">
                            Les matchs de cette équipe seront mis en évidence visuellement avec un badge "⭐ À suivre" et un fond dégradé spécial
                        </p>

                        @if($settings->favoriteTeam)
                        <div class="mt-4 p-4 bg-gradient-to-r from-green-50 via-white to-yellow-50 rounded-lg border-l-4 border-green-500">
                            <p class="text-sm font-medium text-gray-700">
                                Équipe actuellement mise en avant : <span class="font-bold text-green-700">{{ $settings->favoriteTeam->name }}</span>
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Paramètres de Géolocalisation -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span>📍</span> Géolocalisation
                    </h2>

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Rayon de géolocalisation (en mètres)</label>
                        <input type="number" name="geofencing_radius" value="{{ old('geofencing_radius', $settings->geofencing_radius) }}"
                               required min="10" max="1000" step="10"
                               class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                        <p class="text-gray-500 text-sm mt-1">
                            Distance maximale (en mètres) pour qu'un utilisateur puisse faire un check-in dans un point de vente.
                            Valeur recommandée : 50 mètres (précision GPS standard)
                        </p>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg mb-6">
                    <div class="flex items-start gap-3">
                        <div class="text-2xl">💡</div>
                        <div>
                            <h3 class="font-bold text-blue-900 mb-2">Informations</h3>
                            <ul class="text-sm text-blue-800 space-y-1">
                                <li>• Les modifications s'appliqueront immédiatement après l'enregistrement</li>
                                <li>• Couleur primaire : Utilisée pour les éléments principaux (navigation, boutons)</li>
                                <li>• Couleur secondaire : Utilisée pour les accents et les CTA</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-soboa-blue hover:bg-soboa-blue/90 text-white font-bold py-3 px-8 rounded-lg transition shadow-lg hover:scale-105">
                        💾 Enregistrer les paramètres
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
        // Mise à jour dynamique de l'aperçu des couleurs
        document.querySelector('[name="primary_color"]').addEventListener('input', function() {
            document.getElementById('preview-primary').style.backgroundColor = this.value;
            this.nextElementSibling.value = this.value;
        });

        document.querySelector('[name="secondary_color"]').addEventListener('input', function() {
            document.getElementById('preview-secondary').style.backgroundColor = this.value;
            this.nextElementSibling.value = this.value;
        });

        // Aperçu du logo avant upload
        document.getElementById('logo-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Vérifier la taille (2 Mo max)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Le fichier est trop volumineux. Taille maximum : 2 Mo');
                    this.value = '';
                    return;
                }

                // Afficher l'aperçu
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('logo-preview-img').src = event.target.result;
                    document.getElementById('logo-preview').classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                document.getElementById('logo-preview').classList.add('hidden');
            }
        });

        // Désactiver le champ de suppression si un nouveau logo est sélectionné
        const logoInput = document.getElementById('logo-input');
        const removeCheckbox = document.querySelector('[name="remove_logo"]');
        if (logoInput && removeCheckbox) {
            logoInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    removeCheckbox.checked = false;
                    removeCheckbox.disabled = true;
                } else {
                    removeCheckbox.disabled = false;
                }
            });
        }
    </script>
</x-layouts.app>
