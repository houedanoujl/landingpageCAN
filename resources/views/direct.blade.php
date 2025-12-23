<x-layouts.app title="Direct - Scores en Live">
    <div class="min-h-screen bg-gray-50">
        
        <!-- Header -->
        <div class="relative py-12 px-4 overflow-hidden mb-8 shadow-2xl">
            <!-- Background Image with Overlay -->
            <div class="absolute inset-0 z-0">
                <img src="/images/sen.webp" class="w-full h-full object-cover" alt="Background">
                <div class="absolute inset-0 bg-gradient-to-r from-red-900/90 to-black/80"></div>
            </div>
            <div class="relative z-10 max-w-7xl mx-auto text-center">
                <div class="inline-flex items-center gap-2 bg-red-600 text-white px-4 py-1.5 rounded-full text-sm font-bold mb-4 animate-pulse">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
                    </span>
                    EN DIRECT
                </div>
                <h1 class="text-4xl md:text-5xl font-black text-white mt-2 drop-shadow-2xl flex items-center justify-center gap-3">
                    <span class="text-5xl">⚽</span> SCORES EN LIVE
                </h1>
                <p class="text-white/80 mt-4 max-w-2xl mx-auto font-medium drop-shadow-lg">
                    Suivez tous les scores en direct et les résultats des matchs
                </p>
            </div>
        </div>

        <!-- Widget Container -->
        <div class="max-w-7xl mx-auto px-4 pb-12">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                
                <!-- Section Header -->
                <div class="bg-gradient-to-r from-red-600 to-red-800 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center relative">
                            <span class="text-2xl">🏆</span>
                            <span class="absolute -top-1 -right-1 flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-white"></span>
                            </span>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                                Scores en Direct
                                <span class="bg-white text-red-600 text-xs font-black px-2 py-0.5 rounded animate-pulse">LIVE</span>
                            </h2>
                            <p class="text-white/70 text-sm">Résultats et classements en temps réel</p>
                        </div>
                    </div>
                </div>

                <!-- Livescore Widget -->
                <div class="p-4 md:p-6">
                    <div class="min-h-[600px] flex items-center justify-center" id="widget-container">
                        <!-- LIVESCORE WIDGET SOCCERSAPI.COM -->
                        <div id="ls-widget" data-w="wo_w694a87da9ca15a79c8eb5f86_694a8824a93b2" class="livescore-widget w-full"></div>
                        <!-- LIVESCORE WIDGET SOCCERSAPI.COM -->
                    </div>
                </div>

                <!-- Footer Info -->
                <div class="bg-gray-50 border-t border-gray-200 px-6 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-2 text-gray-600 text-sm">
                            <span class="text-lg">📊</span>
                            <span>Données mises à jour en temps réel</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <a href="/matches" class="text-soboa-blue hover:text-soboa-blue/80 font-bold text-sm transition-colors flex items-center gap-1">
                                <span>🎯</span> Faire un pronostic
                            </a>
                            <a href="/leaderboard" class="text-soboa-orange hover:text-soboa-orange/80 font-bold text-sm transition-colors flex items-center gap-1">
                                <span>🏅</span> Voir le classement
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                <!-- Card 1 -->
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-14 h-14 bg-soboa-orange/10 rounded-full flex items-center justify-center">
                            <span class="text-3xl">🎯</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800">Pronostiquez</h3>
                            <p class="text-gray-500 text-sm">Gagnez des points</p>
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">
                        Faites vos pronostics sur les matchs et gagnez jusqu'à 7 points par match !
                    </p>
                    <a href="/matches" class="inline-flex items-center gap-2 text-soboa-blue font-bold text-sm hover:text-soboa-blue/80 transition-colors">
                        Voir les matchs <span>→</span>
                    </a>
                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-3xl">📍</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800">Points de Vente</h3>
                            <p class="text-gray-500 text-sm">+4 points bonus</p>
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">
                        Visitez nos points de vente partenaires et gagnez 4 points bonus par jour !
                    </p>
                    <a href="/map" class="inline-flex items-center gap-2 text-green-600 font-bold text-sm hover:text-green-700 transition-colors">
                        Trouver un lieu <span>→</span>
                    </a>
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center">
                            <span class="text-3xl">🎉</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800">Animations</h3>
                            <p class="text-gray-500 text-sm">Vivez l'expérience</p>
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">
                        Découvrez les animations dans les points de vente partenaires !
                    </p>
                    <a href="/animations" class="inline-flex items-center gap-2 text-purple-600 font-bold text-sm hover:text-purple-700 transition-colors">
                        Voir le calendrier <span>→</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Livescore Widget Script -->
    <script type="text/javascript" src="https://ls.soccersapi.com/widget/res/wo_w694a87da9ca15a79c8eb5f86_694a8824a93b2/widget.js"></script>

    <style>
        /* Ensure widget is responsive */
        #ls-widget {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        #ls-widget iframe {
            width: 100% !important;
            min-height: 600px;
        }
        
        .livescore-widget {
            width: 100% !important;
        }
    </style>
</x-layouts.app>
