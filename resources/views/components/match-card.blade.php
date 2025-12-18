@props(['match'])

@php
    $homeTeam = $match->homeTeam ?? null;
    $awayTeam = $match->awayTeam ?? null;
    $homeFlag = $homeTeam ? "https://flagcdn.com/w40/{$homeTeam->iso_code}.png" : null;
    $awayFlag = $awayTeam ? "https://flagcdn.com/w40/{$awayTeam->iso_code}.png" : null;
@endphp

<div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:border-soboa-orange/30 hover:shadow-xl transition-all group">
    <!-- Match Header -->
    <div class="flex justify-between items-center mb-4">
        <span class="text-xs font-bold text-soboa-blue bg-soboa-blue/10 px-3 py-1 rounded-full">
            Groupe {{ $match->group_name ?? 'N/A' }}
        </span>
        @if($match->status === 'finished')
            <span class="bg-gray-200 text-gray-600 text-xs font-bold px-3 py-1 rounded-full">Terminé</span>
        @else
            <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                À venir
            </span>
        @endif
    </div>
    
    <!-- Date & Time -->
    <div class="text-center mb-4">
        <span class="text-sm text-gray-500 font-semibold capitalize">
            {{ $match->match_date->translatedFormat('l d F Y') }}
        </span>
        <span class="text-lg font-black text-soboa-orange block">
            {{ $match->match_date->format('H:i') }}
        </span>
    </div>
    
    <!-- Teams with Flags -->
    <div class="flex items-center justify-between py-4">
        <!-- Home Team -->
        <div class="text-center flex-1">
            @if($homeFlag)
            <img src="{{ $homeFlag }}" alt="{{ $match->team_a }}" class="w-12 h-8 object-cover rounded mx-auto mb-2 shadow">
            @else
            <div class="w-12 h-8 bg-soboa-blue/10 rounded mx-auto mb-2 flex items-center justify-center text-sm font-bold text-soboa-blue">
                {{ mb_substr($match->team_a, 0, 3) }}
            </div>
            @endif
            <span class="font-bold text-gray-800 text-sm block truncate">{{ $match->team_a }}</span>
        </div>
        
        <!-- Score / VS -->
        <div class="px-4 text-center">
            @if($match->status === 'finished')
                <div class="text-3xl font-black text-gray-800 tracking-wider">
                    {{ $match->score_a ?? 0 }} - {{ $match->score_b ?? 0 }}
                </div>
            @else
                <div class="text-2xl font-black text-gray-300">VS</div>
            @endif
        </div>
        
        <!-- Away Team -->
        <div class="text-center flex-1">
            @if($awayFlag)
            <img src="{{ $awayFlag }}" alt="{{ $match->team_b }}" class="w-12 h-8 object-cover rounded mx-auto mb-2 shadow">
            @else
            <div class="w-12 h-8 bg-soboa-blue/10 rounded mx-auto mb-2 flex items-center justify-center text-sm font-bold text-soboa-blue">
                {{ mb_substr($match->team_b, 0, 3) }}
            </div>
            @endif
            <span class="font-bold text-gray-800 text-sm block truncate">{{ $match->team_b }}</span>
        </div>
    </div>
    
    <!-- Stadium -->
    <div class="text-center text-xs text-gray-400 mb-4 flex items-center justify-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        {{ $match->stadium ?? 'Stade à confirmer' }}
    </div>
    
    <!-- CTA -->
    @if($match->status !== 'finished')
        @if(session('user_id'))
        <a href="/matches" class="block w-full bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-3 rounded-xl text-center transition-colors">
            Pronostiquer
        </a>
        @else
        <a href="/login" class="block w-full bg-soboa-blue hover:bg-soboa-blue-dark text-white font-bold py-3 rounded-xl text-center transition-colors">
            Se connecter
        </a>
        @endif
    @endif
</div>
