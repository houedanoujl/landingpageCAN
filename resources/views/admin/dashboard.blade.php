<x-layouts.app title="Admin - Dashboard">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                    <span class="text-4xl">⚙️</span> Dashboard Administrateur
                </h1>
                <p class="text-gray-600 mt-2">Gérez les matchs, les scores et les utilisateurs</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <span class="text-3xl">👥</span>
                    <p class="text-3xl font-black text-soboa-blue mt-2">{{ $stats['totalUsers'] }}</p>
                    <p class="text-gray-500 text-sm">Utilisateurs</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <span class="text-3xl">⚽</span>
                    <p class="text-3xl font-black text-soboa-blue mt-2">{{ $stats['totalMatches'] }}</p>
                    <p class="text-gray-500 text-sm">Total Matchs</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <span class="text-3xl">✅</span>
                    <p class="text-3xl font-black text-green-600 mt-2">{{ $stats['finishedMatches'] }}</p>
                    <p class="text-gray-500 text-sm">Matchs Terminés</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <span class="text-3xl">📅</span>
                    <p class="text-3xl font-black text-soboa-orange mt-2">{{ $stats['upcomingMatches'] }}</p>
                    <p class="text-gray-500 text-sm">À Venir</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <a href="{{ route('admin.matches') }}" class="bg-soboa-blue hover:bg-soboa-blue-dark text-white rounded-xl p-6 shadow-lg flex items-center gap-4 transition-colors">
                    <span class="text-4xl">📋</span>
                    <div>
                        <p class="font-bold text-lg">Gérer les Matchs</p>
                        <p class="text-white/70 text-sm">Modifier scores et statuts</p>
                    </div>
                </a>
                <a href="{{ route('admin.users') }}" class="bg-soboa-orange hover:bg-soboa-orange-dark text-white rounded-xl p-6 shadow-lg flex items-center gap-4 transition-colors">
                    <span class="text-4xl">👥</span>
                    <div>
                        <p class="font-bold text-lg">Utilisateurs</p>
                        <p class="text-white/70 text-sm">Voir tous les joueurs</p>
                    </div>
                </a>
                <a href="/leaderboard" class="bg-white hover:bg-gray-50 text-soboa-blue rounded-xl p-6 shadow-lg flex items-center gap-4 transition-colors border border-gray-200">
                    <span class="text-4xl">🏆</span>
                    <div>
                        <p class="font-bold text-lg">Classement</p>
                        <p class="text-gray-500 text-sm">Voir le leaderboard</p>
                    </div>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Matches -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-soboa-blue mb-4 flex items-center gap-2">
                        <span>⚽</span> Matchs Récents
                    </h2>
                    <div class="space-y-3">
                        @foreach($recentMatches as $match)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                @if($match->homeTeam)
                                <img src="https://flagcdn.com/w40/{{ $match->homeTeam->iso_code }}.png" class="w-8 h-6 rounded">
                                @endif
                                <span class="font-medium text-sm">{{ $match->team_a }}</span>
                                <span class="font-bold">
                                    @if($match->status === 'finished')
                                    {{ $match->score_a }} - {{ $match->score_b }}
                                    @else
                                    <span class="text-gray-400">vs</span>
                                    @endif
                                </span>
                                <span class="font-medium text-sm">{{ $match->team_b }}</span>
                                @if($match->awayTeam)
                                <img src="https://flagcdn.com/w40/{{ $match->awayTeam->iso_code }}.png" class="w-8 h-6 rounded">
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                @if($match->status === 'finished')
                                <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded">Terminé</span>
                                @else
                                <span class="bg-yellow-100 text-yellow-700 text-xs font-bold px-2 py-1 rounded">À venir</span>
                                @endif
                                <a href="{{ route('admin.edit-match', $match->id) }}" class="text-soboa-orange hover:underline text-sm font-bold">Modifier</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('admin.matches') }}" class="block mt-4 text-center text-soboa-orange font-bold hover:underline">
                        Voir tous les matchs →
                    </a>
                </div>

                <!-- Top Users -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-soboa-blue mb-4 flex items-center gap-2">
                        <span>🏆</span> Top 10 Joueurs
                    </h2>
                    <div class="space-y-2">
                        @foreach($topUsers as $index => $user)
                        <div class="flex items-center justify-between p-3 {{ $index < 3 ? 'bg-soboa-orange/10' : 'bg-gray-50' }} rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-lg w-8 text-center">
                                    @if($index == 0) 🥇 @elseif($index == 1) 🥈 @elseif($index == 2) 🥉 @else {{ $index + 1 }} @endif
                                </span>
                                <div class="w-10 h-10 bg-soboa-blue/20 rounded-full flex items-center justify-center font-bold text-soboa-blue">
                                    {{ mb_substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $user->phone_number }}</p>
                                </div>
                            </div>
                            <span class="font-black text-soboa-orange text-lg">{{ $user->points_total }} pts</span>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('admin.users') }}" class="block mt-4 text-center text-soboa-orange font-bold hover:underline">
                        Voir tous les utilisateurs →
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>
