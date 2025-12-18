<x-layouts.app title="Admin - Gestion du Tournoi">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">

            <!-- Header -->
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                        <span class="text-4xl">⚽</span> Gestion du Tournoi SOBOA FOOT TIME
                    </h1>
                    <p class="text-gray-600 mt-2">Vue d'ensemble de toutes les phases du tournoi</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.create-match') }}" class="bg-soboa-orange hover:bg-soboa-orange/90 text-black px-6 py-3 rounded-xl font-bold transition-all hover:scale-105 shadow-lg">
                        + Créer un match
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="bg-soboa-blue hover:bg-soboa-blue/90 text-white px-6 py-3 rounded-xl font-bold transition-all hover:scale-105 shadow-lg">
                        ← Dashboard
                    </a>
                </div>
            </div>

            <!-- Messages -->
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    ❌ {{ session('error') }}
                </div>
            @endif

            <!-- Statistiques des phases -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
                <div class="bg-white p-4 rounded-xl shadow text-center border-l-4 border-blue-500">
                    <div class="text-3xl font-bold text-blue-600">{{ $phaseStats['group_stage'] }}</div>
                    <div class="text-sm text-gray-600 font-medium">Phase de poules</div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow text-center border-l-4 border-purple-500">
                    <div class="text-3xl font-bold text-purple-600">{{ $phaseStats['round_of_16'] }}</div>
                    <div class="text-sm text-gray-600 font-medium">1/8e de finale</div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow text-center border-l-4 border-pink-500">
                    <div class="text-3xl font-bold text-pink-600">{{ $phaseStats['quarter_final'] }}</div>
                    <div class="text-sm text-gray-600 font-medium">1/4 de finale</div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow text-center border-l-4 border-orange-500">
                    <div class="text-3xl font-bold text-orange-600">{{ $phaseStats['semi_final'] }}</div>
                    <div class="text-sm text-gray-600 font-medium">1/2 finale</div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow text-center border-l-4 border-yellow-500">
                    <div class="text-3xl font-bold text-yellow-600">{{ $phaseStats['third_place'] }}</div>
                    <div class="text-sm text-gray-600 font-medium">3e place</div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow text-center border-l-4 border-green-500">
                    <div class="text-3xl font-bold text-green-600">{{ $phaseStats['final'] }}</div>
                    <div class="text-sm text-gray-600 font-medium">Finale</div>
                </div>
            </div>

            <!-- Navigation par phase -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">⚡ Gérer les matchs par phase</h2>

                <div class="grid md:grid-cols-3 gap-4">
                    <a href="{{ route('admin.phase-matches', 'group_stage') }}"
                        class="bg-blue-100 hover:bg-blue-200 p-4 rounded-lg text-center transition-colors border-2 border-blue-300 group">
                        <div class="text-3xl mb-2">🏟️</div>
                        <div class="font-bold text-blue-900">Phase de poules</div>
                        <div class="text-sm text-blue-700 mt-1">{{ $phaseStats['group_stage'] }} matchs</div>
                        <div class="text-xs text-blue-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">Voir et gérer →</div>
                    </a>

                    <a href="{{ route('admin.phase-matches', 'round_of_16') }}"
                        class="bg-purple-100 hover:bg-purple-200 p-4 rounded-lg text-center transition-colors border-2 border-purple-300 group">
                        <div class="text-3xl mb-2">🎯</div>
                        <div class="font-bold text-purple-900">1/8e de finale</div>
                        <div class="text-sm text-purple-700 mt-1">{{ $phaseStats['round_of_16'] }} matchs</div>
                        <div class="text-xs text-purple-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">Voir et gérer →</div>
                    </a>

                    <a href="{{ route('admin.phase-matches', 'quarter_final') }}"
                        class="bg-pink-100 hover:bg-pink-200 p-4 rounded-lg text-center transition-colors border-2 border-pink-300 group">
                        <div class="text-3xl mb-2">⚔️</div>
                        <div class="font-bold text-pink-900">1/4 de finale</div>
                        <div class="text-sm text-pink-700 mt-1">{{ $phaseStats['quarter_final'] }} matchs</div>
                        <div class="text-xs text-pink-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">Voir et gérer →</div>
                    </a>

                    <a href="{{ route('admin.phase-matches', 'semi_final') }}"
                        class="bg-orange-100 hover:bg-orange-200 p-4 rounded-lg text-center transition-colors border-2 border-orange-300 group">
                        <div class="text-3xl mb-2">🔥</div>
                        <div class="font-bold text-orange-900">1/2 finale</div>
                        <div class="text-sm text-orange-700 mt-1">{{ $phaseStats['semi_final'] }} matchs</div>
                        <div class="text-xs text-orange-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">Voir et gérer →</div>
                    </a>

                    <a href="{{ route('admin.phase-matches', 'third_place') }}"
                        class="bg-yellow-100 hover:bg-yellow-200 p-4 rounded-lg text-center transition-colors border-2 border-yellow-300 group">
                        <div class="text-3xl mb-2">🥉</div>
                        <div class="font-bold text-yellow-900">3e place</div>
                        <div class="text-sm text-yellow-700 mt-1">{{ $phaseStats['third_place'] }} match</div>
                        <div class="text-xs text-yellow-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">Voir et gérer →</div>
                    </a>

                    <a href="{{ route('admin.phase-matches', 'final') }}"
                        class="bg-green-100 hover:bg-green-200 p-4 rounded-lg text-center transition-colors border-2 border-green-300 group">
                        <div class="text-3xl mb-2">🏆</div>
                        <div class="font-bold text-green-900">Finale</div>
                        <div class="text-sm text-green-700 mt-1">{{ $phaseStats['final'] }} match</div>
                        <div class="text-xs text-green-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">Voir et gérer →</div>
                    </a>
                </div>
            </div>

            <!-- Info box -->
            <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg">
                <div class="flex items-start gap-3">
                    <div class="text-2xl">💡</div>
                    <div>
                        <h3 class="font-bold text-blue-900 mb-2">Gestion simplifiée du tournoi</h3>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li>• Créez vos matchs manuellement via le bouton "Créer un match"</li>
                            <li>• Sélectionnez n'importe quelle équipe pour chaque match (pas de restriction)</li>
                            <li>• Les matchs s'affichent immédiatement aux utilisateurs</li>
                            <li>• Les paris se ferment automatiquement 2 minutes avant le début du match</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>
