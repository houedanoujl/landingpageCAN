<x-layouts.app title="Admin - Calendrier des Matchs">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">

            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                        <span class="text-4xl">📅</span> Calendrier & Classement
                    </h1>
                    <p class="text-gray-600 mt-2">Calendrier des matchs, classement des groupes et phases finales</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg transition">
                    ← Retour
                </a>
            </div>

            <!-- Tabs Navigation -->
            <div class="bg-white rounded-xl shadow-lg mb-6 overflow-hidden">
                <div class="flex border-b">
                    <a href="{{ route('admin.calendar', ['tab' => 'calendar', 'month' => $date->month, 'year' => $date->year]) }}" 
                       class="flex-1 py-4 px-6 text-center font-bold transition {{ $tab === 'calendar' ? 'bg-soboa-blue text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <span class="text-xl mr-2">📅</span> Calendrier
                    </a>
                    <a href="{{ route('admin.calendar', ['tab' => 'standings', 'month' => $date->month, 'year' => $date->year]) }}" 
                       class="flex-1 py-4 px-6 text-center font-bold transition {{ $tab === 'standings' ? 'bg-soboa-blue text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <span class="text-xl mr-2">📊</span> Classement
                    </a>
                    <a href="{{ route('admin.calendar', ['tab' => 'bracket', 'month' => $date->month, 'year' => $date->year]) }}" 
                       class="flex-1 py-4 px-6 text-center font-bold transition {{ $tab === 'bracket' ? 'bg-soboa-blue text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <span class="text-xl mr-2">🏆</span> Phases Finales
                    </a>
                </div>
            </div>

            @if($tab === 'calendar')
                <!-- ==================== CALENDRIER TAB ==================== -->
                
                <!-- Navigation mois -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <a href="{{ route('admin.calendar', ['tab' => 'calendar', 'month' => $prevMonth->month, 'year' => $prevMonth->year]) }}"
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg transition">
                            ← {{ $prevMonth->locale('fr')->translatedFormat('F Y') }}
                        </a>

                        <h2 class="text-2xl font-black text-soboa-blue">
                            {{ $date->locale('fr')->translatedFormat('F Y') }}
                        </h2>

                        <a href="{{ route('admin.calendar', ['tab' => 'calendar', 'month' => $nextMonth->month, 'year' => $nextMonth->year]) }}"
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg transition">
                            {{ $nextMonth->locale('fr')->translatedFormat('F Y') }} →
                        </a>
                    </div>
                </div>

                <!-- Liste des matchs par jour -->
                @if($matches->isEmpty())
                    <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                        <span class="text-6xl mb-4 block">📭</span>
                        <p class="text-xl font-bold text-gray-500">Aucun match prévu pour ce mois</p>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach($matches as $dateKey => $dayMatches)
                            @php
                                $matchDate = \Carbon\Carbon::parse($dateKey);
                            @endphp

                            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                                <!-- Date header -->
                                <div class="bg-gradient-to-r from-soboa-blue to-blue-600 px-6 py-4">
                                    <h3 class="text-xl font-black text-white">
                                        {{ $matchDate->locale('fr')->translatedFormat('l j F Y') }}
                                    </h3>
                                </div>

                                <!-- Matches du jour -->
                                <div class="divide-y divide-gray-200">
                                    @foreach($dayMatches as $match)
                                        <div class="p-6 hover:bg-gray-50 transition">
                                            <div class="flex items-start justify-between gap-4">
                                                <!-- Match info -->
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-4 mb-3 flex-wrap">
                                                        <span class="text-2xl font-black text-soboa-blue">
                                                            {{ $match->match_date->format('H:i') }}
                                                        </span>

                                                        <div class="flex items-center gap-3">
                                                            @if($match->homeTeam)
                                                            <img src="{{ $match->homeTeam->flag_url }}" alt="" loading="lazy"
                                                                 class="w-8 h-6 rounded shadow">
                                                            @endif
                                                            <span class="font-bold text-lg">{{ $match->team_a }}</span>
                                                            
                                                            @if($match->status === 'finished')
                                                                <span class="font-black text-xl text-soboa-blue">{{ $match->score_a }} - {{ $match->score_b }}</span>
                                                            @else
                                                                <span class="text-gray-400">vs</span>
                                                            @endif
                                                            
                                                            <span class="font-bold text-lg">{{ $match->team_b }}</span>
                                                            @if($match->awayTeam)
                                                            <img src="{{ $match->awayTeam->flag_url }}" alt="" loading="lazy"
                                                                 class="w-8 h-6 rounded shadow">
                                                            @endif
                                                        </div>

                                                        @php
                                                            $phaseBadges = [
                                                                'group_stage' => ['text' => 'Poules', 'color' => 'blue'],
                                                                'round_of_32' => ['text' => '1/16', 'color' => 'teal'],
                                                                'round_of_16' => ['text' => '1/8', 'color' => 'purple'],
                                                                'quarter_final' => ['text' => '1/4', 'color' => 'indigo'],
                                                                'semi_final' => ['text' => '1/2', 'color' => 'pink'],
                                                                'third_place' => ['text' => '3ème', 'color' => 'orange'],
                                                                'final' => ['text' => 'Finale', 'color' => 'red'],
                                                            ];
                                                            $badge = $phaseBadges[$match->phase] ?? ['text' => $match->phase, 'color' => 'gray'];
                                                        @endphp

                                                        <span class="bg-{{ $badge['color'] }}-100 text-{{ $badge['color'] }}-700 text-xs font-bold px-3 py-1 rounded-full">
                                                            {{ $badge['text'] }}
                                                        </span>
                                                    </div>

                                                    <!-- PDV assignés -->
                                                    @if($match->animations->count() > 0)
                                                        <div class="ml-0 md:ml-28">
                                                            <div class="text-sm font-bold text-gray-600 mb-2">
                                                                📍 Points de vente ({{ $match->animations->count() }}) :
                                                            </div>
                                                            <div class="flex flex-wrap gap-2">
                                                                @foreach($match->animations->take(10) as $animation)
                                                                    <span class="bg-green-50 border border-green-200 text-green-800 text-xs font-medium px-3 py-1 rounded-full">
                                                                        {{ $animation->bar->name }}
                                                                        @if($animation->bar->zone)
                                                                            <span class="text-green-600">• {{ $animation->bar->zone }}</span>
                                                                        @endif
                                                                    </span>
                                                                @endforeach
                                                                @if($match->animations->count() > 10)
                                                                    <span class="bg-gray-100 text-gray-600 text-xs font-medium px-3 py-1 rounded-full">
                                                                        +{{ $match->animations->count() - 10 }} autre(s)
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="ml-0 md:ml-28 text-sm text-gray-400 italic">
                                                            Aucun point de vente assigné
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Actions -->
                                                <div class="flex flex-col gap-2">
                                                    <a href="{{ route('admin.edit-match', $match->id) }}"
                                                       class="bg-soboa-orange hover:bg-soboa-orange/90 text-black font-bold px-4 py-2 rounded-lg text-sm transition whitespace-nowrap">
                                                        ✏️ Modifier
                                                    </a>
                                                    <a href="{{ route('admin.match-predictions', $match->id) }}"
                                                       class="bg-purple-600 hover:bg-purple-700 text-white font-bold px-4 py-2 rounded-lg text-sm transition whitespace-nowrap">
                                                        📊 Pronostics
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            @elseif($tab === 'standings')
                <!-- ==================== CLASSEMENT TAB ==================== -->
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @foreach($groupedStandings as $groupName => $teams)
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                            <div class="bg-gradient-to-r from-soboa-blue to-blue-600 px-6 py-4">
                                <h3 class="text-xl font-black text-white">
                                    Groupe {{ $groupName }}
                                </h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">#</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Équipe</th>
                                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">J</th>
                                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">G</th>
                                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">N</th>
                                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">P</th>
                                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">BP</th>
                                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">BC</th>
                                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">+/-</th>
                                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Pts</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($teams as $index => $teamData)
                                            <tr class="{{ $index < 2 ? 'bg-green-50' : ($index == 2 ? 'bg-yellow-50' : '') }}">
                                                <td class="px-4 py-3 font-bold text-gray-600">{{ $index + 1 }}</td>
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center gap-2">
                                                        <img src="{{ $teamData['team']->flag_url }}" alt="" loading="lazy"
                                                             class="w-6 h-4 rounded shadow">
                                                        <span class="font-bold text-gray-800">{{ $teamData['team']->name }}</span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-center text-gray-600">{{ $teamData['played'] }}</td>
                                                <td class="px-4 py-3 text-center text-green-600 font-bold">{{ $teamData['wins'] }}</td>
                                                <td class="px-4 py-3 text-center text-gray-600">{{ $teamData['draws'] }}</td>
                                                <td class="px-4 py-3 text-center text-red-600">{{ $teamData['losses'] }}</td>
                                                <td class="px-4 py-3 text-center text-gray-600">{{ $teamData['goals_for'] }}</td>
                                                <td class="px-4 py-3 text-center text-gray-600">{{ $teamData['goals_against'] }}</td>
                                                <td class="px-4 py-3 text-center font-bold {{ $teamData['goal_diff'] > 0 ? 'text-green-600' : ($teamData['goal_diff'] < 0 ? 'text-red-600' : 'text-gray-600') }}">
                                                    {{ $teamData['goal_diff'] > 0 ? '+' : '' }}{{ $teamData['goal_diff'] }}
                                                </td>
                                                <td class="px-4 py-3 text-center font-black text-soboa-blue text-lg">{{ $teamData['points'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="px-4 py-2 bg-gray-50 text-xs text-gray-500">
                                <span class="inline-block w-3 h-3 bg-green-200 rounded mr-1"></span> Qualifié directement
                                <span class="inline-block w-3 h-3 bg-yellow-200 rounded mr-1 ml-3"></span> Meilleur 3ème
                            </div>
                        </div>
                    @endforeach
                </div>

            @elseif($tab === 'bracket')
                <!-- ==================== BRACKET TAB ==================== -->
                
                <div class="space-y-8">
                    
                    <!-- 1/16 de Finale -->
                    @if(isset($knockoutMatches['round_of_32']) && $knockoutMatches['round_of_32']->count() > 0)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-teal-600 to-teal-800 px-6 py-4">
                            <h3 class="text-xl font-black text-white flex items-center gap-2">
                                <span>🏟️</span> Seizièmes de Finale
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                @foreach($knockoutMatches['round_of_32'] as $match)
                                    @include('admin.partials.bracket-match', ['match' => $match])
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 1/8 de Finale -->
                    @if(isset($knockoutMatches['round_of_16']) && $knockoutMatches['round_of_16']->count() > 0)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-600 to-purple-800 px-6 py-4">
                            <h3 class="text-xl font-black text-white flex items-center gap-2">
                                <span>🏟️</span> Huitièmes de Finale
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                @foreach($knockoutMatches['round_of_16'] as $match)
                                    @include('admin.partials.bracket-match', ['match' => $match])
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 1/4 de Finale -->
                    @if(isset($knockoutMatches['quarter_final']) && $knockoutMatches['quarter_final']->count() > 0)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 px-6 py-4">
                            <h3 class="text-xl font-black text-white flex items-center gap-2">
                                <span>🏟️</span> Quarts de Finale
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                @foreach($knockoutMatches['quarter_final'] as $match)
                                    @include('admin.partials.bracket-match', ['match' => $match])
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 1/2 Finale -->
                    @if(isset($knockoutMatches['semi_final']) && $knockoutMatches['semi_final']->count() > 0)
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-pink-600 to-pink-800 px-6 py-4">
                            <h3 class="text-xl font-black text-white flex items-center gap-2">
                                <span>🏟️</span> Demi-Finales
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto">
                                @foreach($knockoutMatches['semi_final'] as $match)
                                    @include('admin.partials.bracket-match', ['match' => $match])
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Match pour la 3ème place & Finale -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- 3ème place -->
                        @if(isset($knockoutMatches['third_place']) && $knockoutMatches['third_place']->count() > 0)
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                            <div class="bg-gradient-to-r from-orange-500 to-orange-700 px-6 py-4">
                                <h3 class="text-xl font-black text-white flex items-center gap-2">
                                    <span>🥉</span> Match pour la 3ème Place
                                </h3>
                            </div>
                            <div class="p-6">
                                @foreach($knockoutMatches['third_place'] as $match)
                                    @include('admin.partials.bracket-match', ['match' => $match, 'large' => true])
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Finale -->
                        @if(isset($knockoutMatches['final']) && $knockoutMatches['final']->count() > 0)
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                            <div class="bg-gradient-to-r from-yellow-500 via-yellow-600 to-yellow-700 px-6 py-4">
                                <h3 class="text-xl font-black text-white flex items-center gap-2">
                                    <span>🏆</span> FINALE
                                </h3>
                            </div>
                            <div class="p-6">
                                @foreach($knockoutMatches['final'] as $match)
                                    @include('admin.partials.bracket-match', ['match' => $match, 'large' => true])
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    @if($knockoutMatches->flatten()->isEmpty())
                        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                        <span class="text-6xl mb-4 block">📭</span>
                            <p class="text-xl font-bold text-gray-500">Les phases finales n'ont pas encore commencé</p>
                            <p class="text-gray-400 mt-2">Les matchs à élimination directe apparaîtront ici une fois programmés</p>
                        </div>
                    @endif

                </div>

            @endif

        </div>
    </div>
</x-layouts.app>
