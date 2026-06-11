<x-layouts.app title="Admin - Google Analytics">
    <div class="bg-gray-100 min-h-screen flex flex-col">

        <!-- Header compact -->
        <div class="bg-white shadow-sm px-4 py-3 flex items-center justify-between flex-wrap gap-2">
            <h1 class="text-xl font-bold text-soboa-blue flex items-center gap-2">
                <span>📊</span> Google Analytics
                <span class="text-xs font-mono font-normal text-gray-500 bg-gray-100 rounded px-2 py-1">{{ $gaPropertyId }}</span>
            </h1>
            <div class="flex items-center gap-2">
                <a href="https://analytics.google.com/" target="_blank" rel="noopener"
                   class="bg-soboa-orange hover:bg-soboa-orange/90 text-white px-4 py-2 rounded-lg transition flex items-center gap-2 text-sm font-bold">
                    Ouvrir Google Analytics ↗
                </a>
                <a href="{{ route('admin.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Retour
                </a>
            </div>
        </div>

        @if($gaEmbedUrl)
            <!-- Rapport Looker Studio intégré (branché sur la propriété {{ $gaPropertyId }}) -->
            <div class="flex-1 p-2">
                <iframe
                    width="100%"
                    height="100%"
                    src="{{ $gaEmbedUrl }}"
                    frameborder="0"
                    style="border:0; min-height: calc(100vh - 80px);"
                    allowfullscreen
                    sandbox="allow-storage-access-by-user-activation allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox">
                </iframe>
            </div>
        @else
            <!-- Pas encore de rapport intégré pour cette propriété -->
            <div class="flex-1 flex items-center justify-center p-6">
                <div class="bg-white rounded-2xl shadow-lg p-8 max-w-2xl text-center">
                    <div class="text-5xl mb-4">📈</div>
                    <h2 class="text-2xl font-black text-soboa-blue mb-3">Suivi actif sur {{ $gaPropertyId }}</h2>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        Le tag Google Analytics est installé sur tout le site public : les visites remontent
                        en direct dans la propriété <strong class="font-mono">{{ $gaPropertyId }}</strong>.
                        Pour afficher le rapport directement dans cet onglet :
                    </p>
                    <ol class="text-left text-gray-700 space-y-2 mb-8 list-decimal list-inside">
                        <li>Créer un rapport <strong>Looker Studio</strong> branché sur cette propriété GA4 ;</li>
                        <li>Fichier → Intégrer le rapport → copier l'URL d'intégration ;</li>
                        <li>L'ajouter au fichier <code class="bg-gray-100 px-2 py-0.5 rounded text-sm">.env</code> :
                            <code class="block bg-gray-100 rounded px-3 py-2 mt-1 text-sm font-mono">GOOGLE_ANALYTICS_EMBED_URL=https://lookerstudio.google.com/embed/reporting/…</code>
                        </li>
                        <li>Puis <code class="bg-gray-100 px-2 py-0.5 rounded text-sm font-mono">php artisan config:clear</code>.</li>
                    </ol>
                    <a href="https://analytics.google.com/" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 bg-soboa-blue hover:bg-soboa-blue/90 text-white font-bold py-3 px-8 rounded-xl transition">
                        Consulter les données en attendant ↗
                    </a>
                </div>
            </div>
        @endif

    </div>
</x-layouts.app>
