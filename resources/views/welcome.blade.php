<x-layouts.app title="Accueil">
    <div class="space-y-8">

        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-soboa-blue to-soboa-blue/80 rounded-xl shadow-2xl p-6 md:p-10 text-white relative overflow-hidden">
            <div class="relative z-10">
                <h1 class="text-3xl md:text-5xl font-extrabold mb-4">🏆 CAN 2025 - Maroc</h1>
                <p class="text-lg md:text-xl text-soboa-orange mb-6">Prédisez les scores, gagnez des points et devenez le meilleur pronostiqueur!</p>
                @if(session('user_id'))
                <a href="/matches" class="bg-soboa-orange text-white font-bold py-3 px-6 rounded-full hover:bg-orange-600 transition shadow-lg inline-block">
                    Faire un pronostic
                </a>
                @else
                <a href="/login" class="bg-soboa-orange text-white font-bold py-3 px-6 rounded-full hover:bg-orange-600 transition shadow-lg inline-block">
                    S'inscrire pour jouer
                </a>
                @endif
            </div>
            <!-- Decorative circle -->
            <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-soboa-orange rounded-full opacity-20 blur-2xl"></div>
        </div>

        <!-- Live / Next Match -->
        <section>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-soboa-blue border-l-4 border-soboa-orange pl-3">Prochains Matchs</h2>
                <a href="/matches" class="text-soboa-orange font-semibold hover:underline">Voir tout →</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($upcomingMatches as $match)
                <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-soboa-orange hover:shadow-lg transition">
                    <div class="flex justify-between text-xs text-gray-500 mb-2">
                        <span class="font-bold text-soboa-blue">{{ $match->match_date->translatedFormat('d M H:i') }}</span>
                        <span class="bg-soboa-blue/10 text-soboa-blue px-2 py-0.5 rounded text-xs font-bold">Groupe {{ $match->group_name }}</span>
                    </div>
                    <div class="text-xs text-gray-400 mb-3">📍 {{ $match->stadium }}</div>
                    <div class="flex justify-between items-center my-4">
                        <div class="text-center w-1/3">
                            <div class="w-12 h-12 bg-soboa-blue/10 rounded-full mx-auto mb-2 flex items-center justify-center text-xs font-bold text-soboa-blue">
                                {{ mb_substr($match->team_a, 0, 2) }}
                            </div>
                            <span class="font-bold text-gray-800 block truncate text-sm">{{ $match->team_a }}</span>
                        </div>
                        <div class="text-center w-1/3 text-2xl font-black text-gray-300">
                            VS
                        </div>
                        <div class="text-center w-1/3">
                            <div class="w-12 h-12 bg-soboa-blue/10 rounded-full mx-auto mb-2 flex items-center justify-center text-xs font-bold text-soboa-blue">
                                {{ mb_substr($match->team_b, 0, 2) }}
                            </div>
                            <span class="font-bold text-gray-800 block truncate text-sm">{{ $match->team_b }}</span>
                        </div>
                    </div>
                    @if(session('user_id'))
                    <a href="/matches" class="block w-full mt-2 bg-soboa-orange text-white py-2 rounded hover:bg-orange-600 font-semibold transition text-sm text-center">
                        Pronostiquer
                    </a>
                    @else
                    <a href="/login" class="block w-full mt-2 bg-soboa-blue text-white py-2 rounded hover:bg-blue-800 font-semibold transition text-sm text-center">
                        Se connecter
                    </a>
                    @endif
                </div>
                @empty
                <div class="col-span-3 text-center py-10 bg-white rounded-lg">
                    <p class="text-gray-500">Aucun match programmé pour le moment.</p>
                </div>
                @endforelse
            </div>
        </section>

        <!-- Mini Leaderboard -->
        <section>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-soboa-blue border-l-4 border-soboa-orange pl-3">Meilleurs Pronostiqueurs</h2>
                <a href="/leaderboard" class="text-soboa-orange font-semibold hover:underline">Classement complet →</a>
            </div>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-soboa-blue text-white text-sm uppercase">
                        <tr>
                            <th class="px-6 py-3">#</th>
                            <th class="px-6 py-3">Joueur</th>
                            <th class="px-6 py-3 text-right">Points</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($topUsers as $index => $user)
                        <tr class="hover:bg-soboa-blue/5">
                            <td class="px-6 py-4 font-bold {{ $index == 0 ? 'text-soboa-orange' : 'text-gray-600' }}">
                                @if($index == 0) 🥇 @elseif($index == 1) 🥈 @elseif($index == 2) 🥉 @else {{ $index + 1 }} @endif
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-800">
                                {{ $user->name }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-soboa-orange">
                                {{ $user->points_total }} pts
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                Aucun joueur inscrit pour le moment. <a href="/login" class="text-soboa-orange hover:underline">Soyez le premier !</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Comment ça marche -->
        <section class="bg-white rounded-xl shadow p-6">
            <h2 class="text-2xl font-bold text-soboa-blue mb-6 text-center">Comment ça marche ?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-soboa-orange/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl">📱</span>
                    </div>
                    <h3 class="font-bold text-soboa-blue mb-2">1. Inscrivez-vous</h3>
                    <p class="text-gray-600 text-sm">Créez votre compte avec votre numéro de téléphone</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-soboa-orange/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl">⚽</span>
                    </div>
                    <h3 class="font-bold text-soboa-blue mb-2">2. Pronostiquez</h3>
                    <p class="text-gray-600 text-sm">Prédisez les scores des matchs de la CAN 2025</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-soboa-orange/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl">🏆</span>
                    </div>
                    <h3 class="font-bold text-soboa-blue mb-2">3. Gagnez</h3>
                    <p class="text-gray-600 text-sm">Accumulez des points et grimpez au classement</p>
                </div>
            </div>
        </section>

    </div>
</x-layouts.app>
