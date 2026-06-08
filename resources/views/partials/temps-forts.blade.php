{{-- Section Temps Forts - Grille Simple --}}
@php
    // Récupérer tous les médias actifs (photos et vidéos) avec le bar associé
    $allMedia = \App\Models\AnimationMedia::with('bar')
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('created_at', 'desc')
        ->get();
@endphp

@if($allMedia->count() > 0)
<section id="temps-forts" class="py-8 md:py-12 bg-gradient-to-b from-gray-900 to-green-900/20"
         x-data="{
            lightboxOpen: false,
            currentType: '',
            currentUrl: '',
            currentTitle: '',
            currentVideoType: '',
            
            openMedia(type, url, title, videoType = 'local') {
                this.currentType = type;
                this.currentUrl = url;
                this.currentTitle = title;
                this.currentVideoType = videoType;
                this.lightboxOpen = true;
                document.body.style.overflow = 'hidden';
            },
            
            closeLightbox() {
                this.lightboxOpen = false;
                document.body.style.overflow = 'auto';
                // Reset pour arrêter les vidéos
                setTimeout(() => {
                    this.currentUrl = '';
                    this.currentType = '';
                }, 300);
            }
         }"
         @keydown.escape.window="closeLightbox()">
    
    <div class="container mx-auto px-4">
        
        {{-- Titre --}}
        <div class="text-center mb-8">
            <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">
                <span class="text-yellow-400"></span> Temps Forts
            </h2>
            <p class="text-gray-400">Les meilleurs moments de nos animations</p>
        </div>

        {{-- Grille de médias --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 md:gap-3">
            @foreach($allMedia as $media)
            <div class="relative aspect-square group cursor-pointer overflow-hidden rounded-lg bg-gray-800"
                 @click="openMedia(
                     '{{ $media->type }}',
                     '{{ $media->type === 'video' ? ($media->is_youtube ? 'https://www.youtube.com/embed/' . $media->youtube_id . '?autoplay=1' : ($media->is_facebook ? $media->facebook_embed_url : ($media->is_tiktok ? $media->video_url : Storage::url($media->file_path)))) : Storage::url($media->file_path) }}',
                     '{{ addslashes($media->title ?? '') }}',
                     '{{ $media->video_platform ?? 'local' }}'
                 )">
                
                {{-- Thumbnail --}}
                @if($media->type === 'photo')
                    <img src="{{ Storage::url($media->file_path) }}" 
                         alt="{{ $media->title ?? 'Photo' }}"
                         class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                         loading="lazy">
                @elseif($media->is_youtube)
                    <img src="https://img.youtube.com/vi/{{ $media->youtube_id }}/mqdefault.jpg" 
                         alt="{{ $media->title ?? 'Vidéo' }}"
                         class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                         onerror="this.src='https://img.youtube.com/vi/{{ $media->youtube_id }}/default.jpg'"
                         loading="lazy">
                @elseif($media->is_facebook)
                    <div class="w-full h-full bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center">
                        <svg class="w-12 h-12 text-white/80" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </div>
                @elseif($media->is_tiktok)
                    <div class="w-full h-full bg-gradient-to-br from-pink-500 via-purple-500 to-cyan-400 flex items-center justify-center">
                        <svg class="w-12 h-12 text-white/80" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                        </svg>
                    </div>
                @else
                    {{-- Vidéo locale - thumbnail ou placeholder --}}
                    @if($media->thumbnail_path)
                        <img src="{{ Storage::url($media->thumbnail_path) }}" 
                             alt="{{ $media->title ?? 'Vidéo' }}"
                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                             loading="lazy">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-green-600 to-green-800 flex items-center justify-center">
                            <svg class="w-12 h-12 text-white/80" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                    @endif
                @endif
                
                {{-- Overlay au hover --}}
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all duration-300 flex items-center justify-center">
                    @if($media->type === 'video')
                        {{-- Icône play pour vidéos --}}
                        <div class="w-14 h-14 bg-yellow-500/90 rounded-full flex items-center justify-center opacity-80 group-hover:opacity-100 group-hover:scale-110 transition-all duration-300 shadow-lg">
                            <svg class="w-7 h-7 text-black ml-1" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                    @else
                        {{-- Icône zoom pour photos --}}
                        <svg class="w-10 h-10 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                        </svg>
                    @endif
                </div>
                
                {{-- Badge type de vidéo --}}
                @if($media->type === 'video')
                <div class="absolute top-2 right-2">
                    @if($media->is_youtube)
                        <span class="bg-red-600 text-white text-xs font-bold px-2 py-1 rounded">YT</span>
                    @elseif($media->is_facebook)
                        <span class="bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded">FB</span>
                    @elseif($media->is_tiktok)
                        <span class="bg-black text-white text-xs font-bold px-2 py-1 rounded">TT</span>
                    @endif
                </div>
                @endif
                
                {{-- Titre et lieu en bas --}}
                @if($media->title || $media->bar)
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-2">
                    @if($media->title)
                    <p class="text-white text-xs md:text-sm truncate font-medium">{{ $media->title }}</p>
                    @endif
                    @if($media->bar)
                    <p class="text-yellow-400 text-xs truncate flex items-center gap-1">
                        <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        {{ $media->bar->name }}
                    </p>
                    @endif
                </div>
                @endif
            </div>
            @endforeach
        </div>

    </div>

    {{-- Lightbox --}}
    <div x-show="lightboxOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/95 backdrop-blur-sm p-4"
         @click.self="closeLightbox()">
        
        {{-- Bouton fermer --}}
        <button @click="closeLightbox()" 
                class="absolute top-4 right-4 text-white hover:text-yellow-400 transition-colors z-20">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        
        {{-- Titre --}}
        <div x-show="currentTitle" class="absolute top-4 left-4 text-white text-lg font-semibold max-w-[70%] truncate z-20">
            <span x-text="currentTitle"></span>
        </div>
        
        {{-- Contenu --}}
        <div class="w-full max-w-5xl max-h-[90vh]">
            
            {{-- Photo --}}
            <template x-if="currentType === 'photo'">
                <img :src="currentUrl" 
                     class="max-h-[85vh] max-w-full mx-auto object-contain rounded-lg shadow-2xl">
            </template>
            
            {{-- Vidéo YouTube / Facebook --}}
            <template x-if="currentType === 'video' && (currentVideoType === 'youtube' || currentVideoType === 'facebook')">
                <div class="aspect-video w-full bg-black rounded-lg overflow-hidden shadow-2xl">
                    <iframe :src="currentUrl"
                            class="w-full h-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen>
                    </iframe>
                </div>
            </template>
            
            {{-- Vidéo TikTok --}}
            <template x-if="currentType === 'video' && currentVideoType === 'tiktok'">
                <div class="aspect-video w-full bg-gradient-to-br from-pink-500 via-purple-500 to-cyan-500 rounded-lg flex items-center justify-center shadow-2xl">
                    <div class="text-center text-white p-6">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                        </svg>
                        <p class="text-lg font-semibold mb-4">Vidéo TikTok</p>
                        <a :href="currentUrl" 
                           target="_blank" 
                           class="inline-flex items-center gap-2 bg-white text-black px-6 py-3 rounded-full font-semibold hover:bg-yellow-400 transition-colors">
                            Voir sur TikTok
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </template>
            
            {{-- Vidéo locale --}}
            <template x-if="currentType === 'video' && currentVideoType === 'local'">
                <div class="aspect-video w-full bg-black rounded-lg overflow-hidden shadow-2xl">
                    <video :src="currentUrl"
                           class="w-full h-full"
                           controls
                           autoplay>
                        Votre navigateur ne supporte pas la lecture de vidéos.
                    </video>
                </div>
            </template>
            
        </div>
    </div>
</section>
@endif
