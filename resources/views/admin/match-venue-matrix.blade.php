<x-layouts.app title="Admin - Matrice Matchs/PDV">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-full mx-auto px-4">

            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                        <span class="text-4xl">📊</span> Matrice Matchs / Points de Vente
                    </h1>
                    <p class="text-gray-600 mt-2">Vue croisée des matchs et des points de vente</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg transition">
                    ← Retour
                </a>
            </div>

            <!-- Filtres -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <form method="GET" action="{{ route('admin.match-venue-matrix') }}" class="flex gap-4">
                    <div class="flex-1">
                        <label for="phase" class="block text-sm font-bold text-gray-700 mb-2">
                            Phase du tournoi
                        </label>
                        <select id="phase" name="phase" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-orange">
                            <option value="">Toutes les phases</option>
                            @foreach($phases as $phaseKey => $phaseName)
                                <option value="{{ $phaseKey }}" {{ $phase === $phaseKey ? 'selected' : '' }}>{{ $phaseName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-1">
                        <label for="zone" class="block text-sm font-bold text-gray-700 mb-2">
                            Zone
                        </label>
                        <select id="zone" name="zone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-orange">
                            <option value="">Toutes les zones</option>
                            @foreach($zones as $zoneName)
                                <option value="{{ $zoneName }}" {{ $zone === $zoneName ? 'selected' : '' }}>{{ $zoneName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="bg-soboa-orange hover:bg-soboa-orange/90 text-black font-bold px-6 py-2 rounded-lg transition">
                            Filtrer
                        </button>
                    </div>

                    @if($phase || $zone)
                    <div class="flex items-end">
                        <a href="{{ route('admin.match-venue-matrix') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold px-6 py-2 rounded-lg transition">
                            Réinitialiser
                        </a>
                    </div>
                    @endif
                </form>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl p-4 shadow">
                    <p class="text-2xl font-black text-soboa-blue">{{ $matches->count() }}</p>
                    <p class="text-gray-500 text-sm">Matchs affichés</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow">
                    <p class="text-2xl font-black text-green-600">{{ $bars->count() }}</p>
                    <p class="text-gray-500 text-sm">PDV affichés</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow">
                    @php
                        $totalAssignments = 0;
                        foreach ($matrix as $matchAnimations) {
                            $totalAssignments += count($matchAnimations);
                        }
                    @endphp
                    <p class="text-2xl font-black text-soboa-orange">{{ $totalAssignments }}</p>
                    <p class="text-gray-500 text-sm">Assignations totales</p>
                </div>
            </div>

            @if($matches->isEmpty())
                <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                    <span class="text-6xl mb-4 block">🔍</span>
                    <p class="text-xl font-bold text-gray-500">Aucun match trouvé avec ces filtres</p>
                </div>
            @elseif($bars->isEmpty())
                <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                    <span class="text-6xl mb-4 block">📍</span>
                    <p class="text-xl font-bold text-gray-500">Aucun point de vente actif trouvé</p>
                </div>
            @else
                <!-- Matrice -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-3 py-3 text-left font-bold text-gray-700 border-r border-gray-300 bg-gray-100 sticky left-0 z-10 min-w-[250px]">
                                        Match
                                    </th>
                                    @foreach($bars as $bar)
                                        <th class="px-2 py-3 text-center font-bold text-gray-700 border-l border-gray-200 min-w-[120px]">
                                            <div class="text-xs">{{ $bar->name }}</div>
                                            @if($bar->zone)
                                                <div class="text-xs text-gray-500 font-normal">{{ $bar->zone }}</div>
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($matches as $match)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-4 border-r border-gray-300 bg-gray-50 sticky left-0 z-10">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-bold text-gray-500">
                                                    {{ $match->match_date->format('d/m H:i') }}
                                                </span>
                                                <div class="flex items-center gap-2">
                                                    @if($match->homeTeam)
                                                        <img src="https://flagcdn.com/w20/{{ $match->homeTeam->iso_code }}.png" class="w-5 h-4 rounded">
                                                    @endif
                                                    <span class="font-medium text-xs">{{ $match->team_a }}</span>
                                                    <span class="text-gray-400 text-xs">-</span>
                                                    <span class="font-medium text-xs">{{ $match->team_b }}</span>
                                                    @if($match->awayTeam)
                                                        <img src="https://flagcdn.com/w20/{{ $match->awayTeam->iso_code }}.png" class="w-5 h-4 rounded">
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        @foreach($bars as $bar)
                                            <td class="px-2 py-4 text-center border-l border-gray-200">
                                                @if(isset($matrix[$match->id][$bar->id]))
                                                    <div class="flex items-center justify-center">
                                                        <span class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center text-white text-xs font-bold" title="Assigné">
                                                            ✓
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="flex items-center justify-center">
                                                        <span class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center text-gray-400 text-xs">
                                                            -
                                                        </span>
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Légende -->
                <div class="bg-white rounded-xl shadow-lg p-6 mt-6">
                    <h3 class="font-bold text-gray-700 mb-3">Légende</h3>
                    <div class="flex gap-6">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center text-white text-xs font-bold">✓</span>
                            <span class="text-sm text-gray-600">Match assigné à ce PDV</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center text-gray-400 text-xs">-</span>
                            <span class="text-sm text-gray-600">Match non assigné</span>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-layouts.app>
