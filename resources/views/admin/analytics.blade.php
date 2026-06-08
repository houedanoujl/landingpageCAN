<x-layouts.app title="Admin - Google Analytics">
    <div class="bg-gray-100 min-h-screen flex flex-col">
        
        <!-- Header compact -->
        <div class="bg-white shadow-sm px-4 py-3 flex items-center justify-between">
            <h1 class="text-xl font-bold text-soboa-blue flex items-center gap-2">
                <span></span> Rapport Google Analytics Intégré
            </h1>
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Retour
            </a>
        </div>

        <!-- Iframe Looker Studio pleine hauteur -->
        <div class="flex-1 p-2">
            <iframe 
                width="100%" 
                height="100%"
                src="https://lookerstudio.google.com/embed/reporting/51e6fb8a-ffbc-4e6a-8582-5366f4f14643/page/p_kx1rbu54bd" 
                frameborder="0" 
                style="border:0; min-height: calc(100vh - 80px);" 
                allowfullscreen 
                sandbox="allow-storage-access-by-user-activation allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox">
            </iframe>
        </div>

    </div>
</x-layouts.app>
