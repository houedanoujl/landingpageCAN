<x-layouts.app title="Accueil">
    <div class="space-y-8">

        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-brand-green to-brand-dark rounded-xl shadow-2xl p-6 md:p-10 text-white relative overflow-hidden">
            <div class="relative z-10">
                <h1 class="text-3xl md:text-5xl font-extrabold mb-4">Vivez la passion du foot!</h1>
                <p class="text-lg md:text-xl text-brand-yellow mb-6">Prédisez les scores, visitez les bars partenaires et gagnez des cadeaux exclusifs.</p>
                <a href="/matches" class="bg-brand-yellow text-brand-dark font-bold py-3 px-6 rounded-full hover:bg-white transition shadow-lg inline-block">
                    Faire un pronostic
                </a>
            </div>
            <!-- Decorative circle -->
            <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-brand-red rounded-full opacity-20 blur-2xl"></div>
        </div>

        <!-- Live / Next Match -->
        <section>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-800 border-l-4 border-brand-red pl-3">Prochains Matchs</h2>
                <a href="/matches" class="text-brand-green font-semibold hover:underline">Voir tout</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($upcomingMatches as $match)
                <div class="bg-white rounded-lg shadow-md p-4 border border-gray-100 hover:shadow-lg transition">
                    <div class="flex justify-between text-xs text-gray-500 mb-2">
                        <span>{{ $match->match_date->format('d M H:i') }}</span>
                        <span class="font-semibold text-brand-green">{{ $match->stadium }}</span>
                    </div>
                    <div class="flex justify-between items-center my-4">
                        <div class="text-center w-1/3">
                            <div class="w-12 h-12 bg-gray-200 rounded-full mx-auto mb-2 flex items-center justify-center text-xs font-bold text-gray-500">
                                {{ substr($match->team_a, 0, 3) }}
                            </div>
                            <span class="font-bold text-gray-800 block truncate">{{ $match->team_a }}</span>
                        </div>
                        <div class="text-center w-1/3 text-2xl font-black text-gray-300">
                            VS
                        </div>
                        <div class="text-center w-1/3">
                            <div class="w-12 h-12 bg-gray-200 rounded-full mx-auto mb-2 flex items-center justify-center text-xs font-bold text-gray-500">
                                {{ substr($match->team_b, 0, 3) }}
                            </div>
                            <span class="font-bold text-gray-800 block truncate">{{ $match->team_b }}</span>
                        </div>
                    </div>
                    <button class="w-full mt-2 bg-gray-50 text-brand-dark py-2 rounded border border-gray-200 hover:bg-brand-yellow hover:border-brand-yellow hover:text-brand-dark font-semibold transition text-sm">
                        Pronostiquer
                    </button>
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
            <h2 class="text-2xl font-bold text-gray-800 border-l-4 border-brand-yellow pl-3 mb-4">Meilleurs Pronostiqueurs</h2>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 text-sm uppercase">
                        <tr>
                            <th class="px-6 py-3">#</th>
                            <th class="px-6 py-3">Joueur</th>
                            <th class="px-6 py-3 text-right">Points</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($topUsers as $index => $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-bold {{ $index == 0 ? 'text-brand-red' : 'text-gray-600' }}">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-800">
                                {{ $user->name }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-brand-green">
                                {{ $user->points_total }} pts
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</x-layouts.app>
