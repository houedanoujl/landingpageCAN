<x-layouts.app title="Mes Pronostics">
    <style>
        @keyframes pulse-live {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .live-indicator {
            animation: pulse-live 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }

            100% {
                background-position: 1000px 0;
            }
        }

        .live-card {
            background: linear-gradient(90deg, #ffffff 0%, #fef3c7 50%, #ffffff 100%);
            background-size: 2000px 100%;
            animation: shimmer 3s infinite linear;
        }
    </style>

    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h1 class="text-3xl font-bold text-soboa-blue">Mes Pronostics</h1>
            @if(isset($user))
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-1.5 flex items-center gap-3 pr-6 self-start md:self-auto">
                    <div
                        class="bg-gradient-to-br from-soboa-blue to-blue-800 w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-md">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div class="text-left">
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-0.5">Joueur</p>
                        <p class="font-black text-gray-800 leading-none text-sm md:text-base">{{ $user->name }}</p>
                    </div>
                    <div class="ml-2 pl-4 border-l border-gray-100 text-center min-w-[60px]">
                        <span
                            class="block font-black text-soboa-orange text-xl leading-none">{{ $user->points_total }}</span>
                        <span class="text-[10px] text-gray-400 font-bold uppercase">pts</span>
                    </div>
                </div>
            @endif
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg" role="alert">
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if($totalPredictions == 0)
            <div class="bg-white rounded-xl shadow p-8 text-center">
                <div class="w-20 h-20 bg-soboa-blue/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-soboa-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Aucun pronostic</h2>
                <p class="text-gray-600 mb-4">Vous n'avez pas encore fait de pronostic.</p>
                <a href="/matches"
                    class="inline-block bg-soboa-orange hover:bg-orange-600 text-black font-bold py-3 px-6 rounded-lg shadow transition">
                    Voir les matchs
                </a>
            </div>
        @else

            <!-- Matchs en cours (LIVE) -->
            @if($livePredictions->count() > 0)
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-bold text-red-600">En cours</h2>
                        <span class="relative flex h-3 w-3">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                        </span>
                        <span
                            class="px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">{{ $livePredictions->count() }}</span>
                    </div>

                    <div class="grid gap-4">
                        @foreach($livePredictions as $prediction)
                            <div
                                class="live-card bg-white rounded-lg shadow-lg p-5 border-l-4 border-red-500 relative overflow-hidden">
                                <div class="relative z-10">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <span
                                                class="text-xs text-gray-500">{{ $prediction->match->match_date->translatedFormat('l d F Y - H:i') }}</span>
                                            <div class="text-sm text-gray-400">{{ $prediction->match->stadium }}</div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="live-indicator px-3 py-1 bg-red-600 text-white text-sm font-bold rounded-full flex items-center gap-1">
                                                <span class="w-2 h-2 bg-white rounded-full"></span>
                                                LIVE
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 text-center">
                                            <span class="font-bold text-gray-800">{{ $prediction->match->team_a }}</span>
                                        </div>

                                        <div class="px-4 text-center">
                                            <div class="text-lg font-bold text-soboa-orange">
                                                {{ $prediction->score_a }} - {{ $prediction->score_b }}
                                            </div>
                                            <div class="text-xs text-gray-500">Votre pronostic</div>

                                            @if($prediction->match->score_a !== null && $prediction->match->score_b !== null)
                                                <div class="mt-2 pt-2 border-t">
                                                    <div class="text-lg font-bold text-red-600">
                                                        {{ $prediction->match->score_a }} - {{ $prediction->match->score_b }}
                                                    </div>
                                                    <div class="text-xs text-red-500 font-bold">Score actuel</div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex-1 text-center">
                                            <span class="font-bold text-gray-800">{{ $prediction->match->team_b }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Matchs à venir -->
            @if($scheduledPredictions->count() > 0)
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-bold text-soboa-blue">À venir</h2>
                        <span
                            class="px-2 py-1 bg-soboa-blue/10 text-soboa-blue text-xs font-bold rounded-full">{{ $scheduledPredictions->count() }}</span>
                    </div>

                    <div class="grid gap-4">
                        @foreach($scheduledPredictions as $prediction)
                            <div class="bg-white rounded-lg shadow p-5 border-l-4 border-soboa-blue">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <span
                                            class="text-xs text-gray-500">{{ $prediction->match->match_date->translatedFormat('l d F Y - H:i') }}</span>
                                        <div class="text-sm text-gray-400">{{ $prediction->match->stadium }}</div>
                                    </div>
                                    <span class="px-3 py-1 bg-soboa-blue/10 text-soboa-blue text-sm font-bold rounded-full">
                                        En attente
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="flex-1 text-center">
                                        <span class="font-bold text-gray-800">{{ $prediction->match->team_a }}</span>
                                    </div>

                                    <div class="px-4 text-center">
                                        <div class="text-lg font-bold text-soboa-orange">
                                            {{ $prediction->score_a }} - {{ $prediction->score_b }}
                                        </div>
                                        <div class="text-xs text-gray-500">Votre pronostic</div>
                                    </div>

                                    <div class="flex-1 text-center">
                                        <span class="font-bold text-gray-800">{{ $prediction->match->team_b }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Matchs terminés -->
            @if($finishedPredictions->count() > 0)
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-bold text-gray-700">Terminés</h2>
                        <span
                            class="px-2 py-1 bg-gray-200 text-gray-700 text-xs font-bold rounded-full">{{ $finishedPredictions->count() }}</span>
                    </div>

                    <div class="grid gap-4">
                        @foreach($finishedPredictions as $prediction)
                            <div
                                class="bg-white rounded-lg shadow p-5 border-l-4 {{ $prediction->points_earned > 0 ? 'border-green-500' : 'border-gray-300' }}">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <span
                                            class="text-xs text-gray-500">{{ $prediction->match->match_date->translatedFormat('l d F Y - H:i') }}</span>
                                        <div class="text-sm text-gray-400">{{ $prediction->match->stadium }}</div>
                                    </div>
                                    @if($prediction->points_earned > 0)
                                        <span class="px-3 py-1 bg-green-100 text-green-700 text-sm font-bold rounded-full">
                                            +{{ $prediction->points_earned }} pts
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-gray-200 text-gray-600 text-sm font-bold rounded-full">
                                            0 pts
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="flex-1 text-center">
                                        <span class="font-bold text-gray-800">{{ $prediction->match->team_a }}</span>
                                    </div>

                                    <div class="px-4 text-center">
                                        <div class="text-lg font-bold text-soboa-orange">
                                            {{ $prediction->score_a }} - {{ $prediction->score_b }}
                                        </div>
                                        <div class="text-xs text-gray-500">Votre pronostic</div>

                                        <div class="mt-2 pt-2 border-t">
                                            <div class="text-lg font-bold text-gray-800">
                                                {{ $prediction->match->score_a }} - {{ $prediction->match->score_b }}
                                            </div>
                                            <div class="text-xs text-gray-500">Score final</div>
                                        </div>
                                    </div>

                                    <div class="flex-1 text-center">
                                        <span class="font-bold text-gray-800">{{ $prediction->match->team_b }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Statistiques -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-bold text-soboa-blue mb-4">Statistiques</h2>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-soboa-blue">{{ $totalPredictions }}</div>
                        <div class="text-sm text-gray-500">Pronostics</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-green-600">{{ $successfulPredictions }}</div>
                        <div class="text-sm text-gray-500">Réussis</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-soboa-orange">{{ $totalPointsEarned }}</div>
                        <div class="text-sm text-gray-500">Points gagnés</div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>