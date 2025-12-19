<x-layouts.app title="Admin - Modifier Point de Vente">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="mb-8">
            <a href="{{ route('admin.venues.index') }}" class="text-soboa-blue hover:text-blue-700 font-bold flex items-center gap-2 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Retour à la liste
            </a>
            <h1 class="text-3xl font-black text-gray-900">Modifier le PDV</h1>
            <p class="text-gray-600 mt-1">Modifier la catégorie, la zone ou les informations du point de vente</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-8">
            <form method="POST" action="{{ route('admin.venues.update', $venue) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nom -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Nom du PDV <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $venue->name) }}" required
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-blue focus:border-transparent @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Type PDV (Catégorie) -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Type de PDV <span class="text-red-500">*</span>
                        </label>
                        <select name="type_pdv" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-blue focus:border-transparent @error('type_pdv') border-red-500 @enderror">
                            @foreach($typePdvOptions as $key => $label)
                                <option value="{{ $key }}" {{ old('type_pdv', $venue->type_pdv) === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('type_pdv')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Zone -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Zone géographique
                        </label>
                        <input type="text" name="zone" value="{{ old('zone', $venue->zone) }}" placeholder="Ex: Plateau, Almadies, Thiès..."
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-blue focus:border-transparent @error('zone') border-red-500 @enderror">
                        @error('zone')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-sm text-gray-500 mt-1">Quartier, ville ou région spécifique</p>
                    </div>

                    <!-- Adresse -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Adresse <span class="text-red-500">*</span>
                        </label>
                        <textarea name="address" rows="3" required
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-blue focus:border-transparent @error('address') border-red-500 @enderror">{{ old('address', $venue->address) }}</textarea>
                        @error('address')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Latitude -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Latitude <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="latitude" step="0.00000001" value="{{ old('latitude', $venue->latitude) }}" required
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-blue focus:border-transparent @error('latitude') border-red-500 @enderror">
                        @error('latitude')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Longitude -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Longitude <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="longitude" step="0.00000001" value="{{ old('longitude', $venue->longitude) }}" required
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-blue focus:border-transparent @error('longitude') border-red-500 @enderror">
                        @error('longitude')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Statut -->
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $venue->is_active) ? 'checked' : '' }}
                                   class="w-6 h-6 rounded border-gray-300 text-soboa-blue focus:ring-soboa-blue">
                            <div>
                                <span class="font-bold text-gray-900">PDV Actif</span>
                                <p class="text-sm text-gray-600">Si décoché, le PDV ne sera pas visible dans l'application</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-4 mt-8 pt-6 border-t border-gray-200">
                    <button type="submit" class="flex-1 bg-soboa-blue hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg transition">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Enregistrer les modifications
                    </button>
                    <a href="{{ route('admin.venues.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-lg transition">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
