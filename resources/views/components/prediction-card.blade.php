@props(['match', 'userPrediction' => null])

<div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:border-soboa-orange/30 hover:shadow-xl transition-all">
    <!-- Match Header -->
    <div class="flex justify-between items-center mb-4">
        <span class="text-xs font-bold text-soboa-blue bg-soboa-blue/10 px-3 py-1 rounded-full">
            Groupe {{ $match->group_name ?? 'N/A' }}
        </span>
        <div class="flex items-center gap-2">
            @if($match->status === 'finished')
                <span class="bg-gray-200 text-gray-600 text-xs font-bold px-3 py-1 rounded-full">Terminé</span>
            @else
                <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">À venir</span>
            @endif
        </div>
    </div>
    
    <!-- Date & Stadium -->
    <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
        <span class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            {{ $match->match_date->translatedFormat('l d F Y') }}
        </span>
        <span class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ $match->match_date->format('H:i') }}
        </span>
    </div>
    
    <!-- Teams -->
    <div class="flex items-center justify-between py-4">
        <!-- Team A -->
        <div class="text-center flex-1">
            <div class="w-16 h-16 bg-soboa-blue/10 rounded-full mx-auto mb-2 flex items-center justify-center text-xl font-bold text-soboa-blue shadow-inner">
                {{ mb_substr($match->team_a, 0, 2) }}
            </div>
            <span class="font-bold text-gray-800 block truncate">{{ $match->team_a }}</span>
        </div>
        
        <!-- Score / VS -->
        <div class="px-6 text-center">
            @if($match->status === 'finished')
                <div class="text-4xl font-black text-gray-800 tracking-wider">
                    {{ $match->score_a ?? 0 }} - {{ $match->score_b ?? 0 }}
                </div>
                <div class="text-xs text-gray-500 mt-1">Score final</div>
            @else
                <div class="text-3xl font-black text-gray-300">VS</div>
                <div class="text-sm font-bold text-soboa-orange mt-1">
                    🕐 {{ $match->match_date->format('H:i') }}
                </div>
            @endif
        </div>
        
        <!-- Team B -->
        <div class="text-center flex-1">
            <div class="w-16 h-16 bg-soboa-blue/10 rounded-full mx-auto mb-2 flex items-center justify-center text-xl font-bold text-soboa-blue shadow-inner">
                {{ mb_substr($match->team_b, 0, 2) }}
            </div>
            <span class="font-bold text-gray-800 block truncate">{{ $match->team_b }}</span>
        </div>
    </div>
    
    <!-- Stadium -->
    <div class="text-center text-sm text-gray-400 mb-4 flex items-center justify-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        {{ $match->stadium }}
    </div>
    
    <!-- User's Prediction (if exists) -->
    @if($userPrediction)
    <div class="bg-soboa-blue/5 rounded-xl p-4 mb-4">
        <div class="flex items-center justify-between">
            <span class="text-sm font-semibold text-soboa-blue">Votre pronostic</span>
            <span class="text-lg font-black text-soboa-orange">
                {{ $userPrediction->score_a }} - {{ $userPrediction->score_b }}
            </span>
        </div>
        @if($match->status === 'finished' && $userPrediction->points_earned > 0)
        <div class="mt-2 flex items-center justify-center gap-2 text-green-600">
            <span class="text-xl">🎉</span>
            <span class="font-bold">+{{ $userPrediction->points_earned }} points gagnés!</span>
        </div>
        @endif
    </div>
    @endif
    
    <!-- Prediction Form -->
    @if($match->status !== 'finished' && $match->match_date > now()->addHour())
        @if(session('user_id'))
        <form action="{{ route('predictions.store') }}" method="POST" class="space-y-4 border-t pt-4">
            @csrf
            <input type="hidden" name="match_id" value="{{ $match->id }}">
            
            <p class="text-sm text-gray-600 text-center font-medium">
                {{ $userPrediction ? 'Modifier votre pronostic' : 'Entrez votre pronostic' }}
            </p>
            
            <div class="flex items-center justify-center gap-4">
                <!-- Score Team A -->
                <div class="flex flex-col items-center">
                    <label class="text-xs text-gray-500 mb-1 font-medium">{{ $match->team_a }}</label>
                    <input type="number" 
                           name="score_a" 
                           id="score_a_{{ $match->id }}"
                           min="0" 
                           max="20" 
                           value="{{ $userPrediction->score_a ?? 0 }}"
                           onchange="checkForPenalties{{ $match->id }}()"
                           class="w-16 h-14 text-center text-2xl font-bold border-2 border-gray-200 rounded-xl focus:border-soboa-orange focus:ring-soboa-orange transition-colors"
                           required>
                </div>
                
                <span class="text-2xl font-bold text-gray-300 mt-6">-</span>
                
                <!-- Score Team B -->
                <div class="flex flex-col items-center">
                    <label class="text-xs text-gray-500 mb-1 font-medium">{{ $match->team_b }}</label>
                    <input type="number" 
                           name="score_b" 
                           id="score_b_{{ $match->id }}"
                           min="0" 
                           max="20" 
                           value="{{ $userPrediction->score_b ?? 0 }}"
                           onchange="checkForPenalties{{ $match->id }}()"
                           class="w-16 h-14 text-center text-2xl font-bold border-2 border-gray-200 rounded-xl focus:border-soboa-orange focus:ring-soboa-orange transition-colors"
                           required>
                </div>
            </div>
            
            @php
                $knockoutPhases = ['round_of_16', 'quarter_final', 'semi_final', 'third_place', 'final'];
                $isKnockoutPhase = in_array($match->phase, $knockoutPhases);
            @endphp
            
            @if($isKnockoutPhase)
            <!-- Section Tirs au But (visible seulement si égalité ET phase éliminatoire) -->
            <div id="penaltiesSection{{ $match->id }}" class="mt-4 p-4 bg-yellow-50 border border-yellow-300 rounded-xl" style="display: none;">
                <div class="text-center text-sm font-bold text-gray-700 mb-3">
                    ⚽ En cas de tirs au but
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center justify-center gap-2 p-3 border-2 rounded-xl cursor-pointer transition-all hover:bg-yellow-100 has-[:checked]:border-yellow-600 has-[:checked]:bg-yellow-100">
                        <input type="radio" 
                               name="penalty_winner" 
                               value="home"
                               {{ ($userPrediction->penalty_winner ?? '') === 'home' ? 'checked' : '' }}
                               class="w-4 h-4 text-yellow-600">
                        <span class="text-sm font-bold text-gray-800">{{ $match->team_a }}</span>
                    </label>
                    <label class="flex items-center justify-center gap-2 p-3 border-2 rounded-xl cursor-pointer transition-all hover:bg-yellow-100 has-[:checked]:border-yellow-600 has-[:checked]:bg-yellow-100">
                        <input type="radio" 
                               name="penalty_winner" 
                               value="away"
                               {{ ($userPrediction->penalty_winner ?? '') === 'away' ? 'checked' : '' }}
                               class="w-4 h-4 text-yellow-600">
                        <span class="text-sm font-bold text-gray-800">{{ $match->team_b }}</span>
                    </label>
                </div>
                <p class="text-xs text-gray-600 mt-2 text-center">
                    💡 Sélectionnez le vainqueur si vous prédisez une égalité
                </p>
            </div>
            
            <script>
                function checkForPenalties{{ $match->id }}() {
                    const scoreA = document.getElementById('score_a_{{ $match->id }}').value;
                    const scoreB = document.getElementById('score_b_{{ $match->id }}').value;
                    const penaltiesSection = document.getElementById('penaltiesSection{{ $match->id }}');
                    
                    if (scoreA !== '' && scoreB !== '' && scoreA === scoreB) {
                        penaltiesSection.style.display = 'block';
                    } else {
                        penaltiesSection.style.display = 'none';
                        // Décocher les radios si on cache la section
                        const radios = penaltiesSection.querySelectorAll('input[type="radio"]');
                        radios.forEach(radio => radio.checked = false);
                    }
                }
                
                // Vérifier au chargement
                document.addEventListener('DOMContentLoaded', function() {
                    checkForPenalties{{ $match->id }}();
                });
            </script>
            @endif
            
            <button type="submit" 
                    class="w-full bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-3 px-4 rounded-xl shadow-lg transition-all transform active:scale-95 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ $userPrediction ? 'Modifier mon pronostic' : 'Valider mon pronostic' }}
            </button>
        </form>
        @else
        <div class="text-center border-t pt-4">
            <p class="text-gray-600 mb-3">Connectez-vous pour faire vos pronostics</p>
            <a href="/login" class="inline-block bg-soboa-blue hover:bg-soboa-blue-dark text-white font-bold py-3 px-6 rounded-xl shadow-lg transition-colors">
                Se connecter
            </a>
        </div>
        @endif
    @elseif($match->status !== 'finished')
    <div class="text-center border-t pt-4 text-gray-500">
        <div class="flex items-center justify-center gap-2 text-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            Pronostics fermés (moins d'1h avant le match)
        </div>
    </div>
    @endif
</div>
