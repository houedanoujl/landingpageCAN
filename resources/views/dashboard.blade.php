<x-layouts.app title="Tableau de bord">
    <div class="bg-gray-50 min-h-screen">

        <!-- Header with Points -->
        <div class="bg-soboa-blue py-12 px-4">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <!-- User Info -->
                    <div class="text-center md:text-left">
                        <p class="text-white/60 text-sm uppercase tracking-widest mb-1">Bienvenue</p>
                        <h1 class="text-3xl md:text-4xl font-black text-white">{{ $user->name ?? 'Joueur' }}</h1>
                    </div>

                    <!-- Points Display -->
                    <div
                        class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20 text-center min-w-[200px]">
                        <p class="text-white/60 text-sm uppercase tracking-widest mb-1">Total points</p>
                        <p class="text-5xl font-black text-soboa-orange">{{ $user->points_total ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="max-w-7xl mx-auto px-4 -mt-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-4 shadow-lg text-center">
                    <span class="text-3xl">🏆</span>
                    <p class="text-2xl font-black text-soboa-blue mt-2">{{ $rank ?? '--' }}</p>
                    <p class="text-gray-500 text-sm">Classement</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-lg text-center">
                    <span class="text-3xl">⚽</span>
                    <p class="text-2xl font-black text-soboa-blue mt-2">{{ $predictionCount ?? 0 }}</p>
                    <p class="text-gray-500 text-sm">Pronostics</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-lg text-center">
                    <span class="text-3xl">🎯</span>
                    <p class="text-2xl font-black text-soboa-blue mt-2">{{ $correctPredictions ?? 0 }}</p>
                    <p class="text-gray-500 text-sm">Bons résultats</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-lg text-center">
                    <span class="text-3xl">📍</span>
                    <p class="text-2xl font-black text-soboa-blue mt-2">{{ $venueVisits ?? 0 }}</p>
                    <p class="text-gray-500 text-sm">Visites lieux</p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Next Match -->
                <div class="lg:col-span-2">
                    <h2 class="text-xl font-bold text-soboa-blue mb-4 flex items-center gap-2">
                        <span>📅</span> Prochain match
                    </h2>

                    @if($nextMatch)
                        <x-match-card :match="$nextMatch" />
                    @else
                        <div class="bg-white rounded-2xl p-8 shadow-lg text-center">
                            <span class="text-5xl block mb-4">⚽</span>
                            <p class="text-gray-600 font-medium">Aucun match à venir</p>
                            <p class="text-gray-400 text-sm">Revenez bientôt!</p>
                        </div>
                    @endif

                    <a href="/matches"
                        class="mt-4 inline-flex items-center gap-2 text-soboa-orange font-bold hover:underline">
                        Voir tous les matchs
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </a>
                </div>

                <!-- Quick Actions -->
                <div>
                    <h2 class="text-xl font-bold text-soboa-blue mb-4 flex items-center gap-2">
                        <span>⚡</span> Actions rapides
                    </h2>

                    <div class="space-y-3">
                        <a href="/matches"
                            class="block bg-soboa-orange text-white rounded-xl p-4 font-bold hover:bg-soboa-orange-dark transition-colors shadow-lg">
                            ⚽ Faire un pronostic
                        </a>
                        <a href="/map"
                            class="block bg-soboa-blue text-white rounded-xl p-4 font-bold hover:bg-soboa-blue-dark transition-colors shadow-lg">
                            📍 Lieux partenaires (+4 pts)
                        </a>
                        <a href="/leaderboard"
                            class="block bg-white text-soboa-blue rounded-xl p-4 font-bold hover:bg-gray-50 transition-colors shadow-lg border border-gray-200">
                            🏆 Voir le classement
                        </a>
                        <a href="/mes-pronostics"
                            class="block bg-white text-soboa-blue rounded-xl p-4 font-bold hover:bg-gray-50 transition-colors shadow-lg border border-gray-200">
                            📊 Historique pronostics
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Predictions -->
        <div class="max-w-7xl mx-auto px-4 pb-12">
            <h2 class="text-xl font-bold text-soboa-blue mb-4 flex items-center gap-2">
                <span>📜</span> Derniers pronostics
            </h2>

            @if($recentPredictions && $recentPredictions->count() > 0)
                <div class="space-y-3">
                    @foreach($recentPredictions as $prediction)
                        <div class="bg-white rounded-xl p-4 shadow-lg flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="text-center">
                                    <span
                                        class="text-sm font-bold text-gray-800">{{ $prediction->match->team_a ?? 'Équipe A' }}</span>
                                    <span class="text-lg font-black text-soboa-orange mx-2">{{ $prediction->score_a }} -
                                        {{ $prediction->score_b }}</span>
                                    <span
                                        class="text-sm font-bold text-gray-800">{{ $prediction->match->team_b ?? 'Équipe B' }}</span>
                                </div>
                            </div>
                            <div>
                                @if($prediction->points_earned !== null)
                                    @if($prediction->points_earned >= 6)
                                        <span
                                            class="bg-green-100 text-green-700 font-bold px-3 py-1 rounded-full text-sm">+{{ $prediction->points_earned }}
                                            pts 🏆</span>
                                    @elseif($prediction->points_earned > 0)
                                        <span
                                            class="bg-yellow-100 text-yellow-700 font-bold px-3 py-1 rounded-full text-sm">+{{ $prediction->points_earned }}
                                            pts</span>
                                    @else
                                        <span class="bg-gray-100 text-gray-500 font-bold px-3 py-1 rounded-full text-sm">+0 pts</span>
                                    @endif
                                @else
                                    <span class="bg-soboa-blue/10 text-soboa-blue font-bold px-3 py-1 rounded-full text-sm">En
                                        attente</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <a href="/mes-pronostics"
                    class="mt-4 inline-flex items-center gap-2 text-soboa-orange font-bold hover:underline">
                    Voir tout l'historique
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            @else
                <div class="bg-white rounded-xl p-8 shadow-lg text-center">
                    <span class="text-5xl block mb-4">📝</span>
                    <p class="text-gray-600 font-medium">Aucun pronostic pour le moment</p>
                    <a href="/matches" class="text-soboa-orange font-bold hover:underline">Faites votre premier
                        pronostic!</a>
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>