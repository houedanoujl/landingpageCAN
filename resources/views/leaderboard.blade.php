<x-layouts.app title="Classement">
    <div class="space-y-6">
        <!-- Header -->
        <div class="relative py-8 px-6 rounded-2xl overflow-hidden mb-6 shadow-2xl text-center">
            <div class="absolute inset-0 z-0">
                <img src="/images/sen.webp" class="w-full h-full object-cover" alt="Background">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>
            </div>
            <div class="relative z-10">
                <h1 class="text-3xl md:text-4xl font-black text-white drop-shadow-2xl">🏆 Classement</h1>
                <p class="text-white/90 font-bold mt-2 uppercase tracking-widest text-xs">
                    {{ $period_label }}
                </p>
            </div>
        </div>

        <!-- Sélecteur de période -->
        <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-100">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-lg">📅</span>
                <span class="font-bold text-gray-700">Période</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('leaderboard', ['period' => 'global']) }}" 
                   class="px-4 py-2 rounded-full text-sm font-medium transition {{ $selected_period === 'global' ? 'bg-soboa-orange text-black' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Général
                </a>
                @foreach($available_periods as $key => $period)
                    <a href="{{ route('leaderboard', ['period' => $key]) }}" 
                       class="px-4 py-2 rounded-full text-sm font-medium transition {{ $selected_period === $key ? 'bg-soboa-orange text-black' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $period['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Info sur les gains -->
        @if(str_starts_with($selected_period, 'week_'))
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow-lg p-4 text-white">
            <div class="flex items-center gap-3">
                <span class="text-3xl">🎁</span>
                <div>
                    <p class="font-bold">Classement Hebdomadaire</p>
                    <p class="text-sm text-white/90">Les 5 premiers de cette semaine remportent des lots !</p>
                </div>
            </div>
        </div>
        @elseif($selected_period === 'semifinal')
        <div class="bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl shadow-lg p-4 text-white">
            <div class="flex items-center gap-3">
                <span class="text-3xl">🎟️</span>
                <div>
                    <p class="font-bold">Classement Spécial Demi-Finale</p>
                    <p class="text-sm text-white/90">Le numéro 1 gagne un billet pour la finale !</p>
                </div>
            </div>
        </div>
        @endif

        <!-- TOP 5 National -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
            <div class="bg-gradient-to-r from-soboa-blue to-soboa-blue/80 p-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <span>🏅</span> TOP 5 {{ str_starts_with($selected_period, 'week_') ? 'de la Semaine' : 'National' }}
                </h2>
                <p class="text-white/80 text-sm mt-1">
                    @if(str_starts_with($selected_period, 'week_'))
                        Points gagnés cette semaine
                    @else
                        Les meilleurs pronostiqueurs
                    @endif
                </p>
            </div>

            @if(count($top5) > 0)
                <!-- Podium visuel (Top 3) -->
                <div class="bg-soboa-orange p-6 pb-8">
                    <div class="flex justify-center items-end gap-4">
                        <!-- 2ème place -->
                        @if(isset($top5[1]))
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full border-4 border-gray-300 bg-gray-700 flex items-center justify-center text-lg font-bold text-white mb-2">
                                    {{ substr($top5[1]['name'], 0, 1) }}
                                </div>
                                <div class="text-center">
                                    <div class="font-bold text-sm text-black">{{ $top5[1]['name'] }}</div>
                                    <div class="text-black/60 text-xs">{{ $top5[1]['points'] }} pts</div>
                                </div>
                                <div class="h-16 w-14 bg-gradient-to-b from-gray-300 to-gray-400 mt-2 rounded-t-lg flex items-center justify-center text-xl font-bold text-gray-800 shadow-lg">
                                    2
                                </div>
                            </div>
                        @endif

                        <!-- 1ère place -->
                        @if(isset($top5[0]))
                            <div class="flex flex-col items-center z-10">
                                <div class="w-18 h-18 rounded-full border-4 border-yellow-400 bg-gray-700 flex items-center justify-center text-2xl font-bold text-yellow-400 mb-2" style="width: 4.5rem; height: 4.5rem;">
                                    {{ substr($top5[0]['name'], 0, 1) }}
                                </div>
                                <div class="text-center">
                                    <div class="font-bold text-base text-black">{{ $top5[0]['name'] }}</div>
                                    <div class="text-black/70 text-sm">{{ $top5[0]['points'] }} pts</div>
                                </div>
                                <div class="h-24 w-18 bg-gradient-to-b from-yellow-300 to-yellow-500 mt-2 rounded-t-lg flex items-center justify-center text-3xl font-bold text-yellow-900 shadow-lg" style="width: 4.5rem;">
                                    👑
                                </div>
                            </div>
                        @endif

                        <!-- 3ème place -->
                        @if(isset($top5[2]))
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full border-4 border-orange-400/50 bg-gray-700 flex items-center justify-center text-lg font-bold text-white mb-2">
                                    {{ substr($top5[2]['name'], 0, 1) }}
                                </div>
                                <div class="text-center">
                                    <div class="font-bold text-sm text-black">{{ $top5[2]['name'] }}</div>
                                    <div class="text-black/60 text-xs">{{ $top5[2]['points'] }} pts</div>
                                </div>
                                <div class="h-12 w-14 bg-gradient-to-b from-orange-300 to-orange-500 mt-2 rounded-t-lg flex items-center justify-center text-xl font-bold text-orange-900 shadow-lg">
                                    3
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 4ème et 5ème places -->
                @if(isset($top5[3]) || isset($top5[4]))
                    <div class="divide-y divide-gray-100">
                        @foreach([$top5[3] ?? null, $top5[4] ?? null] as $entry)
                            @if($entry)
                                <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                                    <div class="flex items-center gap-3">
                                        <span class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-600 text-sm">
                                            {{ $entry['rank'] }}
                                        </span>
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center font-bold text-gray-700">
                                            {{ substr($entry['name'], 0, 1) }}
                                        </div>
                                        <span class="font-medium text-gray-800">{{ $entry['name'] }}</span>
                                    </div>
                                    <span class="font-bold text-gray-700">{{ $entry['points'] }} pts</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            @else
                <div class="p-8 text-center text-gray-500">
                    <span class="text-4xl mb-2 block">📊</span>
                    <p>Aucun classement disponible pour cette période.</p>
                </div>
            @endif
        </div>

        <!-- TOP 20 National (positions 6-20) -->
        @if(count($top20) > 5)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                <div class="bg-soboa-blue p-4">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <span>📊</span> TOP 20 National
                    </h2>
                    <p class="text-white/80 text-sm mt-1">Positions 6 à 20</p>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach(array_slice($top20, 0) as $entry)
                        <div class="p-4 flex items-center justify-between hover:bg-gray-50 transition {{ auth()->check() && auth()->id() === ($entry['user_id'] ?? null) ? 'bg-soboa-orange/10 border-l-4 border-soboa-orange' : '' }}">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full {{ $entry['rank'] <= 10 ? 'bg-soboa-blue text-white' : 'bg-gray-200 text-gray-600' }} flex items-center justify-center font-bold text-sm">
                                    {{ $entry['rank'] }}
                                </span>
                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center font-bold text-gray-700">
                                    {{ substr($entry['name'], 0, 1) }}
                                </div>
                                <span class="font-medium text-gray-800">{{ $entry['name'] }}</span>
                            </div>
                            <span class="font-bold text-gray-700">{{ $entry['points'] }} pts</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Position personnelle de l'utilisateur -->
        @if($user_position)
            <div class="bg-gradient-to-r from-soboa-blue to-soboa-blue/90 rounded-xl shadow-lg p-6 text-white">
                <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <span>📍</span> Votre position
                </h3>
                
                <div class="bg-white/10 rounded-xl p-4 backdrop-blur">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-soboa-orange flex items-center justify-center text-2xl font-bold text-black">
                                #{{ $user_position['rank'] }}
                            </div>
                            <div>
                                <div class="text-xl font-bold">{{ $user_position['points'] }} points</div>
                                <div class="text-white/70 text-sm">
                                    @if($user_in_top5)
                                        🎉 Vous êtes dans le TOP 5 !
                                    @else
                                        sur {{ $user_position['total_users'] }} participants
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        @if(!$user_in_top5 && $user_position['rank'] <= 10)
                            <div class="text-right">
                                <div class="text-soboa-orange font-bold">Presque !</div>
                                <div class="text-white/70 text-xs">Plus que {{ $user_position['rank'] - 5 }} place(s)</div>
                            </div>
                        @endif
                    </div>
                </div>

                @if(!$user_in_top5)
                    <div class="mt-4 text-center">
                        <p class="text-white/80 text-sm">
                            💡 Continuez à pronostiquer et visitez les lieux partenaires pour gagner des points !
                        </p>
                    </div>
                @endif
            </div>
        @else
            <!-- Message pour les utilisateurs non connectés -->
            <div class="bg-gray-100 rounded-xl p-6 text-center">
                <span class="text-4xl mb-2 block">🔒</span>
                <p class="text-gray-600 mb-4">Connectez-vous pour voir votre position dans le classement</p>
                <a href="/login" class="inline-block bg-soboa-orange text-black font-bold px-6 py-3 rounded-full hover:bg-soboa-orange/90 transition">
                    Se connecter
                </a>
            </div>
        @endif

        <!-- Informations sur les récompenses -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span>🎁</span> Récompenses
            </h3>
            
            <div class="space-y-4">
                <!-- Classement hebdomadaire -->
                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg p-4 border border-yellow-200">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl">📅</span>
                        <div>
                            <h4 class="font-bold text-gray-800">Classement Hebdomadaire</h4>
                            <p class="text-gray-600 text-sm mt-1">
                                Chaque semaine, les <strong>5 premiers</strong> du classement sont déclarés gagnants.
                            </p>
                            <p class="text-gray-500 text-xs mt-2">
                                → 20 gagnants au total (4 semaines × 5 gagnants)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Classement demi-finale -->
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl">🏆</span>
                        <div>
                            <h4 class="font-bold text-gray-800">Classement Spécial Demi-finale</h4>
                            <p class="text-gray-600 text-sm mt-1">
                                Après les demi-finales, un classement global est recalculé.
                            </p>
                            <p class="text-soboa-blue font-bold text-sm mt-2">
                                🎫 Le N°1 gagne un billet pour la finale !
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Légende des points -->
        <div class="bg-gradient-to-r from-soboa-blue/5 to-soboa-orange/5 rounded-xl p-4 border border-gray-200">
            <h3 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                <span>💡</span> Comment gagner des points ?
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs">
                <span class="bg-white px-3 py-2 rounded-lg border border-gray-200 flex items-center gap-2">
                    <span>🔑</span> +1 pt/connexion/jour
                </span>
                <span class="bg-white px-3 py-2 rounded-lg border border-gray-200 flex items-center gap-2">
                    <span>⚽</span> +1 pt/pronostic
                </span>
                <span class="bg-white px-3 py-2 rounded-lg border border-gray-200 flex items-center gap-2">
                    <span>🎯</span> +3 pts/bon vainqueur
                </span>
                <span class="bg-white px-3 py-2 rounded-lg border border-gray-200 flex items-center gap-2">
                    <span>🏆</span> +3 pts/score exact
                </span>
                <span class="bg-white px-3 py-2 rounded-lg border border-gray-200 flex items-center gap-2">
                    <span>📍</span> +4 pts/visite lieu
                </span>
            </div>
        </div>
    </div>
</x-layouts.app>