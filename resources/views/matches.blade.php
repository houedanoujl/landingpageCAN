<x-layouts.app title="Matchs">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-soboa-blue">Calendrier des Matchs</h1>
            <span class="text-sm text-gray-500">CAN 2025 - Maroc</span>
        </div>

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg" role="alert">
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg" role="alert">
            <span class="font-medium">{{ session('error') }}</span>
        </div>
        @endif

        <!-- Filtres par groupe -->
        <div class="bg-white rounded-xl shadow-sm p-4 sticky top-20 z-40 border border-gray-200">
            <div class="flex gap-2 overflow-x-auto pb-2">
                <a href="/matches" class="px-4 py-2 bg-soboa-blue text-white rounded-full text-sm font-bold whitespace-nowrap">Tous</a>
                @foreach(['A', 'B', 'C', 'D', 'E', 'F'] as $group)
                <a href="/matches?group={{ $group }}" 
                   class="px-4 py-2 bg-gray-100 text-gray-600 hover:bg-soboa-orange hover:text-white rounded-full text-sm font-medium whitespace-nowrap transition">
                    Groupe {{ $group }}
                </a>
                @endforeach
            </div>
        </div>

        <!-- Matchs par groupe -->
        @forelse($matches as $groupName => $groupMatches)
        <div class="space-y-4">
            <!-- En-tête du groupe -->
            <div class="flex items-center gap-3">
                <div class="bg-soboa-blue text-white px-4 py-2 rounded-lg font-bold text-lg shadow">
                    Groupe {{ $groupName ?: 'N/A' }}
                </div>
                <div class="flex-1 h-0.5 bg-soboa-blue/20 rounded"></div>
                <span class="text-sm text-gray-500">{{ $groupMatches->count() }} matchs</span>
            </div>

            <!-- Liste des matchs du groupe -->
            @foreach($groupMatches as $match)
            <div class="bg-white rounded-lg shadow p-5 border-l-4 {{ $match->status === 'finished' ? 'border-gray-400' : 'border-soboa-orange' }}">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <span class="text-xs font-bold uppercase text-soboa-blue tracking-wide">{{ $match->match_date->translatedFormat('l d F Y') }}</span>
                        <div class="text-sm text-gray-500">📍 {{ $match->stadium }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 bg-soboa-blue/10 text-soboa-blue text-xs font-bold rounded">Groupe {{ $match->group_name ?: 'N/A' }}</span>
                        @if($match->status === 'finished')
                            <span class="px-2 py-1 bg-gray-200 text-gray-600 text-xs font-bold rounded">Terminé</span>
                        @else
                            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded">À venir</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <!-- Team A -->
                    <div class="flex-1 flex flex-col items-center">
                        @if($match->homeTeam)
                        <img src="https://flagcdn.com/w80/{{ $match->homeTeam->iso_code }}.png" 
                             alt="{{ $match->team_a }}" 
                             class="w-16 h-12 object-cover rounded shadow mb-2">
                        @else
                        <div class="w-16 h-12 bg-soboa-blue/10 rounded flex items-center justify-center mb-2 shadow-inner">
                            <span class="text-lg font-bold text-soboa-blue">{{ mb_substr($match->team_a, 0, 3) }}</span>
                        </div>
                        @endif
                        <span class="font-bold text-lg text-center leading-tight text-gray-800">{{ $match->team_a }}</span>
                    </div>

                    <!-- Score / Time -->
                    <div class="px-4 text-center">
                        @if($match->status === 'finished')
                            <div class="text-3xl font-black text-gray-800 tracking-widest">
                                {{ $match->score_a ?? 0 }} - {{ $match->score_b ?? 0 }}
                            </div>
                        @else
                            <div class="text-2xl font-black text-gray-300">VS</div>
                            <div class="text-sm font-bold text-soboa-orange mt-1">🕐 {{ $match->match_date->format('H:i') }}</div>
                        @endif
                    </div>

                    <!-- Team B -->
                    <div class="flex-1 flex flex-col items-center">
                        @if($match->awayTeam)
                        <img src="https://flagcdn.com/w80/{{ $match->awayTeam->iso_code }}.png" 
                             alt="{{ $match->team_b }}" 
                             class="w-16 h-12 object-cover rounded shadow mb-2">
                        @else
                        <div class="w-16 h-12 bg-soboa-blue/10 rounded flex items-center justify-center mb-2 shadow-inner">
                            <span class="text-lg font-bold text-soboa-blue">{{ mb_substr($match->team_b, 0, 3) }}</span>
                        </div>
                        @endif
                        <span class="font-bold text-lg text-center leading-tight text-gray-800">{{ $match->team_b }}</span>
                    </div>
                </div>

                @if($match->status !== 'finished' && $match->match_date > now())
                <div class="mt-6 border-t pt-4">
                    @if(session('user_id'))
                    <!-- Formulaire de pronostic pour utilisateur connecté -->
                    <form action="{{ route('predictions.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="match_id" value="{{ $match->id }}">
                        
                        <p class="text-sm text-gray-600 text-center font-medium">Entrez votre pronostic :</p>
                        
                        <div class="flex items-center justify-center gap-4">
                            <!-- Score équipe A -->
                            <div class="flex flex-col items-center">
                                <label class="text-xs text-gray-500 mb-1">{{ $match->team_a }}</label>
                                <input type="number"
                                       name="score_a"
                                       min="0"
                                       max="20"
                                       value="0"
                                       class="w-16 h-12 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-soboa-orange focus:ring-soboa-orange"
                                       required>
                            </div>
                            
                            <span class="text-2xl font-bold text-gray-400">-</span>
                            
                            <!-- Score équipe B -->
                            <div class="flex flex-col items-center">
                                <label class="text-xs text-gray-500 mb-1">{{ $match->team_b }}</label>
                                <input type="number"
                                       name="score_b"
                                       min="0"
                                       max="20"
                                       value="0"
                                       class="w-16 h-12 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-soboa-orange focus:ring-soboa-orange"
                                       required>
                            </div>
                        </div>
                        
                        <button type="submit"
                                class="w-full bg-soboa-orange hover:bg-orange-600 text-white font-bold py-3 px-4 rounded-lg shadow transition transform active:scale-95 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Valider mon pronostic
                        </button>
                    </form>
                    @else
                    <!-- Message pour inviter à se connecter -->
                    <div class="text-center">
                        <p class="text-gray-600 mb-3">Connectez-vous pour faire vos pronostics</p>
                        <a href="/login"
                           class="inline-block bg-soboa-orange hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-lg shadow transition">
                            Se connecter
                        </a>
                    </div>
                    @endif
                </div>
                @elseif($match->status !== 'finished')
                <div class="mt-6 border-t pt-4">
                    <div class="text-center text-gray-500 text-sm">
                        <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Match en cours - Pronostics fermés
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @empty
        <div class="text-center py-10">
            <p class="text-gray-500">Aucun match trouvé.</p>
        </div>
        @endforelse
    </div>
</x-layouts.app>
