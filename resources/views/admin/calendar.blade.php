<x-layouts.app title="Admin - Calendrier des Matchs">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">

            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                        <span class="text-4xl">📅</span> Calendrier des Matchs
                    </h1>
                    <p class="text-gray-600 mt-2">Vue chronologique des matchs et leurs points de vente</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg transition">
                    ← Retour
                </a>
            </div>

            <!-- Navigation mois -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.calendar', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}"
                       class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg transition">
                        ← {{ $prevMonth->locale('fr')->translatedFormat('F Y') }}
                    </a>

                    <h2 class="text-2xl font-black text-soboa-blue">
                        {{ $date->locale('fr')->translatedFormat('F Y') }}
                    </h2>

                    <a href="{{ route('admin.calendar', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}"
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
                                                <div class="flex items-center gap-4 mb-3">
                                                    <span class="text-2xl font-black text-soboa-blue">
                                                        {{ $match->match_date->format('H:i') }}
                                                    </span>

                                                    <div class="flex items-center gap-3">
                                                        @if($match->homeTeam)
                                                        <img src="https://flagcdn.com/w40/{{ $match->homeTeam->iso_code }}.png"
                                                             class="w-8 h-6 rounded shadow">
                                                        @endif
                                                        <span class="font-bold text-lg">{{ $match->team_a }}</span>
                                                        <span class="text-gray-400">vs</span>
                                                        <span class="font-bold text-lg">{{ $match->team_b }}</span>
                                                        @if($match->awayTeam)
                                                        <img src="https://flagcdn.com/w40/{{ $match->awayTeam->iso_code }}.png"
                                                             class="w-8 h-6 rounded shadow">
                                                        @endif
                                                    </div>

                                                    @php
                                                        $phaseBadges = [
                                                            'group_stage' => ['text' => 'Poules', 'color' => 'blue'],
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
                                                    <div class="ml-28">
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
                                                    <div class="ml-28 text-sm text-gray-400 italic">
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

        </div>
    </div>
</x-layouts.app>
