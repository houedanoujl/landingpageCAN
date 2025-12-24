<x-layouts.app title="Admin - Modifier le média">
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
                    <h1 class="text-3xl font-black text-soboa-blue">Modifier le média</h1>
                    <p class="text-gray-600">{{ $mediaItem->title }}</p>
                </div>
            </div>

            <!-- Prévisualisation actuelle -->
            <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
                <h3 class="text-sm font-bold text-gray-500 mb-3">📎 Média actuel</h3>
                @if($mediaItem->type === 'photo')
                <img src="{{ $mediaItem->file_url }}" alt="{{ $mediaItem->title }}" class="w-full max-h-64 object-contain rounded-xl">
                @else
                <div class="aspect-video bg-black rounded-xl overflow-hidden">
                    @if($mediaItem->is_youtube && $mediaItem->youtube_id)
                    <iframe src="https://www.youtube.com/embed/{{ $mediaItem->youtube_id }}" 
                            class="w-full h-full" frameborder="0" allowfullscreen></iframe>
                    @elseif($mediaItem->file_path)
                    <video controls class="w-full h-full">
                        <source src="{{ $mediaItem->file_url }}" type="video/mp4">
                    </video>
                    @endif
                </div>
                @endif
            </div>

            <form action="{{ route('admin.update-media', $mediaItem->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-lg p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Type -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3">Type de média *</label>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-3 cursor-pointer p-3 border-2 rounded-xl hover:border-blue-300 transition has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="type" value="photo" 
                                   {{ $mediaItem->type === 'photo' ? 'checked' : '' }}
                                   class="w-5 h-5 text-blue-600" 
                                   onchange="toggleVideoFields(this.value)">
                            <span class="text-2xl">📸</span>
                            <span class="font-medium">Photo (Highlight)</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer p-3 border-2 rounded-xl hover:border-purple-300 transition has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50">
                            <input type="radio" name="type" value="video" 
                                   {{ $mediaItem->type === 'video' ? 'checked' : '' }}
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
                    <input type="text" name="title" id="title" value="{{ old('title', $mediaItem->title) }}" required
                           class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-soboa-orange focus:border-soboa-orange text-lg p-3">
                    @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-soboa-orange focus:border-soboa-orange">{{ old('description', $mediaItem->description) }}</textarea>
                    @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fichier -->
                <div>
                    <label for="file" class="block text-sm font-bold text-gray-700 mb-2">
                        Nouveau fichier (laisser vide pour conserver l'actuel)
                    </label>
                    <input type="file" name="file" id="file" accept="image/*,video/*"
                           class="w-full border-2 border-dashed border-gray-300 rounded-xl p-4 focus:ring-soboa-orange focus:border-soboa-orange hover:border-gray-400 transition">
                    <p class="text-gray-500 text-xs mt-2">📸 Photos: JPG, PNG, WebP (max 50MB) | 🎥 Vidéos: MP4, WebM (max 50MB)</p>
                    @error('file')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- URL Vidéo (pour YouTube, etc.) -->
                <div id="video-url-field" class="{{ $mediaItem->type !== 'video' ? 'hidden' : '' }}">
                    <label for="video_url" class="block text-sm font-bold text-gray-700 mb-2">
                        URL Vidéo (YouTube, etc.)
                    </label>
                    <input type="url" name="video_url" id="video_url" value="{{ old('video_url', $mediaItem->video_url) }}"
                           class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-soboa-orange focus:border-soboa-orange text-lg p-3"
                           placeholder="https://www.youtube.com/watch?v=...">
                    @error('video_url')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Miniature (pour vidéos) -->
                <div id="thumbnail-field" class="{{ $mediaItem->type !== 'video' ? 'hidden' : '' }}">
                    <label for="thumbnail" class="block text-sm font-bold text-gray-700 mb-2">Miniature</label>
                    @if($mediaItem->thumbnail_url)
                    <img src="{{ $mediaItem->thumbnail_url }}" alt="Miniature" class="w-32 h-20 object-cover rounded-lg mb-3">
                    @endif
                    <input type="file" name="thumbnail" id="thumbnail" accept="image/*"
                           class="w-full border-2 border-dashed border-gray-300 rounded-xl p-4 focus:ring-soboa-orange focus:border-soboa-orange hover:border-gray-400 transition">
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
                        <option value="{{ $bar->id }}" {{ old('bar_id', $mediaItem->bar_id) == $bar->id ? 'selected' : '' }}>
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
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $mediaItem->sort_order) }}" min="0"
                           class="w-32 border-gray-300 rounded-xl shadow-sm focus:ring-soboa-orange focus:border-soboa-orange text-lg p-3">
                    @error('sort_order')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Boutons -->
                <div class="flex gap-4 pt-6 border-t">
                    <button type="submit" 
                            class="flex-1 bg-soboa-orange hover:bg-soboa-orange/90 text-black font-bold py-4 px-6 rounded-xl transition shadow-lg text-lg">
                        ✅ Enregistrer les modifications
                    </button>
                    <a href="{{ route('admin.media') }}" 
                       class="flex-1 text-center bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-4 px-6 rounded-xl transition text-lg">
                        Annuler
                    </a>
                </div>
            </form>

            <!-- Zone de suppression -->
            <div class="mt-8 bg-red-50 border-2 border-red-200 rounded-xl p-6">
                <h3 class="text-red-800 font-bold text-lg mb-2">⚠️ Zone dangereuse</h3>
                <p class="text-red-600 text-sm mb-4">La suppression est définitive et ne peut pas être annulée.</p>
                <form action="{{ route('admin.delete-media', $mediaItem->id) }}" method="POST" 
                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce média ? Cette action est irréversible.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-xl transition">
                        🗑️ Supprimer ce média
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    function toggleVideoFields(type) {
        const videoUrlField = document.getElementById('video-url-field');
        const thumbnailField = document.getElementById('thumbnail-field');
        
        if (type === 'video') {
            videoUrlField.classList.remove('hidden');
            thumbnailField.classList.remove('hidden');
        } else {
            videoUrlField.classList.add('hidden');
            thumbnailField.classList.add('hidden');
        }
    }
    </script>
</x-layouts.app>
