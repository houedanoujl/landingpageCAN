<x-layouts.app title="Admin - Matchs">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="text-soboa-orange hover:underline text-sm font-bold mb-2 inline-block">← Retour au dashboard</a>
                    <h1 class="text-3xl font-black text-soboa-blue">Gestion des Matchs</h1>
                </div>
            </div>

            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6" role="alert">
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            @endif

            <!-- Matches Table -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-soboa-blue text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-bold">Date</th>
                            <th class="px-4 py-3 text-left text-sm font-bold">Groupe</th>
                            <th class="px-4 py-3 text-left text-sm font-bold">Match</th>
                            <th class="px-4 py-3 text-center text-sm font-bold">Score</th>
                            <th class="px-4 py-3 text-center text-sm font-bold">Statut</th>
                            <th class="px-4 py-3 text-right text-sm font-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($matches as $match)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4">
                                <span class="font-medium">{{ $match->match_date->format('d/m/Y') }}</span>
                                <span class="text-gray-500 text-sm block">{{ $match->match_date->format('H:i') }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="bg-soboa-blue/10 text-soboa-blue font-bold px-2 py-1 rounded text-sm">{{ $match->group_name }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    @if($match->homeTeam)
                                    <img src="https://flagcdn.com/w40/{{ $match->homeTeam->iso_code }}.png" class="w-8 h-6 rounded shadow">
                                    @endif
                                    <span class="font-bold">{{ $match->team_a }}</span>
                                    <span class="text-gray-400">vs</span>
                                    <span class="font-bold">{{ $match->team_b }}</span>
                                    @if($match->awayTeam)
                                    <img src="https://flagcdn.com/w40/{{ $match->awayTeam->iso_code }}.png" class="w-8 h-6 rounded shadow">
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($match->status === 'finished')
                                <span class="font-black text-xl">{{ $match->score_a }} - {{ $match->score_b }}</span>
                                @else
                                <span class="text-gray-400">--</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($match->status === 'finished')
                                <span class="bg-green-100 text-green-700 font-bold px-3 py-1 rounded-full text-sm">Terminé</span>
                                @else
                                <span class="bg-yellow-100 text-yellow-700 font-bold px-3 py-1 rounded-full text-sm">À venir</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.edit-match', $match->id) }}" 
                                       class="bg-soboa-orange hover:bg-soboa-orange-dark text-white font-bold px-3 py-1.5 rounded text-sm transition-colors">
                                        ✏️ Modifier
                                    </a>
                                    @if($match->status === 'finished')
                                    <form action="{{ route('admin.calculate-points', $match->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="bg-soboa-blue hover:bg-soboa-blue-dark text-white font-bold px-3 py-1.5 rounded text-sm transition-colors">
                                            🔄 Recalculer
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-layouts.app>
