@php
use Carbon\Carbon;

$currentDate = Carbon::now();
$year = request('year', $currentDate->year);
$month = request('month', $currentDate->month);
$activeTab = $tab ?? 'calendar';

$startOfMonth = Carbon::create($year, $month, 1);
$endOfMonth = $startOfMonth->copy()->endOfMonth();
$startDayOfWeek = $startOfMonth->dayOfWeekIso;
$prevMonthDays = $startDayOfWeek - 1;
$prevMonth = $startOfMonth->copy()->subMonth();
$daysInPrevMonth = $prevMonth->daysInMonth;

$calendarDays = [];

for ($i = $prevMonthDays; $i > 0; $i--) {
    $day = $daysInPrevMonth - $i + 1;
    $date = $prevMonth->copy()->day($day);
    $calendarDays[] = ['date' => $day, 'fullDate' => $date->format('Y-m-d'), 'isCurrentMonth' => false, 'isToday' => false];
}

for ($day = 1; $day <= $endOfMonth->day; $day++) {
    $date = Carbon::create($year, $month, $day);
    $calendarDays[] = ['date' => $day, 'fullDate' => $date->format('Y-m-d'), 'isCurrentMonth' => true, 'isToday' => $date->isToday()];
}

$remainingDays = 42 - count($calendarDays);
$nextMonth = $startOfMonth->copy()->addMonth();
for ($day = 1; $day <= $remainingDays; $day++) {
    $date = $nextMonth->copy()->day($day);
    $calendarDays[] = ['date' => $day, 'fullDate' => $date->format('Y-m-d'), 'isCurrentMonth' => false, 'isToday' => false];
}

$matchesByDate = $matches->groupBy(function($match) {
    return Carbon::parse($match->match_date)->format('Y-m-d');
});

$daysWithMatches = collect($calendarDays)->filter(function($day) use ($matchesByDate) {
    return $matchesByDate->has($day['fullDate']) && $day['isCurrentMonth'];
});

$prevMonthLink = route('calendar', ['year' => $prevMonth->year, 'month' => $prevMonth->month, 'tab' => 'calendar']);
$nextMonthLink = route('calendar', ['year' => $nextMonth->year, 'month' => $nextMonth->month, 'tab' => 'calendar']);
$monthNames = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
$dayNames = ['', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
@endphp

<x-layouts.app title="Calendrier & Classement CAN 2025">
<div class="min-h-screen bg-gradient-to-br from-soboa-blue via-blue-800 to-blue-900 py-4 px-2 md:px-4">

{{-- Header --}}
<div class="max-w-7xl mx-auto mb-4">
    <div class="text-center">
        <h1 class="text-xl md:text-4xl font-black text-white mb-2">CAN 2025</h1>
        <p class="text-blue-200 text-xs md:text-sm">{{ $totalMatches }} matchs - {{ $finishedMatches }} terminés - {{ $upcomingMatches }} à venir</p>
    </div>
</div>

{{-- Navigation Onglets --}}
<div class="max-w-7xl mx-auto mb-4">
    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-1 flex">
        <a href="{{ route('calendar', ['tab' => 'calendar', 'year' => $year, 'month' => $month]) }}" 
           class="flex-1 py-3 px-4 text-center font-bold rounded-lg transition {{ $activeTab === 'calendar' ? 'bg-white text-soboa-blue' : 'text-white hover:bg-white/10' }}">
            <span class="text-lg mr-1">📅</span><span class="text-xs sm:text-base">Calendrier</span>
        </a>
        <a href="{{ route('calendar', ['tab' => 'standings']) }}" 
           class="flex-1 py-3 px-4 text-center font-bold rounded-lg transition {{ $activeTab === 'standings' ? 'bg-white text-soboa-blue' : 'text-white hover:bg-white/10' }}">
            <span class="text-lg mr-1">📊</span><span class="text-xs sm:text-base">Classement</span>
        </a>
        <a href="{{ route('calendar', ['tab' => 'bracket']) }}" 
           class="flex-1 py-3 px-4 text-center font-bold rounded-lg transition {{ $activeTab === 'bracket' ? 'bg-white text-soboa-blue' : 'text-white hover:bg-white/10' }}">
            <span class="text-lg mr-1">🏆</span><span class="text-xs sm:text-base">Bracket</span>
        </a>
    </div>
</div>

@if($activeTab === 'calendar')
{{-- ==================== ONGLET CALENDRIER ==================== --}}

{{-- Navigation Mois --}}
<div class="max-w-7xl mx-auto mb-4">
    <div class="flex items-center justify-between bg-white/10 backdrop-blur-sm rounded-xl p-2">
        <a href="{{ $prevMonthLink }}" class="p-2 text-white hover:bg-white/20 rounded-lg">
            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <h2 class="text-lg md:text-xl font-bold text-white">{{ $monthNames[$month] }} {{ $year }}</h2>
        <a href="{{ $nextMonthLink }}" class="p-2 text-white hover:bg-white/20 rounded-lg">
            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </a>
    </div>
</div>

{{-- VERSION MOBILE : Liste des matchs par jour --}}
<div class="md:hidden max-w-7xl mx-auto space-y-3">
    @forelse($daysWithMatches as $day)
        @php 
            $dayMatches = $matchesByDate->get($day['fullDate'], collect());
            $dateObj = Carbon::parse($day['fullDate']);
        @endphp
        <div class="bg-white rounded-xl shadow-lg overflow-hidden @if($day['isToday']) ring-2 ring-soboa-orange @endif">
            <div class="bg-gradient-to-r from-soboa-blue to-blue-700 px-4 py-2 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-2xl font-black text-white">{{ $day['date'] }}</span>
                    <span class="text-white/80 text-sm">{{ $dayNames[$dateObj->dayOfWeekIso] }}</span>
                </div>
                @if($day['isToday'])
                    <span class="bg-soboa-orange text-white text-xs px-2 py-1 rounded-full font-bold">Aujourd'hui</span>
                @endif
                <span class="bg-white/20 text-white text-xs px-2 py-1 rounded-full">{{ $dayMatches->count() }} match{{ $dayMatches->count() > 1 ? 's' : '' }}</span>
            </div>
            
            <div class="divide-y divide-gray-100">
                @foreach($dayMatches as $match)
                    <a href="{{ route('matches') }}#match-{{ $match->id }}" class="block p-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 flex-1">
                                @if($match->homeTeam && $match->homeTeam->iso_code)
                                    <img src="https://flagcdn.com/w40/{{ $match->homeTeam->iso_code }}.png" class="w-8 h-5 object-cover rounded shadow-sm" alt="">
                                @endif
                                <span class="font-semibold text-gray-800 text-sm truncate">{{ $match->homeTeam->name ?? $match->team_a ?? '?' }}</span>
                            </div>
                            
                            <div class="flex-shrink-0 mx-2">
                                @if($match->status === 'finished')
                                    <div class="flex flex-col items-center">
                                        <div class="flex items-center gap-1 bg-green-100 px-3 py-1 rounded-lg">
                                            <span class="text-lg font-black text-green-700">{{ $match->score_a }}</span>
                                            <span class="text-gray-400 font-bold">-</span>
                                            <span class="text-lg font-black text-green-700">{{ $match->score_b }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-blue-50 px-3 py-1 rounded-lg">
                                        <span class="text-blue-600 font-semibold text-sm">{{ Carbon::parse($match->match_date)->format('H:i') }}</span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="flex items-center gap-2 flex-1 justify-end">
                                <span class="font-semibold text-gray-800 text-sm truncate text-right">{{ $match->awayTeam->name ?? $match->team_b ?? '?' }}</span>
                                @if($match->awayTeam && $match->awayTeam->iso_code)
                                    <img src="https://flagcdn.com/w40/{{ $match->awayTeam->iso_code }}.png" class="w-8 h-5 object-cover rounded shadow-sm" alt="">
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-8 text-center">
            <p class="text-white/80">Aucun match prévu ce mois</p>
        </div>
    @endforelse
</div>

{{-- VERSION DESKTOP : Grille calendrier --}}
<div class="hidden md:block max-w-7xl mx-auto">
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div style="display: grid; grid-template-columns: repeat(7, 1fr);" class="bg-gradient-to-r from-soboa-blue to-blue-700">
            <div class="py-3 text-center text-white font-bold text-sm border-r border-white/20">Lun</div>
            <div class="py-3 text-center text-white font-bold text-sm border-r border-white/20">Mar</div>
            <div class="py-3 text-center text-white font-bold text-sm border-r border-white/20">Mer</div>
            <div class="py-3 text-center text-white font-bold text-sm border-r border-white/20">Jeu</div>
            <div class="py-3 text-center text-white font-bold text-sm border-r border-white/20">Ven</div>
            <div class="py-3 text-center text-white font-bold text-sm border-r border-white/20">Sam</div>
            <div class="py-3 text-center text-white font-bold text-sm">Dim</div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(7, 1fr);">
            @foreach($calendarDays as $day)
                @php $dayMatches = $matchesByDate->get($day['fullDate'], collect()); @endphp
                <div class="border-r border-b border-gray-200 p-2 min-h-[100px] @if(!$day['isCurrentMonth']) bg-gray-50 @elseif($day['isToday']) bg-orange-50 ring-2 ring-inset ring-soboa-orange @else bg-white @endif">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-bold w-7 h-7 flex items-center justify-center rounded-full @if(!$day['isCurrentMonth']) text-gray-400 @elseif($day['isToday']) bg-soboa-orange text-white @else text-gray-700 @endif">{{ $day['date'] }}</span>
                        @if($dayMatches->count() > 0)
                            <span class="text-[10px] bg-soboa-blue text-white px-1.5 py-0.5 rounded-full font-bold">{{ $dayMatches->count() }}</span>
                        @endif
                    </div>
                    @if($dayMatches->count() > 0)
                        <div class="space-y-1 overflow-y-auto" style="max-height: 80px;">
                            @foreach($dayMatches as $match)
                                <a href="{{ route('matches') }}#match-{{ $match->id }}" class="block rounded p-1.5 text-[11px] hover:opacity-80 transition-opacity @if($match->status === 'finished') bg-green-100 border-l-2 border-green-500 @else bg-blue-50 border-l-2 border-blue-400 @endif">
                                    <div class="text-[9px] text-gray-500 mb-0.5">{{ Carbon::parse($match->match_date)->format('H:i') }}</div>
                                    <div class="flex items-center justify-between gap-1">
                                        <div class="flex items-center gap-1 min-w-0 flex-1">
                                            @if($match->homeTeam && $match->homeTeam->iso_code)
                                                <img src="https://flagcdn.com/w20/{{ $match->homeTeam->iso_code }}.png" class="w-4 h-3 object-cover rounded-sm flex-shrink-0">
                                            @endif
                                            <span class="font-medium truncate">{{ Str::limit($match->homeTeam->name ?? '?', 3, '') }}</span>
                                        </div>
                                        @if($match->status === 'finished')
                                            <span class="font-black text-green-700 flex-shrink-0">{{ $match->score_a }}-{{ $match->score_b }}</span>
                                        @else
                                            <span class="text-blue-500 flex-shrink-0 text-[10px]">vs</span>
                                        @endif
                                        <div class="flex items-center gap-1 min-w-0 flex-1 justify-end">
                                            <span class="font-medium truncate">{{ Str::limit($match->awayTeam->name ?? '?', 3, '') }}</span>
                                            @if($match->awayTeam && $match->awayTeam->iso_code)
                                                <img src="https://flagcdn.com/w20/{{ $match->awayTeam->iso_code }}.png" class="w-4 h-3 object-cover rounded-sm flex-shrink-0">
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Légende --}}
<div class="max-w-7xl mx-auto mt-4">
    <div class="flex flex-wrap justify-center gap-4 text-xs md:text-sm">
        <div class="flex items-center gap-2 text-white">
            <div class="w-4 h-4 bg-green-100 border-l-2 border-green-500 rounded"></div>
            <span>Terminé</span>
        </div>
        <div class="flex items-center gap-2 text-white">
            <div class="w-4 h-4 bg-blue-50 border-l-2 border-blue-400 rounded"></div>
            <span>À venir</span>
        </div>
        <div class="flex items-center gap-2 text-white">
            <div class="w-4 h-4 bg-soboa-orange rounded-full"></div>
            <span>Aujourd'hui</span>
        </div>
    </div>
</div>

@elseif($activeTab === 'standings')
{{-- ==================== ONGLET CLASSEMENT ==================== --}}

<div class="max-w-7xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @foreach($groupedStandings as $groupName => $teams)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-soboa-blue to-blue-700 px-4 py-3">
                    <h3 class="text-lg font-black text-white">Groupe {{ $groupName }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-2 text-left text-xs font-bold text-gray-500">#</th>
                                <th class="px-2 py-2 text-left text-xs font-bold text-gray-500">Équipe</th>
                                <th class="px-2 py-2 text-center text-xs font-bold text-gray-500">J</th>
                                <th class="px-2 py-2 text-center text-xs font-bold text-gray-500">G</th>
                                <th class="px-2 py-2 text-center text-xs font-bold text-gray-500">N</th>
                                <th class="px-2 py-2 text-center text-xs font-bold text-gray-500">P</th>
                                <th class="px-2 py-2 text-center text-xs font-bold text-gray-500 hidden sm:table-cell">BP</th>
                                <th class="px-2 py-2 text-center text-xs font-bold text-gray-500 hidden sm:table-cell">BC</th>
                                <th class="px-2 py-2 text-center text-xs font-bold text-gray-500">+/-</th>
                                <th class="px-2 py-2 text-center text-xs font-bold text-gray-500">Pts</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($teams as $index => $teamData)
                                <tr class="{{ $index < 2 ? 'bg-green-50' : ($index == 2 ? 'bg-yellow-50' : '') }}">
                                    <td class="px-2 py-2 font-bold text-gray-600">{{ $index + 1 }}</td>
                                    <td class="px-2 py-2">
                                        <div class="flex items-center gap-2">
                                            <img src="https://flagcdn.com/w40/{{ $teamData['team']->iso_code }}.png" class="w-6 h-4 rounded shadow">
                                            <span class="font-bold text-gray-800 text-xs sm:text-sm">{{ $teamData['team']->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-2 py-2 text-center text-gray-600">{{ $teamData['played'] }}</td>
                                    <td class="px-2 py-2 text-center text-green-600 font-bold">{{ $teamData['wins'] }}</td>
                                    <td class="px-2 py-2 text-center text-gray-600">{{ $teamData['draws'] }}</td>
                                    <td class="px-2 py-2 text-center text-red-600">{{ $teamData['losses'] }}</td>
                                    <td class="px-2 py-2 text-center text-gray-600 hidden sm:table-cell">{{ $teamData['goals_for'] }}</td>
                                    <td class="px-2 py-2 text-center text-gray-600 hidden sm:table-cell">{{ $teamData['goals_against'] }}</td>
                                    <td class="px-2 py-2 text-center font-bold {{ $teamData['goal_diff'] > 0 ? 'text-green-600' : ($teamData['goal_diff'] < 0 ? 'text-red-600' : 'text-gray-600') }}">
                                        {{ $teamData['goal_diff'] > 0 ? '+' : '' }}{{ $teamData['goal_diff'] }}
                                    </td>
                                    <td class="px-2 py-2 text-center font-black text-soboa-blue text-lg">{{ $teamData['points'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-3 py-2 bg-gray-50 text-xs text-gray-500 flex flex-wrap gap-2">
                    <span><span class="inline-block w-3 h-3 bg-green-200 rounded mr-1"></span>Qualifié</span>
                    <span><span class="inline-block w-3 h-3 bg-yellow-200 rounded mr-1"></span>3ème</span>
                </div>
            </div>
        @endforeach
    </div>
</div>

@elseif($activeTab === 'bracket')
{{-- ==================== ONGLET PHASES FINALES ==================== --}}

<div class="max-w-7xl mx-auto space-y-6">
    
    {{-- 1/8 de Finale --}}
    @if(isset($knockoutMatches['round_of_16']) && $knockoutMatches['round_of_16']->count() > 0)
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-purple-600 to-purple-800 px-4 py-3">
            <h3 class="text-lg font-black text-white flex items-center gap-2">
                <span>🏟️</span> Huitièmes de Finale
            </h3>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach($knockoutMatches['round_of_16'] as $match)
                    @include('partials.bracket-match-public', ['match' => $match])
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- 1/4 de Finale --}}
    @if(isset($knockoutMatches['quarter_final']) && $knockoutMatches['quarter_final']->count() > 0)
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 px-4 py-3">
            <h3 class="text-lg font-black text-white flex items-center gap-2">
                <span>🏟️</span> Quarts de Finale
            </h3>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach($knockoutMatches['quarter_final'] as $match)
                    @include('partials.bracket-match-public', ['match' => $match])
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- 1/2 Finale --}}
    @if(isset($knockoutMatches['semi_final']) && $knockoutMatches['semi_final']->count() > 0)
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-pink-600 to-pink-800 px-4 py-3">
            <h3 class="text-lg font-black text-white flex items-center gap-2">
                <span>🏟️</span> Demi-Finales
            </h3>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-2xl mx-auto">
                @foreach($knockoutMatches['semi_final'] as $match)
                    @include('partials.bracket-match-public', ['match' => $match])
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- 3ème place & Finale --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- 3ème place --}}
        @if(isset($knockoutMatches['third_place']) && $knockoutMatches['third_place']->count() > 0)
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-orange-500 to-orange-700 px-4 py-3">
                <h3 class="text-lg font-black text-white flex items-center gap-2">
                    <span>🥉</span> 3ème Place
                </h3>
            </div>
            <div class="p-4">
                @foreach($knockoutMatches['third_place'] as $match)
                    @include('partials.bracket-match-public', ['match' => $match, 'large' => true])
                @endforeach
            </div>
        </div>
        @endif

        {{-- Finale --}}
        @if(isset($knockoutMatches['final']) && $knockoutMatches['final']->count() > 0)
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-yellow-500 via-yellow-600 to-yellow-700 px-4 py-3">
                <h3 class="text-lg font-black text-white flex items-center gap-2">
                    <span>🏆</span> FINALE
                </h3>
            </div>
            <div class="p-4">
                @foreach($knockoutMatches['final'] as $match)
                    @include('partials.bracket-match-public', ['match' => $match, 'large' => true])
                @endforeach
            </div>
        </div>
        @endif
    </div>

    @if((!isset($knockoutMatches['round_of_16']) || $knockoutMatches['round_of_16']->count() == 0) && (!isset($knockoutMatches['quarter_final']) || $knockoutMatches['quarter_final']->count() == 0) && (!isset($knockoutMatches['semi_final']) || $knockoutMatches['semi_final']->count() == 0) && (!isset($knockoutMatches['final']) || $knockoutMatches['final']->count() == 0))
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-8 text-center">
            <span class="text-6xl mb-4 block">🏆</span>
            <p class="text-xl font-bold text-white">Les phases finales n'ont pas encore commencé</p>
            <p class="text-white/70 mt-2">Les matchs à élimination directe apparaîtront ici</p>
        </div>
    @endif

</div>

@endif

</div>
</x-layouts.app>
