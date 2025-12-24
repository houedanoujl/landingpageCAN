<x-layouts.app title="Admin - Médias Animations">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                        <span class="text-4xl">📸</span> Médias Animations
                    </h1>
                    <p class="text-gray-600 mt-2">Gérez les highlights (photos) et vidéos des animations</p>
                </div>
                <a href="{{ route('admin.create-media') }}" 
                   class="inline-flex items-center gap-2 bg-soboa-orange hover:bg-soboa-orange/90 text-black font-bold py-3 px-6 rounded-xl transition shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Ajouter un média
                </a>
            </div>

            @if(isset($tableNotExists) && $tableNotExists)
            <!-- Alert: Table doesn't exist -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-xl shadow">
                <div class="flex items-center gap-4">
                    <span class="text-4xl">⚠️</span>
                    <div>
                        <h3 class="font-bold text-yellow-800 text-lg">Migration requise</h3>
                        <p class="text-yellow-700 mt-1">La table <code class="bg-yellow-100 px-2 py-1 rounded font-mono">animation_media</code> n'existe pas encore.</p>
                        <p class="text-yellow-600 text-sm mt-2">Exécutez les migrations pour créer cette table :</p>
                        <code class="block bg-yellow-100 px-3 py-2 rounded font-mono mt-2 text-yellow-800">php artisan migrate</code>
                    </div>
                </div>
            </div>
            @else

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-blue-500">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-3xl">📸</span>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Photos (Highlights)</p>
                            <p class="text-3xl font-black text-soboa-blue">{{ $photos->count() }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-purple-500">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center">
                            <span class="text-3xl">🎥</span>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Vidéos</p>
                            <p class="text-3xl font-black text-purple-600">{{ $videos->count() }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-green-500">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-3xl">✅</span>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Médias actifs</p>
                            <p class="text-3xl font-black text-green-600">{{ $media->where('is_active', true)->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Photos Section -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
                <div class="px-6 py-4 bg-blue-50 border-b border-blue-100">
                    <h2 class="text-xl font-bold text-blue-800 flex items-center gap-2">
                        <span>📸</span> Highlights (Photos)
                    </h2>
                </div>
                
                @if($photos->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
                    @foreach($photos as $photo)
                    <div class="bg-gray-50 rounded-xl overflow-hidden border-2 {{ $photo->is_active ? 'border-gray-200' : 'border-red-300 bg-red-50' }} hover:shadow-lg transition">
                        <div class="aspect-video relative">
                            <img src="{{ $photo->file_url }}" alt="{{ $photo->title }}" class="w-full h-full object-cover">
                            @if(!$photo->is_active)
                            <div class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-lg font-bold">Inactif</div>
                            @endif
                            <div class="absolute top-2 left-2 bg-black/70 text-white text-xs px-2 py-1 rounded-lg font-bold">
                                #{{ $photo->sort_order }}
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-gray-800 truncate">{{ $photo->title }}</h3>
                            @if($photo->bar)
                            <p class="text-xs text-gray-500 mt-1">📍 {{ $photo->bar->name }}</p>
                            @endif
                            <div class="flex gap-2 mt-3">
                                <a href="{{ route('admin.edit-media', $photo->id) }}" 
                                   class="flex-1 text-center bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm py-2 rounded-lg transition font-medium">
                                    Modifier
                                </a>
                                <form action="{{ route('admin.toggle-media', $photo->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full {{ $photo->is_active ? 'bg-yellow-100 hover:bg-yellow-200 text-yellow-700' : 'bg-green-100 hover:bg-green-200 text-green-700' }} text-sm py-2 rounded-lg transition font-medium">
                                        {{ $photo->is_active ? 'Désactiver' : 'Activer' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.delete-media', $photo->id) }}" method="POST" 
                                      onsubmit="return confirm('Supprimer cette photo ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-100 hover:bg-red-200 text-red-700 text-sm py-2 px-3 rounded-lg transition">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-8 text-center text-gray-500">
                    <span class="text-5xl block mb-4">📸</span>
                    <p class="text-lg">Aucune photo pour le moment.</p>
                    <a href="{{ route('admin.create-media') }}" class="inline-block mt-4 bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold py-2 px-6 rounded-lg transition">
                        Ajouter une photo
                    </a>
                </div>
                @endif
            </div>

            <!-- Videos Section -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-purple-50 border-b border-purple-100">
                    <h2 class="text-xl font-bold text-purple-800 flex items-center gap-2">
                        <span>🎥</span> Vidéos
                    </h2>
                </div>
                
                @if($videos->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-6">
                    @foreach($videos as $video)
                    <div class="bg-gray-50 rounded-xl overflow-hidden border-2 {{ $video->is_active ? 'border-gray-200' : 'border-red-300 bg-red-50' }} hover:shadow-lg transition">
                        <div class="aspect-video relative bg-black">
                            @if($video->is_youtube && $video->youtube_id)
                            <img src="https://img.youtube.com/vi/{{ $video->youtube_id }}/hqdefault.jpg" 
                                 alt="{{ $video->title }}" 
                                 class="w-full h-full object-cover">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-16 h-16 bg-red-600 rounded-full flex items-center justify-center shadow-xl">
                                    <svg class="w-8 h-8 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z"/>
                                    </svg>
                                </div>
                            </div>
                            @elseif($video->thumbnail_url)
                            <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-800">
                                <span class="text-5xl">🎬</span>
                            </div>
                            @endif
                            @if(!$video->is_active)
                            <div class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-lg font-bold">Inactif</div>
                            @endif
                            <div class="absolute top-2 left-2 bg-black/70 text-white text-xs px-2 py-1 rounded-lg font-bold">
                                #{{ $video->sort_order }}
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-gray-800 truncate">{{ $video->title }}</h3>
                            @if($video->video_url)
                            <p class="text-xs text-blue-500 mt-1 truncate">{{ $video->video_url }}</p>
                            @endif
                            @if($video->bar)
                            <p class="text-xs text-gray-500 mt-1">📍 {{ $video->bar->name }}</p>
                            @endif
                            <div class="flex gap-2 mt-3">
                                <a href="{{ route('admin.edit-media', $video->id) }}" 
                                   class="flex-1 text-center bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm py-2 rounded-lg transition font-medium">
                                    Modifier
                                </a>
                                <form action="{{ route('admin.toggle-media', $video->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full {{ $video->is_active ? 'bg-yellow-100 hover:bg-yellow-200 text-yellow-700' : 'bg-green-100 hover:bg-green-200 text-green-700' }} text-sm py-2 rounded-lg transition font-medium">
                                        {{ $video->is_active ? 'Désactiver' : 'Activer' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.delete-media', $video->id) }}" method="POST" 
                                      onsubmit="return confirm('Supprimer cette vidéo ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-100 hover:bg-red-200 text-red-700 text-sm py-2 px-3 rounded-lg transition">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-8 text-center text-gray-500">
                    <span class="text-5xl block mb-4">🎥</span>
                    <p class="text-lg">Aucune vidéo pour le moment.</p>
                    <a href="{{ route('admin.create-media') }}" class="inline-block mt-4 bg-purple-100 hover:bg-purple-200 text-purple-700 font-bold py-2 px-6 rounded-lg transition">
                        Ajouter une vidéo
                    </a>
                </div>
                @endif
            </div>

            @endif

            <!-- Retour -->
            <div class="mt-8">
                <a href="/admin" class="inline-flex items-center gap-2 text-gray-600 hover:text-soboa-blue transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour au dashboard
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
