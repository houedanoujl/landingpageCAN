<x-layouts.app title="Matchs">
    <div class="space-y-6">
        <div class="relative py-12 px-8 rounded-2xl overflow-hidden mb-8 shadow-2xl">
            <!-- Background Image with Overlay -->
            <div class="absolute inset-0 z-0">
                <img src="/images/sen.webp" class="w-full h-full object-cover" alt="Background">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-[1px]"></div>
            </div>
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h1 class="text-4xl font-black text-white drop-shadow-2xl">Calendrier des Matchs</h1>
                    <p class="text-white/80 font-bold uppercase tracking-widest text-xs mt-1 drop-shadow-lg">Vivez
                        l'excitation du football</p>
                </div>
                <div class="bg-white/10 backdrop-blur-md border border-white/20 px-4 py-2 rounded-xl shadow-xl">
                    <span class="text-xs text-white/70 font-black uppercase tracking-wider block">Compétition</span>
                    <span class="text-soboa-orange font-black drop-shadow-md">SOBOA FOOT TIME</span>
                </div>
            </div>
        </div>

        <!-- Bannière du point de vente sélectionné -->
        @if(isset($selectedVenue))
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-4 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-white/80">Point de vente actuel</p>
                            <p class="font-bold text-lg">{{ $selectedVenue->name }}</p>
                            @if($selectedVenue->zone)
                                <p class="text-xs text-white/70">{{ $selectedVenue->zone }}</p>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('venues') }}"
                        class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm font-medium transition">
                        Changer
                    </a>
                </div>
            </div>
        @endif

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

        <!-- Info section -->
        <div class="bg-soboa-blue/10 border-l-4 border-soboa-blue rounded-lg p-4">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⚽</span>
                <div>
                    <p class="font-bold text-gray-800">Matchs diffusés à {{ $selectedVenue->name }}</p>
                    <p class="text-sm text-gray-600">{{ $venueMatches->count() }} match(s) disponible(s) pour vos
                        pronostics</p>
                </div>
            </div>
        </div>

        <!-- Liste des matchs -->
        @forelse($venueMatches as $match)
            <div id="match-{{ $match->id }}"
                class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all border-2 border-gray-100
                    {{ isset($favoriteTeamId) && ($match->home_team_id == $favoriteTeamId || $match->away_team_id == $favoriteTeamId) ? 'ring-2 ring-soboa-orange ring-offset-2' : '' }}">

                <!-- Header du match -->
                <div class="bg-gradient-to-r from-soboa-blue to-blue-600 px-6 py-3 text-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">⚽</span>
                            <div>
                                <p class="text-sm font-medium text-white">
                                    {{ \Carbon\Carbon::parse($match->match_date)->translatedFormat('l d F Y à H:i') }}
                                </p>
                            </div>
                        </div>
                        @if($match->status === 'finished')
                            <span class="bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                                Terminé
                            </span>
                        @elseif($match->status === 'live')
                            <span class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full animate-pulse">
                                🔴 En cours
                            </span>
                        @else
                            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">
                                À venir
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Corps du match -->
                <div class="p-6">
                    <!-- Équipes -->
                    <div class="flex items-center justify-between mb-6">
                        <!-- Équipe domicile -->
                        <div class="flex-1 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-2">
                                @if($match->homeTeam && $match->homeTeam->flag_url)
                                    <img src="{{ $match->homeTeam->flag_url }}" alt="{{ $match->homeTeam->name }}"
                                        class="w-12 h-12 object-contain">
                                @else
                                    <span class="text-2xl">🏴</span>
                                @endif
                            </div>
                            <h3 class="font-black text-lg text-gray-800">
                                {{ $match->homeTeam ? $match->homeTeam->name : $match->team_a }}
                            </h3>
                        </div>

                        <!-- Score / VS -->
                        <div class="px-6">
                            @if($match->status === 'finished' && $match->score_home !== null && $match->score_away !== null)
                                <div class="text-center">
                                    <div class="flex items-center gap-3 text-3xl font-black text-soboa-blue">
                                        <span>{{ $match->score_home }}</span>
                                        <span class="text-gray-400">-</span>
                                        <span>{{ $match->score_away }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Score final</p>
                                </div>
                            @else
                                <div class="text-center">
                                    <span class="text-2xl font-black text-gray-400">VS</span>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ \Carbon\Carbon::parse($match->match_date)->format('H:i') }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Équipe extérieure -->
                        <div class="flex-1 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-2">
                                @if($match->awayTeam && $match->awayTeam->flag_url)
                                    <img src="{{ $match->awayTeam->flag_url }}" alt="{{ $match->awayTeam->name }}"
                                        class="w-12 h-12 object-contain">
                                @else
                                    <span class="text-2xl">🏴</span>
                                @endif
                            </div>
                            <h3 class="font-black text-lg text-gray-800">
                                {{ $match->awayTeam ? $match->awayTeam->name : $match->team_b }}
                            </h3>
                        </div>
                    </div>

                    <!-- Formulaire de pronostic -->
                    @if($match->status !== 'finished')
                        @php
                            $userPrediction = $userPredictions[$match->id] ?? null;
                            $isPredictionLocked = \Carbon\Carbon::parse($match->match_date)->subMinutes(5)->isPast();
                        @endphp

                        @if(session('user_id'))
                            <div class="border-t pt-6">
                                @if($userPrediction)
                                    <!-- Pronostic existant -->
                                    <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center gap-2">
                                                <span class="text-2xl">✅</span>
                                                <div>
                                                    <p class="font-bold text-green-800">Votre pronostic</p>
                                                    <p class="text-xs text-green-600">Enregistré le
                                                        {{ $userPrediction->created_at->format('d/m/Y à H:i') }}</p>
                                                </div>
                                            </div>
                                            @if($userPrediction->points_earned > 0)
                                                <span class="bg-green-600 text-white font-bold px-3 py-1 rounded-full text-sm">
                                                    +{{ $userPrediction->points_earned }} pts
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center justify-center gap-4 text-lg font-black text-green-800">
                                            <span>{{ $userPrediction->score_a }}</span>
                                            <span class="text-green-600">-</span>
                                            <span>{{ $userPrediction->score_b }}</span>
                                        </div>
                                        @if(!$isPredictionLocked)
                                            <button onclick="enableEdit({{ $match->id }})"
                                                class="mt-3 w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition">
                                                Modifier mon pronostic
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <!-- Formulaire de pronostic -->
                                    @if($isPredictionLocked)
                                        <div class="bg-gray-100 border border-gray-300 rounded-xl p-4 text-center">
                                            <span class="text-2xl">🔒</span>
                                            <p class="text-gray-600 font-medium mt-2">Les pronostics sont fermés pour ce match</p>
                                        </div>
                                    @else
                                        <form action="{{ route('predictions.store') }}" method="POST" class="space-y-4">
                                            @csrf
                                            <input type="hidden" name="match_id" value="{{ $match->id }}">
                                            <input type="hidden" name="venue_id" value="{{ $selectedVenue->id }}">

                                            @if ($errors->any())
                                                <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                                                    <ul class="text-sm text-red-600 list-disc list-inside">
                                                        @foreach ($errors->all() as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif

                                            <div class="flex items-center justify-center gap-4">
                                                <div class="text-center flex-1">
                                                    <label class="block text-sm font-bold text-gray-700 mb-2">
                                                        Score {{ $match->homeTeam ? $match->homeTeam->name : $match->team_a }}
                                                    </label>
                                                    <input type="number" name="score_a" min="0" max="20" required
                                                        class="w-full text-center text-2xl font-black border-2 border-gray-300 rounded-xl p-3 focus:border-soboa-orange focus:ring-0">
                                                </div>
                                                <span class="text-2xl font-black text-gray-400 mt-6">-</span>
                                                <div class="text-center flex-1">
                                                    <label class="block text-sm font-bold text-gray-700 mb-2">
                                                        Score {{ $match->awayTeam ? $match->awayTeam->name : $match->team_b }}
                                                    </label>
                                                    <input type="number" name="score_b" min="0" max="20" required
                                                        class="w-full text-center text-2xl font-black border-2 border-gray-300 rounded-xl p-3 focus:border-soboa-orange focus:ring-0">
                                                </div>
                                            </div>

                                            <button type="submit"
                                                class="w-full bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-3 px-6 rounded-xl shadow-lg transition transform active:scale-95">
                                                🎯 Valider mon pronostic
                                            </button>

                                            <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded">
                                                <p class="text-xs text-blue-800">
                                                    <strong>Points:</strong> +1 pt pour un pronostic • +3 pts si bon vainqueur • +3 pts si score exact (max 7 pts)
                                                </p>
                                            </div>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        @else
                            <div class="border-t pt-6">
                                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
                                    <span class="text-2xl">🔐</span>
                                    <p class="text-yellow-800 font-medium mt-2">Connectez-vous pour faire votre pronostic</p>
                                    <a href="/login"
                                        class="mt-3 inline-block bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-2 px-6 rounded-lg transition">
                                        Se connecter
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                <span class="text-6xl">📅</span>
                <h3 class="text-2xl font-black text-gray-800 mt-4">Aucun match pour ce lieu</h3>
                <p class="text-gray-600 mt-2">Aucune animation n'est programmée pour ce point de vente.</p>
                <a href="{{ route('venues') }}"
                    class="mt-6 inline-block bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-3 px-6 rounded-lg transition">
                    Choisir un autre lieu
                </a>
            </div>
        @endforelse
    </div>

    <script>
        function scrollToMatch(matchId) {
            const element = document.getElementById('match-' + matchId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        function enableEdit(matchId) {
            // Cette fonction peut être étendue pour permettre la modification
            window.location.reload();
        }
    </script>
</x-layouts.app>