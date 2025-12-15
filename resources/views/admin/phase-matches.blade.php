<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $phaseName }} - Admin CAN 2025</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-blue-900 text-white p-6 shadow-lg">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">{{ $phaseName }}</h1>
                    <p class="text-blue-200 mt-1">Gérez les matchs et qualifiez les équipes manuellement</p>
                </div>
                <a href="{{ route('admin.tournament') }}"
                    class="bg-blue-700 hover:bg-blue-600 px-4 py-2 rounded-lg font-bold transition-colors">
                    ← Retour au tournoi
                </a>
            </div>
        </div>

        <div class="max-w-7xl mx-auto p-6">
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

            <!-- Liste des matchs -->
            @if ($matches->count() > 0)
                <div class="space-y-6">
                    @foreach ($matches as $match)
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                            <div class="bg-gray-100 p-4 border-b flex justify-between items-center">
                                <div>
                                    <span class="font-bold text-gray-700">
                                        Match {{ $match->match_number ?? $match->id }}
                                    </span>
                                    @if ($match->match_date)
                                        <span class="text-sm text-gray-600 ml-4">
                                            📅 {{ $match->match_date->format('d/m/Y H:i') }}
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    @if ($match->status === 'finished')
                                        <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                                            Terminé
                                        </span>
                                    @else
                                        <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                                            À venir
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="p-6">
                                <div class="grid md:grid-cols-3 gap-6 items-center mb-6">
                                    <!-- Équipe à domicile -->
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-gray-800 mb-2">
                                            {{ $match->team_a ?? 'TBD' }}
                                        </div>
                                        @if ($match->status === 'finished' && $match->score_a !== null)
                                            <div class="text-4xl font-bold text-blue-600">
                                                {{ $match->score_a }}
                                            </div>
                                        @else
                                            <button
                                                onclick="document.getElementById('qualify-home-{{ $match->id }}').classList.toggle('hidden')"
                                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors">
                                                ✏️ Qualifier équipe
                                            </button>
                                        @endif

                                        <!-- Formulaire de qualification équipe à domicile -->
                                        <div id="qualify-home-{{ $match->id }}" class="hidden mt-3">
                                            <form
                                                action="{{ route('admin.qualify-team', $match->id) }}"
                                                method="POST">
                                                @csrf
                                                <input type="hidden" name="position" value="home">
                                                <select name="team_id" required
                                                    class="w-full border-2 border-gray-300 rounded-lg p-2 mb-2">
                                                    <option value="">-- Sélectionner --</option>
                                                    @foreach ($teams as $team)
                                                        <option value="{{ $team->id }}">
                                                            {{ $team->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit"
                                                    class="w-full bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded font-bold text-sm">
                                                    Valider
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- VS -->
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-gray-400">VS</div>
                                        @if ($match->stadium)
                                            <div class="text-xs text-gray-500 mt-2">{{ $match->stadium }}</div>
                                        @endif
                                    </div>

                                    <!-- Équipe extérieure -->
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-gray-800 mb-2">
                                            {{ $match->team_b ?? 'TBD' }}
                                        </div>
                                        @if ($match->status === 'finished' && $match->score_b !== null)
                                            <div class="text-4xl font-bold text-red-600">
                                                {{ $match->score_b }}
                                            </div>
                                        @else
                                            <button
                                                onclick="document.getElementById('qualify-away-{{ $match->id }}').classList.toggle('hidden')"
                                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-bold transition-colors">
                                                ✏️ Qualifier équipe
                                            </button>
                                        @endif

                                        <!-- Formulaire de qualification équipe extérieure -->
                                        <div id="qualify-away-{{ $match->id }}" class="hidden mt-3">
                                            <form
                                                action="{{ route('admin.qualify-team', $match->id) }}"
                                                method="POST">
                                                @csrf
                                                <input type="hidden" name="position" value="away">
                                                <select name="team_id" required
                                                    class="w-full border-2 border-gray-300 rounded-lg p-2 mb-2">
                                                    <option value="">-- Sélectionner --</option>
                                                    @foreach ($teams as $team)
                                                        <option value="{{ $team->id }}">
                                                            {{ $team->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit"
                                                    class="w-full bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded font-bold text-sm">
                                                    Valider
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informations supplémentaires -->
                                <div class="border-t pt-4 text-sm text-gray-600">
                                    @if ($match->parentMatch1 || $match->parentMatch2)
                                        <div class="bg-blue-50 p-3 rounded-lg">
                                            <strong class="text-blue-900">📌 Provenance des équipes :</strong>
                                            <div class="mt-2 grid md:grid-cols-2 gap-2">
                                                @if ($match->parentMatch1)
                                                    <div>
                                                        • Équipe à domicile : Gagnant du
                                                        <strong>Match {{ $match->parentMatch1->match_number ?? $match->parentMatch1->id }}</strong>
                                                    </div>
                                                @endif
                                                @if ($match->parentMatch2)
                                                    <div>
                                                        • Équipe extérieure : Gagnant du
                                                        <strong>Match {{ $match->parentMatch2->match_number ?? $match->parentMatch2->id }}</strong>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Bouton d'édition du match -->
                                    <div class="mt-4 text-right">
                                        <a href="{{ route('admin.edit-match', $match->id) }}"
                                            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-bold inline-block">
                                            ⚙️ Modifier le match
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white p-12 rounded-xl shadow-lg text-center">
                    <div class="text-6xl mb-4">🏟️</div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Aucun match dans cette phase</h2>
                    <p class="text-gray-600 mb-6">
                        Les matchs de {{ $phaseName }} n'ont pas encore été créés.
                    </p>
                    @if ($phase !== 'group_stage')
                        <a href="{{ route('admin.tournament') }}"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-bold inline-block">
                            🚀 Générer le bracket depuis la page principale
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</body>

</html>
