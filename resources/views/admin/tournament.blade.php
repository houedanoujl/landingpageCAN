<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du Tournoi - Admin CAN 2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-blue-900 text-white p-6 shadow-lg">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">🏆 Gestion du Tournoi CAN 2025</h1>
                    <p class="text-blue-200 mt-1">Gérez toutes les phases du tournoi manuellement</p>
                </div>
                <div class="flex gap-4">
                    <a href="{{ route('admin.dashboard') }}"
                        class="bg-blue-700 hover:bg-blue-600 px-4 py-2 rounded-lg font-bold transition-colors">
                        ← Retour
                    </a>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto p-6">
            <!-- Messages de succès/erreur -->
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
                <div class="bg-white p-4 rounded-xl shadow text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $phaseStats['group_stage'] }}</div>
                    <div class="text-sm text-gray-600">Phase de poules</div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $phaseStats['round_of_16'] }}</div>
                    <div class="text-sm text-gray-600">1/8e de finale</div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow text-center">
                    <div class="text-2xl font-bold text-pink-600">{{ $phaseStats['quarter_final'] }}</div>
                    <div class="text-sm text-gray-600">1/4 de finale</div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow text-center">
                    <div class="text-2xl font-bold text-orange-600">{{ $phaseStats['semi_final'] }}</div>
                    <div class="text-sm text-gray-600">1/2 finale</div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $phaseStats['third_place'] }}</div>
                    <div class="text-sm text-gray-600">3e place</div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $phaseStats['final'] }}</div>
                    <div class="text-sm text-gray-600">Finale</div>
                </div>
            </div>

            <!-- Actions principales -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <!-- Générer le bracket -->
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        🎯 Générer le tableau à élimination directe
                    </h2>
                    <p class="text-gray-600 mb-4">
                        Créez automatiquement tous les matchs de knockout (1/8e, 1/4, 1/2, finale, 3e place) avec
                        leurs liens.
                    </p>
                    <form action="{{ route('admin.generate-bracket') }}" method="POST"
                        onsubmit="return confirm('Créer le tableau complet ? Cela va générer 15 matchs.')">
                        @csrf
                        <button type="submit"
                            class="bg-purple-600 hover:bg-purple-700 text-white font-bold px-6 py-3 rounded-lg w-full transition-colors">
                            🚀 Générer le bracket complet
                        </button>
                    </form>
                </div>

                <!-- Calculer les qualifiés -->
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        🧮 Calculer les équipes qualifiées
                    </h2>
                    <p class="text-gray-600 mb-4">
                        Calculez automatiquement les 16 équipes qualifiées depuis la phase de poules (1er + 2e de
                        chaque groupe + 4 meilleurs 3e).
                    </p>
                    <form action="{{ route('admin.calculate-qualified') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-lg w-full transition-colors">
                            📊 Calculer les qualifiés
                        </button>
                    </form>
                </div>
            </div>

            <!-- Classements des groupes -->
            <div class="bg-white p-6 rounded-xl shadow-lg mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">📋 Classements de la phase de poules</h2>

                @if (count($groupStandings) > 0)
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($groupStandings as $groupName => $standings)
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <div class="bg-blue-600 text-white p-3 font-bold text-center">
                                    Groupe {{ $groupName }}
                                </div>
                                <div class="divide-y divide-gray-200">
                                    @foreach ($standings as $index => $team)
                                        <div
                                            class="flex justify-between items-center p-3 {{ $index < 2 ? 'bg-green-50' : ($index === 2 ? 'bg-yellow-50' : '') }}">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-gray-600">{{ $index + 1 }}.</span>
                                                <span class="font-semibold">{{ $team['team_name'] }}</span>
                                                @if ($index < 2)
                                                    <span class="text-green-600 text-xs">✓ Qualifié</span>
                                                @elseif ($index === 2)
                                                    <span class="text-yellow-600 text-xs">? Peut-être</span>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <div class="font-bold text-lg">{{ $team['points'] }} pts</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $team['played'] }}J | {{ $team['goal_difference'] > 0 ? '+' : '' }}{{ $team['goal_difference'] }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">
                        Aucun classement disponible. Les matchs de poules doivent être terminés.
                    </p>
                @endif
            </div>

            <!-- Navigation vers les phases -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">⚡ Gérer les matchs par phase</h2>

                <div class="grid md:grid-cols-3 gap-4">
                    <a href="{{ route('admin.phase-matches', 'group_stage') }}"
                        class="bg-blue-100 hover:bg-blue-200 p-4 rounded-lg text-center transition-colors border-2 border-blue-300">
                        <div class="text-2xl mb-2">🏟️</div>
                        <div class="font-bold text-blue-900">Phase de poules</div>
                        <div class="text-sm text-blue-700">{{ $phaseStats['group_stage'] }} matchs</div>
                    </a>

                    <a href="{{ route('admin.phase-matches', 'round_of_16') }}"
                        class="bg-purple-100 hover:bg-purple-200 p-4 rounded-lg text-center transition-colors border-2 border-purple-300">
                        <div class="text-2xl mb-2">🎯</div>
                        <div class="font-bold text-purple-900">1/8e de finale</div>
                        <div class="text-sm text-purple-700">{{ $phaseStats['round_of_16'] }} matchs</div>
                    </a>

                    <a href="{{ route('admin.phase-matches', 'quarter_final') }}"
                        class="bg-pink-100 hover:bg-pink-200 p-4 rounded-lg text-center transition-colors border-2 border-pink-300">
                        <div class="text-2xl mb-2">⚔️</div>
                        <div class="font-bold text-pink-900">1/4 de finale</div>
                        <div class="text-sm text-pink-700">{{ $phaseStats['quarter_final'] }} matchs</div>
                    </a>

                    <a href="{{ route('admin.phase-matches', 'semi_final') }}"
                        class="bg-orange-100 hover:bg-orange-200 p-4 rounded-lg text-center transition-colors border-2 border-orange-300">
                        <div class="text-2xl mb-2">🔥</div>
                        <div class="font-bold text-orange-900">1/2 finale</div>
                        <div class="text-sm text-orange-700">{{ $phaseStats['semi_final'] }} matchs</div>
                    </a>

                    <a href="{{ route('admin.phase-matches', 'third_place') }}"
                        class="bg-yellow-100 hover:bg-yellow-200 p-4 rounded-lg text-center transition-colors border-2 border-yellow-300">
                        <div class="text-2xl mb-2">🥉</div>
                        <div class="font-bold text-yellow-900">3e place</div>
                        <div class="text-sm text-yellow-700">{{ $phaseStats['third_place'] }} match</div>
                    </a>

                    <a href="{{ route('admin.phase-matches', 'final') }}"
                        class="bg-green-100 hover:bg-green-200 p-4 rounded-lg text-center transition-colors border-2 border-green-300">
                        <div class="text-2xl mb-2">🏆</div>
                        <div class="font-bold text-green-900">Finale</div>
                        <div class="text-sm text-green-700">{{ $phaseStats['final'] }} match</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
