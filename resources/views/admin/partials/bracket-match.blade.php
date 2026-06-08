@php
    $isLarge = $large ?? false;
    $isFinished = $match->status === 'finished';
    $homeWinner = $isFinished && $match->score_a > $match->score_b;
    $awayWinner = $isFinished && $match->score_b > $match->score_a;
@endphp

<div class="bg-gray-50 rounded-lg border-2 {{ $isFinished ? 'border-green-300' : 'border-gray-200' }} overflow-hidden {{ $isLarge ? 'max-w-md mx-auto' : '' }}">
    <!-- Date et heure -->
    <div class="bg-gray-200 px-3 py-2 text-center">
        <span class="text-xs font-bold text-gray-600">
            {{ $match->match_date->locale('fr')->translatedFormat('D j M') }} • {{ $match->match_date->format('H:i') }}
        </span>
    </div>
    
    <!-- Équipe domicile -->
    <div class="flex items-center justify-between px-4 py-3 {{ $homeWinner ? 'bg-green-100' : '' }} border-b border-gray-200">
        <div class="flex items-center gap-3">
            @if($match->homeTeam)
                <img src="{{ $match->homeTeam->flag_url }}" alt="" loading="lazy"
                     class="w-8 h-6 rounded shadow">
            @else
                <div class="w-8 h-6 bg-gray-300 rounded flex items-center justify-center text-xs">?</div>
            @endif
            <span class="font-bold {{ $homeWinner ? 'text-green-800' : 'text-gray-800' }} {{ $isLarge ? 'text-lg' : '' }}">
                {{ $match->team_a ?? 'À déterminer' }}
            </span>
        </div>
        <span class="font-black text-xl {{ $homeWinner ? 'text-green-700' : 'text-gray-700' }}">
            {{ $isFinished ? $match->score_a : '-' }}
        </span>
    </div>
    
    <!-- Équipe extérieur -->
    <div class="flex items-center justify-between px-4 py-3 {{ $awayWinner ? 'bg-green-100' : '' }}">
        <div class="flex items-center gap-3">
            @if($match->awayTeam)
                <img src="{{ $match->awayTeam->flag_url }}" alt="" loading="lazy"
                     class="w-8 h-6 rounded shadow">
            @else
                <div class="w-8 h-6 bg-gray-300 rounded flex items-center justify-center text-xs">?</div>
            @endif
            <span class="font-bold {{ $awayWinner ? 'text-green-800' : 'text-gray-800' }} {{ $isLarge ? 'text-lg' : '' }}">
                {{ $match->team_b ?? 'À déterminer' }}
            </span>
        </div>
        <span class="font-black text-xl {{ $awayWinner ? 'text-green-700' : 'text-gray-700' }}">
            {{ $isFinished ? $match->score_b : '-' }}
        </span>
    </div>

    <!-- Tirs au but si applicable -->
    @if($isFinished && $match->score_a == $match->score_b && ($match->penalty_score_a !== null || $match->penalty_score_b !== null))
    <div class="bg-yellow-50 px-3 py-2 text-center border-t border-yellow-200">
        <span class="text-xs font-bold text-yellow-700">
            TAB: {{ $match->penalty_score_a ?? 0 }} - {{ $match->penalty_score_b ?? 0 }}
        </span>
    </div>
    @endif

    <!-- Actions -->
    <div class="bg-gray-100 px-3 py-2 flex justify-center gap-2">
        <a href="{{ route('admin.edit-match', $match->id) }}" 
           class="text-xs bg-soboa-orange hover:bg-soboa-orange/80 text-black font-bold px-3 py-1 rounded transition">
            Modifier
        </a>
    </div>
</div>
