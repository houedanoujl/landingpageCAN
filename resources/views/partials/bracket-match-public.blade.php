@php
    $isLarge = $large ?? false;
    $isFinished = $match->status === 'finished';
    $homeWinner = $isFinished && $match->score_a > $match->score_b;
    $awayWinner = $isFinished && $match->score_b > $match->score_a;
@endphp

<div class="bg-gray-50 rounded-lg border-2 {{ $isFinished ? 'border-green-300' : 'border-gray-200' }} overflow-hidden">
    {{-- Date et heure --}}
    <div class="bg-gray-200 px-3 py-2 text-center">
        <span class="text-xs font-bold text-gray-600">
            {{ $match->match_date->locale('fr')->translatedFormat('D j M') }} • {{ $match->match_date->format('H:i') }}
        </span>
    </div>
    
    {{-- Équipe domicile --}}
    <div class="flex items-center justify-between px-3 py-2 {{ $homeWinner ? 'bg-green-100' : '' }} border-b border-gray-200">
        <div class="flex items-center gap-2 flex-1 min-w-0">
            @if($match->homeTeam)
                <img src="{{ $match->homeTeam->flag_url }}" class="w-6 h-4 rounded shadow flex-shrink-0" alt="" loading="lazy">
            @else
                <div class="w-6 h-4 bg-gray-300 rounded flex items-center justify-center text-[8px] flex-shrink-0">?</div>
            @endif
            <span class="font-bold {{ $homeWinner ? 'text-green-800' : 'text-gray-800' }} text-sm truncate">
                {{ $match->team_a ?? 'À déterminer' }}
            </span>
        </div>
        <span class="font-black text-lg {{ $homeWinner ? 'text-green-700' : 'text-gray-700' }} flex-shrink-0 ml-2">
            {{ $isFinished ? $match->score_a : '-' }}
        </span>
    </div>
    
    {{-- Équipe extérieur --}}
    <div class="flex items-center justify-between px-3 py-2 {{ $awayWinner ? 'bg-green-100' : '' }}">
        <div class="flex items-center gap-2 flex-1 min-w-0">
            @if($match->awayTeam)
                <img src="{{ $match->awayTeam->flag_url }}" class="w-6 h-4 rounded shadow flex-shrink-0" alt="" loading="lazy">
            @else
                <div class="w-6 h-4 bg-gray-300 rounded flex items-center justify-center text-[8px] flex-shrink-0">?</div>
            @endif
            <span class="font-bold {{ $awayWinner ? 'text-green-800' : 'text-gray-800' }} text-sm truncate">
                {{ $match->team_b ?? 'À déterminer' }}
            </span>
        </div>
        <span class="font-black text-lg {{ $awayWinner ? 'text-green-700' : 'text-gray-700' }} flex-shrink-0 ml-2">
            {{ $isFinished ? $match->score_b : '-' }}
        </span>
    </div>

    {{-- Tirs au but si applicable --}}
    @if($isFinished && $match->score_a == $match->score_b && ($match->penalty_score_a !== null || $match->penalty_score_b !== null))
    <div class="bg-yellow-50 px-3 py-1 text-center border-t border-yellow-200">
        <span class="text-xs font-bold text-yellow-700">
            TAB: {{ $match->penalty_score_a ?? 0 }} - {{ $match->penalty_score_b ?? 0 }}
        </span>
    </div>
    @endif
</div>
