@props(['match'])

@php
    $homeTeam = $match->homeTeam ?? null;
    $awayTeam = $match->awayTeam ?? null;
    $homeFlag = $homeTeam ? "https://flagcdn.com/w80/{$homeTeam->iso_code}.png" : null;
    $awayFlag = $awayTeam ? "https://flagcdn.com/w80/{$awayTeam->iso_code}.png" : null;

    // Determine if match is live, upcoming, or finished
    $isLive = $match->status === 'live';
    $isFinished = $match->status === 'finished';
    $isUpcoming = !$isLive && !$isFinished;
@endphp

<div class="relative bg-gradient-to-br from-white via-gray-50 to-white rounded-3xl shadow-xl border-2 border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-300 group">

    <!-- Decorative Background Pattern -->
    <div class="absolute inset-0 opacity-5 pointer-events-none">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <circle cx="20" cy="20" r="1" fill="currentColor" class="text-soboa-blue"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid)" />
        </svg>
    </div>

    <!-- Top Status Bar -->
    <div class="relative bg-gradient-to-r from-soboa-blue to-blue-600 px-6 py-3">
        <div class="flex items-center justify-between">
            <!-- Group Badge -->
            @if($match->group_name)
                <div class="flex items-center gap-2 text-white">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                    <span class="text-sm font-bold uppercase tracking-wider">Groupe {{ $match->group_name }}</span>
                </div>
            @endif

            <!-- Status Badge -->
            @if($isLive)
                <div class="flex items-center gap-2 bg-red-500 px-3 py-1 rounded-full animate-pulse">
                    <span class="w-2 h-2 bg-white rounded-full"></span>
                    <span class="text-white text-xs font-black uppercase">En direct</span>
                </div>
            @elseif($isFinished)
                <div class="bg-gray-700 px-3 py-1 rounded-full">
                    <span class="text-white text-xs font-bold uppercase">Terminé</span>
                </div>
            @else
                <div class="flex items-center gap-2 bg-green-500 px-3 py-1 rounded-full">
                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-white text-xs font-bold uppercase">À venir</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Match Content -->
    <div class="relative p-6">

        <!-- Date & Time Display -->
        <div class="text-center mb-6">
            <div class="inline-flex flex-col items-center gap-1 bg-soboa-orange/10 px-6 py-3 rounded-2xl border border-soboa-orange/20">
                <svg class="w-5 h-5 text-soboa-orange mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-gray-700 font-semibold text-sm capitalize leading-tight">
                    {{ $match->match_date->translatedFormat('l d F Y') }}
                </span>
                <div class="flex items-center gap-2 mt-1">
                    <svg class="w-4 h-4 text-soboa-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-2xl font-black text-soboa-orange">
                        {{ $match->match_date->format('H:i') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Teams Display -->
        <div class="flex items-center justify-between gap-4 mb-6">

            <!-- Home Team -->
            <div class="flex-1 text-center group/team">
                <div class="relative inline-block mb-3">
                    @if($homeFlag)
                        <div class="w-20 h-20 rounded-full overflow-hidden shadow-lg ring-4 ring-white group-hover/team:ring-soboa-orange transition-all duration-300 transform group-hover/team:scale-110">
                            <img src="{{ $homeFlag }}" alt="{{ $match->team_a }}" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-soboa-blue to-blue-600 shadow-lg ring-4 ring-white flex items-center justify-center group-hover/team:ring-soboa-orange transition-all duration-300 transform group-hover/team:scale-110">
                            <span class="text-2xl font-black text-white">{{ mb_substr($match->team_a, 0, 2) }}</span>
                        </div>
                    @endif
                </div>
                <h3 class="font-black text-gray-800 text-lg leading-tight px-2">
                    {{ $homeTeam ? $homeTeam->name : $match->team_a }}
                </h3>
            </div>

            <!-- VS / Score Separator -->
            <div class="flex-shrink-0 px-3">
                @if($isFinished && $match->score_a !== null && $match->score_b !== null)
                    <!-- Final Score -->
                    <div class="text-center">
                        <div class="flex items-center gap-3">
                            <span class="text-4xl font-black text-soboa-blue">{{ $match->score_a }}</span>
                            <span class="text-2xl font-bold text-gray-300">-</span>
                            <span class="text-4xl font-black text-soboa-blue">{{ $match->score_b }}</span>
                        </div>
                        <span class="text-xs text-gray-500 font-medium uppercase mt-1 block">Score final</span>
                    </div>
                @else
                    <!-- VS Display -->
                    <div class="text-center">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-soboa-orange to-orange-600 flex items-center justify-center shadow-lg transform group-hover:rotate-12 transition-transform duration-300">
                            <span class="text-2xl font-black text-white">VS</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Away Team -->
            <div class="flex-1 text-center group/team">
                <div class="relative inline-block mb-3">
                    @if($awayFlag)
                        <div class="w-20 h-20 rounded-full overflow-hidden shadow-lg ring-4 ring-white group-hover/team:ring-soboa-orange transition-all duration-300 transform group-hover/team:scale-110">
                            <img src="{{ $awayFlag }}" alt="{{ $match->team_b }}" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-soboa-blue to-blue-600 shadow-lg ring-4 ring-white flex items-center justify-center group-hover/team:ring-soboa-orange transition-all duration-300 transform group-hover/team:scale-110">
                            <span class="text-2xl font-black text-white">{{ mb_substr($match->team_b, 0, 2) }}</span>
                        </div>
                    @endif
                </div>
                <h3 class="font-black text-gray-800 text-lg leading-tight px-2">
                    {{ $awayTeam ? $awayTeam->name : $match->team_b }}
                </h3>
            </div>
        </div>

        <!-- Stadium Info -->
        @if($match->stadium)
            <div class="flex items-center justify-center gap-2 text-gray-500 mb-6 p-3 bg-gray-50 rounded-xl">
                <svg class="w-5 h-5 text-soboa-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="text-sm font-semibold">{{ $match->stadium }}</span>
            </div>
        @endif

        <!-- Action Button -->
        @if(!$isFinished)
            @if(session('user_id'))
                <a href="/matches"
                   class="block w-full bg-gradient-to-r from-soboa-orange to-orange-600 hover:from-orange-600 hover:to-soboa-orange text-white font-black py-4 rounded-xl text-center transition-all duration-300 transform hover:scale-105 hover:shadow-xl flex items-center justify-center gap-2 group/button">
                    <svg class="w-5 h-5 group-hover/button:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    <span class="text-lg">Pronostiquer maintenant</span>
                    <svg class="w-5 h-5 group-hover/button:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            @else
                <a href="/login"
                   class="block w-full bg-gradient-to-r from-soboa-blue to-blue-600 hover:from-blue-600 hover:to-soboa-blue text-white font-black py-4 rounded-xl text-center transition-all duration-300 transform hover:scale-105 hover:shadow-xl flex items-center justify-center gap-2 group/button">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    <span class="text-lg">Se connecter pour pronostiquer</span>
                </a>
            @endif
        @else
            <!-- Finished Match - Show result summary -->
            <div class="bg-gradient-to-r from-gray-100 to-gray-200 p-4 rounded-xl text-center">
                <div class="flex items-center justify-center gap-2 text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-bold">Match terminé</span>
                </div>
            </div>
        @endif

    </div>

    <!-- Bottom Accent -->
    <div class="h-2 bg-gradient-to-r from-soboa-orange via-soboa-blue to-soboa-orange"></div>
</div>
