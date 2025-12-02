<x-layouts.app title="Mes Pronostics">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-soboa-blue">Mes Pronostics</h1>
            @if(isset($user))
            <div class="text-right">
                <p class="text-sm text-gray-500">Connecté en tant que</p>
                <p class="font-bold text-soboa-blue">{{ $user->name }}</p>
                <p class="text-sm text-soboa-orange font-bold">{{ $user->points_total }} points</p>
            </div>
            @endif
        </div>

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg" role="alert">
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        @endif

        @if($predictions->isEmpty())
        <div class="bg-white rounded-xl shadow p-8 text-center">
            <div class="w-20 h-20 bg-soboa-blue/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-soboa-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">Aucun pronostic</h2>
            <p class="text-gray-600 mb-4">Vous n'avez pas encore fait de pronostic.</p>
            <a href="/matches" class="inline-block bg-soboa-orange hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-lg shadow transition">
                Voir les matchs
            </a>
        </div>
        @else
        <div class="grid gap-4">
            @foreach($predictions as $prediction)
            <div class="bg-white rounded-lg shadow p-5 border-l-4 {{ $prediction->points_earned > 0 ? 'border-green-500' : 'border-soboa-orange' }}">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <span class="text-xs text-gray-500">{{ $prediction->match->match_date->translatedFormat('l d F Y - H:i') }}</span>
                        <div class="text-sm text-gray-400">{{ $prediction->match->stadium }}</div>
                    </div>
                    @if($prediction->match->status === 'finished')
                        @if($prediction->points_earned > 0)
                        <span class="px-3 py-1 bg-green-100 text-green-700 text-sm font-bold rounded-full">
                            +{{ $prediction->points_earned }} pts
                        </span>
                        @else
                        <span class="px-3 py-1 bg-gray-200 text-gray-600 text-sm font-bold rounded-full">
                            0 pts
                        </span>
                        @endif
                    @else
                        <span class="px-3 py-1 bg-soboa-blue/10 text-soboa-blue text-sm font-bold rounded-full">
                            En attente
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
                        
                        @if($prediction->match->status === 'finished')
                        <div class="mt-2 pt-2 border-t">
                            <div class="text-lg font-bold text-gray-800">
                                {{ $prediction->match->score_a }} - {{ $prediction->match->score_b }}
                            </div>
                            <div class="text-xs text-gray-500">Score final</div>
                        </div>
                        @endif
                    </div>
                    
                    <div class="flex-1 text-center">
                        <span class="font-bold text-gray-800">{{ $prediction->match->team_b }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Statistiques -->
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-lg font-bold text-soboa-blue mb-4">Statistiques</h2>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold text-soboa-blue">{{ $predictions->count() }}</div>
                    <div class="text-sm text-gray-500">Pronostics</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-green-600">{{ $predictions->where('points_earned', '>', 0)->count() }}</div>
                    <div class="text-sm text-gray-500">Réussis</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-soboa-orange">{{ $predictions->sum('points_earned') }}</div>
                    <div class="text-sm text-gray-500">Points gagnés</div>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-layouts.app>
