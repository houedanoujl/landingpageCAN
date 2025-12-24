<x-layouts.app title="Admin - Ajouter un média">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-2xl mx-auto px-4">
            
            <!-- Header -->
            <div class="flex items-center gap-4 mb-8">
                <a href="{{ route('admin.media') }}" class="text-gray-500 hover:text-soboa-blue transition">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-black text-soboa-blue">Ajouter un média</h1>
                    <p class="text-gray-600">Photo ou vidéo pour les animations</p>
                </div>
            </div>

            <form action="{{ route('admin.store-media') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-lg p-6 space-y-6">
                @csrf

                <!-- Type -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3">Type de média *</label>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-3 cursor-pointer p-3 border-2 rounded-xl hover:border-blue-300 transition has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="type" value="photo" checked 
                                   class="w-5 h-5 text-blue-600" 
                                   onchange="toggleVideoFields(this.value)">
                            <span class="text-2xl">📸</span>
                            <span class="font-medium">Photo (Highlight)</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer p-3 border-2 rounded-xl hover:border-purple-300 transition has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50">
                            <input type="radio" name="type" value="video" 
                                   class="w-5 h-5 text-purple-600"
                                   onchange="toggleVideoFields(this.value)">
                            <span class="text-2xl">🎥</span>
                            <span class="font-medium">Vidéo</span>
                        </label>
                    </div>
                    @error('type')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Titre -->
                <div>
                    <label for="title" class="block text-sm font-bold text-gray-700 mb-2">Titre *</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                           class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-soboa-orange focus:border-soboa-orange text-lg p-3"
                           placeholder="Ex: Ambiance incroyable au match Sénégal vs Maroc">
                    @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-soboa-orange focus:border-soboa-orange"
                              placeholder="Description optionnelle du média...">{{ old('description') }}</textarea>
                    @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fichier -->
                <div>
                    <label for="file" class="block text-sm font-bold text-gray-700 mb-2">
                        Fichier <span id="file-label-required">*</span>
                    </label>
                    <input type="file" name="file" id="file" accept="image/*,video/*"
                           class="w-full border-2 border-dashed border-gray-300 rounded-xl p-4 focus:ring-soboa-orange focus:border-soboa-orange hover:border-gray-400 transition">
                    <p class="text-gray-500 text-xs mt-2">📸 Photos: JPG, PNG, WebP (max 50MB) | 🎥 Vidéos: MP4, WebM (max 50MB)</p>
                    @error('file')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- URL Vidéo (pour YouTube, etc.) -->
                <div id="video-url-field" class="hidden">
                    <label for="video_url" class="block text-sm font-bold text-gray-700 mb-2">
                        URL Vidéo (YouTube, etc.)
                    </label>
                    <input type="url" name="video_url" id="video_url" value="{{ old('video_url') }}"
                           class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-soboa-orange focus:border-soboa-orange text-lg p-3"
                           placeholder="https://www.youtube.com/watch?v=...">
                    <p class="text-gray-500 text-xs mt-2">💡 Si vous utilisez une URL YouTube, vous n'avez pas besoin d'uploader un fichier.</p>
                    @error('video_url')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Miniature (pour vidéos) -->
                <div id="thumbnail-field" class="hidden">
                    <label for="thumbnail" class="block text-sm font-bold text-gray-700 mb-2">Miniature (optionnel)</label>
                    <input type="file" name="thumbnail" id="thumbnail" accept="image/*"
                           class="w-full border-2 border-dashed border-gray-300 rounded-xl p-4 focus:ring-soboa-orange focus:border-soboa-orange hover:border-gray-400 transition">
                    <p class="text-gray-500 text-xs mt-2">Image de prévisualisation pour la vidéo (JPG, PNG, max 5MB)</p>
                    @error('thumbnail')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Lieu associé -->
                <div>
                    <label for="bar_id" class="block text-sm font-bold text-gray-700 mb-2">📍 Lieu associé (optionnel)</label>
                    <select name="bar_id" id="bar_id"
                            class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-soboa-orange focus:border-soboa-orange text-lg p-3">
                        <option value="">-- Aucun lieu --</option>
                        @foreach($bars as $bar)
                        <option value="{{ $bar->id }}" {{ old('bar_id') == $bar->id ? 'selected' : '' }}>
                            {{ $bar->name }} ({{ $bar->zone ?? 'Sans zone' }})
                        </option>
                        @endforeach
                    </select>
                    @error('bar_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ordre d'affichage -->
                <div>
                    <label for="sort_order" class="block text-sm font-bold text-gray-700 mb-2">Ordre d'affichage</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                           class="w-32 border-gray-300 rounded-xl shadow-sm focus:ring-soboa-orange focus:border-soboa-orange text-lg p-3">
                    <p class="text-gray-500 text-xs mt-2">Les médias avec un ordre plus bas apparaissent en premier.</p>
                    @error('sort_order')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Boutons -->
                <div class="flex gap-4 pt-6 border-t">
                    <button type="submit" 
                            class="flex-1 bg-soboa-orange hover:bg-soboa-orange/90 text-black font-bold py-4 px-6 rounded-xl transition shadow-lg text-lg">
                        ✅ Ajouter le média
                    </button>
                    <a href="{{ route('admin.media') }}" 
                       class="flex-1 text-center bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-4 px-6 rounded-xl transition text-lg">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
    function toggleVideoFields(type) {
        const videoUrlField = document.getElementById('video-url-field');
        const thumbnailField = document.getElementById('thumbnail-field');
        const fileLabelRequired = document.getElementById('file-label-required');
        
        if (type === 'video') {
            videoUrlField.classList.remove('hidden');
            thumbnailField.classList.remove('hidden');
            fileLabelRequired.textContent = ''; // Pas obligatoire si URL vidéo
        } else {
            videoUrlField.classList.add('hidden');
            thumbnailField.classList.add('hidden');
            fileLabelRequired.textContent = '*';
        }
    }
    </script>
</x-layouts.app>
