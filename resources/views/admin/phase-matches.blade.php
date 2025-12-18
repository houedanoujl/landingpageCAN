<x-layouts.app title="Admin - {{ $phaseName }}">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">

            <!-- Header -->
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-black text-soboa-blue">{{ $phaseName }}</h1>
                    <p class="text-gray-600 mt-2">Gérez les matchs de cette phase</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.create-match') }}" class="bg-soboa-orange hover:bg-soboa-orange/90 text-black px-6 py-3 rounded-xl font-bold transition-all hover:scale-105 shadow-lg">
                        + Créer un match
                    </a>
                    <a href="{{ route('admin.tournament') }}" class="bg-soboa-blue hover:bg-soboa-blue/90 text-white px-6 py-3 rounded-xl font-bold transition-all hover:scale-105 shadow-lg">
                        ← Retour au tournoi
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

            <!-- Liste des matchs -->
            @if ($matches->count() > 0)
                <div class="space-y-6">
                    @foreach ($matches as $match)
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                            <div class="bg-gray-100 p-4 border-b flex justify-between items-center">
                                <div>
                                    <span class="font-bold text-gray-700">
                                        Match #{{ $match->id }}
                                    </span>
                                    @if ($match->match_date)
                                        <span class="text-sm text-gray-600 ml-4">
                                            📅 {{ $match->match_date->format('d/m/Y H:i') }}
                                        </span>
                                    @endif
                                    @if ($match->stadium)
                                        <span class="text-sm text-gray-600 ml-4">
                                            🏟️ {{ $match->stadium }}
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    @if ($match->status === 'finished')
                                        <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                                            Terminé
                                        </span>
                                    @elseif ($match->status === 'live')
                                        <span class="bg-red-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                                            En cours
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
                                        @if($match->homeTeam && $match->homeTeam->iso_code)
                                            <img src="https://flagcdn.com/w80/{{ $match->homeTeam->iso_code }}.png"
                                                 alt="{{ $match->team_a }}"
                                                 class="w-20 h-14 object-cover rounded shadow mx-auto mb-3">
                                        @endif
                                        <div class="text-2xl font-bold text-gray-800 mb-2">
                                            {{ $match->team_a }}
                                        </div>
                                        @if ($match->status === 'finished' && $match->score_a !== null)
                                            <div class="text-4xl font-bold text-blue-600">
                                                {{ $match->score_a }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- VS / Score -->
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-gray-400">VS</div>
                                        @if($match->status !== 'finished')
                                            <div class="text-sm text-gray-500 mt-2">{{ $match->match_date->format('H:i') }}</div>
                                        @endif
                                    </div>

                                    <!-- Équipe extérieure -->
                                    <div class="text-center">
                                        @if($match->awayTeam && $match->awayTeam->iso_code)
                                            <img src="https://flagcdn.com/w80/{{ $match->awayTeam->iso_code }}.png"
                                                 alt="{{ $match->team_b }}"
                                                 class="w-20 h-14 object-cover rounded shadow mx-auto mb-3">
                                        @endif
                                        <div class="text-2xl font-bold text-gray-800 mb-2">
                                            {{ $match->team_b }}
                                        </div>
                                        @if ($match->status === 'finished' && $match->score_b !== null)
                                            <div class="text-4xl font-bold text-red-600">
                                                {{ $match->score_b }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="border-t pt-4 text-right">
                                    <a href="{{ route('admin.edit-match', $match->id) }}"
                                        class="bg-soboa-blue hover:bg-soboa-blue/90 text-white px-6 py-2 rounded-lg font-bold inline-block transition-all">
                                        ⚙️ Modifier le match
                                    </a>
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
                    <a href="{{ route('admin.create-match') }}"
                        class="bg-soboa-orange hover:bg-soboa-orange/90 text-black px-6 py-3 rounded-lg font-bold inline-block transition-all">
                        + Créer un match pour cette phase
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
