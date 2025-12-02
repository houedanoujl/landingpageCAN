<x-layouts.app title="Matchs"><x-layouts.app title="Matchs">

    <div class="space-y-6">    <div class="space-y-6">

        <div class="flex items-center justify-between">        <h1 class="text-3xl font-bold text-gray-800">Calendrier des Matchs</h1>

            <h1 class="text-3xl font-bold text-soboa-blue">Calendrier des Matchs</h1>

            <span class="text-sm text-gray-500">CAN 2025 - Maroc</span>        @if(session('success'))

        </div>        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg" role="alert">

            <span class="font-medium">{{ session('success') }}</span>

        @if(session('success'))        </div>

        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg" role="alert">        @endif

            <span class="font-medium">{{ session('success') }}</span>

        </div>        @if(session('error'))

        @endif        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg" role="alert">

            <span class="font-medium">{{ session('error') }}</span>

        @if(session('error'))        </div>

        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg" role="alert">        @endif

            <span class="font-medium">{{ session('error') }}</span>

        </div>        <div class="bg-white rounded-xl shadow-sm p-4 sticky top-20 z-40 border border-gray-200">

        @endif            <div class="flex gap-2 overflow-x-auto pb-2">

                <button class="px-4 py-2 bg-brand-green text-white rounded-full text-sm font-bold whitespace-nowrap">Tous</button>

        <!-- Filtres par groupe -->                <button class="px-4 py-2 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-full text-sm font-medium whitespace-nowrap">Aujourd'hui</button>

        <div class="bg-white rounded-xl shadow-sm p-4 sticky top-20 z-40 border border-gray-200">                <button class="px-4 py-2 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-full text-sm font-medium whitespace-nowrap">Demain</button>

            <div class="flex gap-2 overflow-x-auto pb-2">                <button class="px-4 py-2 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-full text-sm font-medium whitespace-nowrap">Terminés</button>

                <a href="/matches" class="px-4 py-2 bg-soboa-blue text-white rounded-full text-sm font-bold whitespace-nowrap">Tous</a>            </div>

                @foreach(['A', 'B', 'C', 'D', 'E', 'F'] as $group)        </div>

                <a href="/matches?group={{ $group }}" 

                   class="px-4 py-2 bg-gray-100 text-gray-600 hover:bg-soboa-orange hover:text-white rounded-full text-sm font-medium whitespace-nowrap transition">        <div class="space-y-4">

                    Groupe {{ $group }}            @forelse($matches as $match)

                </a>            <div class="bg-white rounded-lg shadow p-5 border-l-4 {{ $match->status === 'finished' ? 'border-gray-400' : 'border-brand-green' }}">

                @endforeach                <div class="flex justify-between items-start mb-4">

            </div>                    <div>

        </div>                        <span class="text-xs font-bold uppercase text-gray-400 tracking-wide">{{ $match->match_date->format('l d F Y') }}</span>

                        <div class="text-sm text-gray-500">{{ $match->stadium }}</div>

        <!-- Matchs par groupe -->                    </div>

        @forelse($matches as $groupName => $groupMatches)                    @if($match->status === 'finished')

        <div class="space-y-4">                        <span class="px-2 py-1 bg-gray-200 text-gray-600 text-xs font-bold rounded">Terminé</span>

            <!-- En-tête du groupe -->                    @else

            <div class="flex items-center gap-3">                        <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded">À venir</span>

                <div class="bg-soboa-blue text-white px-4 py-2 rounded-lg font-bold text-lg shadow">                    @endif

                    Groupe {{ $groupName }}                </div>

                </div>

                <div class="flex-1 h-0.5 bg-soboa-blue/20 rounded"></div>                <div class="flex items-center justify-between">

                <span class="text-sm text-gray-500">{{ $groupMatches->count() }} matchs</span>                    <!-- Team A -->

            </div>                    <div class="flex-1 flex flex-col items-center">

                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-2 shadow-inner">

            <!-- Liste des matchs du groupe -->                            <span class="text-xl font-bold text-gray-400">{{ substr($match->team_a, 0, 1) }}</span>

            @foreach($groupMatches as $match)                        </div>

            <div class="bg-white rounded-lg shadow p-5 border-l-4 {{ $match->status === 'finished' ? 'border-gray-400' : 'border-soboa-orange' }}">                        <span class="font-bold text-lg text-center leading-tight">{{ $match->team_a }}</span>

                <div class="flex justify-between items-start mb-4">                    </div>

                    <div>

                        <span class="text-xs font-bold uppercase text-soboa-blue tracking-wide">{{ $match->match_date->translatedFormat('l d F Y') }}</span>                    <!-- Score / Time -->

                        <div class="text-sm text-gray-500">📍 {{ $match->stadium }}</div>                    <div class="px-4 text-center">

                    </div>                        @if($match->status === 'finished')

                    <div class="flex items-center gap-2">                            <div class="text-3xl font-black text-gray-800 tracking-widest">

                        <span class="px-2 py-1 bg-soboa-blue/10 text-soboa-blue text-xs font-bold rounded">Groupe {{ $match->group_name }}</span>                                {{ $match->score_a ?? 0 }} - {{ $match->score_b ?? 0 }}

                        @if($match->status === 'finished')                            </div>

                            <span class="px-2 py-1 bg-gray-200 text-gray-600 text-xs font-bold rounded">Terminé</span>                        @else

                        @else                            <div class="text-2xl font-black text-gray-300">VS</div>

                            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded">À venir</span>                            <div class="text-sm font-bold text-brand-red mt-1">{{ $match->match_date->format('H:i') }}</div>

                        @endif                        @endif

                    </div>                    </div>

                </div>

                    <!-- Team B -->

                <div class="flex items-center justify-between">                    <div class="flex-1 flex flex-col items-center">

                    <!-- Team A -->                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-2 shadow-inner">

                    <div class="flex-1 flex flex-col items-center">                            <span class="text-xl font-bold text-gray-400">{{ substr($match->team_b, 0, 1) }}</span>

                        <div class="w-16 h-16 bg-soboa-blue/10 rounded-full flex items-center justify-center mb-2 shadow-inner">                        </div>

                            <span class="text-xl font-bold text-soboa-blue">{{ mb_substr($match->team_a, 0, 2) }}</span>                        <span class="font-bold text-lg text-center leading-tight">{{ $match->team_b }}</span>

                        </div>                    </div>

                        <span class="font-bold text-lg text-center leading-tight text-gray-800">{{ $match->team_a }}</span>                </div>

                    </div>

                @if($match->status !== 'finished' && $match->match_date > now())

                    <!-- Score / Time -->                <div class="mt-6 border-t pt-4">

                    <div class="px-4 text-center">                    <!-- Formulaire de pronostic -->

                        @if($match->status === 'finished')                    <form action="{{ route('predictions.store') }}" method="POST" class="space-y-4">

                            <div class="text-3xl font-black text-gray-800 tracking-widest">                        @csrf

                                {{ $match->score_a ?? 0 }} - {{ $match->score_b ?? 0 }}                        <input type="hidden" name="match_id" value="{{ $match->id }}">

                            </div>                        

                        @else                        <p class="text-sm text-gray-600 text-center font-medium">Entrez votre pronostic :</p>

                            <div class="text-2xl font-black text-gray-300">VS</div>                        

                            <div class="text-sm font-bold text-soboa-orange mt-1">🕐 {{ $match->match_date->format('H:i') }}</div>                        <!-- Identité de l'utilisateur -->

                        @endif                        <div class="grid grid-cols-2 gap-3">

                    </div>                            <div>

                                <input type="text" 

                    <!-- Team B -->                                       name="user_name" 

                    <div class="flex-1 flex flex-col items-center">                                       placeholder="Votre nom"

                        <div class="w-16 h-16 bg-soboa-blue/10 rounded-full flex items-center justify-center mb-2 shadow-inner">                                       value="{{ session('predictor_name', '') }}"

                            <span class="text-xl font-bold text-soboa-blue">{{ mb_substr($match->team_b, 0, 2) }}</span>                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-brand-green focus:ring-brand-green"

                        </div>                                       required>

                        <span class="font-bold text-lg text-center leading-tight text-gray-800">{{ $match->team_b }}</span>                            </div>

                    </div>                            <div>

                </div>                                <input type="email" 

                                       name="user_email" 

                @if($match->status !== 'finished' && $match->match_date > now())                                       placeholder="Votre email"

                <div class="mt-6 border-t pt-4">                                       value="{{ session('predictor_email', '') }}"

                    @if(session('user_id'))                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-brand-green focus:ring-brand-green"

                    <!-- Formulaire de pronostic pour utilisateur connecté -->                                       required>

                    <form action="{{ route('predictions.store') }}" method="POST" class="space-y-4">                            </div>

                        @csrf                        </div>

                        <input type="hidden" name="match_id" value="{{ $match->id }}">                        

                                                <div class="flex items-center justify-center gap-4">

                        <p class="text-sm text-gray-600 text-center font-medium">Entrez votre pronostic :</p>                            <!-- Score équipe A -->

                                                    <div class="flex flex-col items-center">

                        <div class="flex items-center justify-center gap-4">                                <label class="text-xs text-gray-500 mb-1">{{ $match->team_a }}</label>

                            <!-- Score équipe A -->                                <input type="number" 

                            <div class="flex flex-col items-center">                                       name="score_a" 

                                <label class="text-xs text-gray-500 mb-1">{{ $match->team_a }}</label>                                       min="0" 

                                <input type="number"                                        max="20" 

                                       name="score_a"                                        value="0"

                                       min="0"                                        class="w-16 h-12 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-brand-green focus:ring-brand-green"

                                       max="20"                                        required>

                                       value="0"                            </div>

                                       class="w-16 h-12 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-soboa-orange focus:ring-soboa-orange"                            

                                       required>                            <span class="text-2xl font-bold text-gray-400">-</span>

                            </div>                            

                                                        <!-- Score équipe B -->

                            <span class="text-2xl font-bold text-gray-400">-</span>                            <div class="flex flex-col items-center">

                                                            <label class="text-xs text-gray-500 mb-1">{{ $match->team_b }}</label>

                            <!-- Score équipe B -->                                <input type="number" 

                            <div class="flex flex-col items-center">                                       name="score_b" 

                                <label class="text-xs text-gray-500 mb-1">{{ $match->team_b }}</label>                                       min="0" 

                                <input type="number"                                        max="20" 

                                       name="score_b"                                        value="0"

                                       min="0"                                        class="w-16 h-12 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-brand-green focus:ring-brand-green"

                                       max="20"                                        required>

                                       value="0"                            </div>

                                       class="w-16 h-12 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-soboa-orange focus:ring-soboa-orange"                        </div>

                                       required>                        

                            </div>                        <button type="submit" 

                        </div>                                class="w-full bg-brand-yellow hover:bg-yellow-400 text-brand-dark font-bold py-3 px-4 rounded-lg shadow transition transform active:scale-95 flex items-center justify-center gap-2">

                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <button type="submit"                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>

                                class="w-full bg-soboa-orange hover:bg-orange-600 text-white font-bold py-3 px-4 rounded-lg shadow transition transform active:scale-95 flex items-center justify-center gap-2">                            </svg>

                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">                            Valider mon pronostic

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>                        </button>

                            </svg>                    </form>

                            Valider mon pronostic                </div>

                        </button>                @elseif($match->status !== 'finished')

                    </form>                <div class="mt-6 border-t pt-4">

                    @else                    <div class="text-center text-gray-500 text-sm">

                    <!-- Message pour inviter à se connecter -->                        <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                    <div class="text-center">                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>

                        <p class="text-gray-600 mb-3">Connectez-vous pour faire vos pronostics</p>                        </svg>

                        <a href="/login"                         Match en cours - Pronostics fermés

                           class="inline-block bg-soboa-orange hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-lg shadow transition">                    </div>

                            Se connecter                </div>

                        </a>                @endif

                    </div>            </div>

                    @endif            @empty

                </div>            <div class="text-center py-10">

                @elseif($match->status !== 'finished')                <p class="text-gray-500">Aucun match trouvé.</p>

                <div class="mt-6 border-t pt-4">            </div>

                    <div class="text-center text-gray-500 text-sm">            @endforelse

                        <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">        </div>

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>    </div>

                        </svg></x-layouts.app>

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
