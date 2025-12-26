<x-layouts.app title="Admin - Modifier Stade">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-2xl mx-auto px-4">

            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('admin.stadiums') }}" class="text-soboa-orange hover:underline font-bold mb-2 inline-block">
                    ← Retour aux stades
                </a>
                <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                    <span class="text-4xl">🏟️</span> Modifier {{ $stadium->name }}
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
                <form action="{{ route('admin.update-stadium', $stadium->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Nom du stade *</label>
                            <input type="text" name="name" value="{{ old('name', $stadium->name) }}" required
                                   class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue"
                                   placeholder="Ex: Stade Mohammed V">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Ville *</label>
                            <input type="text" name="city" value="{{ old('city', $stadium->city) }}" required
                                   class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue"
                                   placeholder="Ex: Casablanca">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Capacité *</label>
                            <input type="number" name="capacity" value="{{ old('capacity', $stadium->capacity) }}" required min="0"
                                   class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue"
                                   placeholder="Ex: 45000">
                            <p class="text-gray-500 text-sm mt-1">Nombre de places disponibles</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 font-bold mb-2">Latitude *</label>
                                <input type="text" name="latitude" value="{{ old('latitude', $stadium->latitude) }}" required
                                       class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue"
                                       placeholder="Ex: 33.582869">
                                <p class="text-gray-500 text-xs mt-1">Entre -90 et 90</p>
                            </div>

                            <div>
                                <label class="block text-gray-700 font-bold mb-2">Longitude *</label>
                                <input type="text" name="longitude" value="{{ old('longitude', $stadium->longitude) }}" required
                                       class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue"
                                       placeholder="Ex: -7.646877">
                                <p class="text-gray-500 text-xs mt-1">Entre -180 et 180</p>
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $stadium->is_active) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-soboa-blue shadow-sm focus:border-soboa-blue focus:ring focus:ring-soboa-blue focus:ring-opacity-50">
                                <span class="ml-2 text-gray-700 font-bold">Stade actif</span>
                            </label>
                            <p class="text-gray-500 text-sm mt-1">Les stades actifs apparaissent dans les formulaires de création de matchs</p>
                        </div>

                        <div class="flex justify-between items-center pt-4 border-t">
                            <button type="button" 
                                    onclick="deleteStadium()"
                                    class="text-red-600 hover:underline font-bold">
                                🗑️ Supprimer
                            </button>
                            <div class="flex gap-4">
                                <a href="{{ route('admin.stadiums') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-lg transition">
                                    Annuler
                                </a>
                                <button type="submit" class="bg-soboa-blue hover:bg-soboa-blue/90 text-white font-bold py-3 px-6 rounded-lg transition">
                                    Enregistrer
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                
                <!-- Formulaire de suppression SÉPARÉ -->
                <form id="delete-stadium-form" action="{{ route('admin.delete-stadium', $stadium->id) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>

        </div>
    </div>
    
    <script>
        function deleteStadium() {
            if (confirm('⚠️ Êtes-vous sûr de vouloir supprimer ce stade ?\n\nCette action est IRRÉVERSIBLE.')) {
                document.getElementById('delete-stadium-form').submit();
            }
        }
    </script>
</x-layouts.app>
