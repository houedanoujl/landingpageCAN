<x-layouts.app title="Admin - Pronostics du Match">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">

            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('admin.matches') }}" class="text-soboa-orange hover:underline font-bold mb-2 inline-block">
                    ← Retour aux matchs
                </a>
                <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                    <span class="text-4xl"></span> Pronostics du Match
                </h1>
            </div>

            <!-- Informations du match -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-4">
                        @if($match->homeTeam)
                        <div class="flex items-center gap-2">
                            @if($match->homeTeam->iso_code)
                                <img src="https://flagicons.lipis.dev/flags/4x3/{{ strtolower($match->homeTeam->iso_code) }}.svg"
                                     alt="{{ $match->homeTeam->name }}"
                                     class="w-12 h-8 object-cover rounded shadow"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                                <span class="text-xl" style="display:none;"></span>
                            @else
                                <span class="text-xl"></span>
                            @endif
                            <span class="font-bold text-lg">{{ $match->homeTeam->name }}</span>
                        </div>
                        @else
                        <span class="font-bold text-lg">{{ $match->team_a }}</span>
                        @endif

                        <span class="text-2xl font-black text-gray-400">VS</span>

                        @if($match->awayTeam)
                        <div class="flex items-center gap-2">
                            @if($match->awayTeam->iso_code)
                                <img src="https://flagicons.lipis.dev/flags/4x3/{{ strtolower($match->awayTeam->iso_code) }}.svg"
                                     alt="{{ $match->awayTeam->name }}"
                                     class="w-12 h-8 object-cover rounded shadow"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                                <span class="text-xl" style="display:none;"></span>
                            @else
                                <span class="text-xl"></span>
                            @endif
                            <span class="font-bold text-lg">{{ $match->awayTeam->name }}</span>
                        </div>
                        @else
                        <span class="font-bold text-lg">{{ $match->team_b }}</span>
                        @endif
                    </div>

                    <div class="text-right">
                        <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($match->match_date)->locale('fr')->isoFormat('D MMM YYYY - HH:mm') }}</div>
                        @if($match->status === 'finished')
                        <div class="mt-2">
                            <span class="text-2xl font-black text-soboa-blue">{{ $match->score_a ?? '-' }}</span>
                            <span class="text-gray-400 mx-2">-</span>
                            <span class="text-2xl font-black text-soboa-blue">{{ $match->score_b ?? '-' }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-4 text-sm">
                    @if($match->phase)
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-semibold">
                        {{ match($match->phase) {
                            'group_stage' => 'Phase de poules',
                            'round_of_16' => '1/8e de finale',
                            'quarter_final' => 'Quart de finale',
                            'semi_final' => 'Demi-finale',
                            'third_place' => '3e place',
                            'final' => 'Finale',
                            default => $match->phase
                        } }}
                    </span>
                    @endif

                    @if($match->group_name)
                    <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full font-semibold">
                        Groupe {{ $match->group_name }}
                    </span>
                    @endif

                    <span class="px-3 py-1 rounded-full font-semibold {{ match($match->status) {
                        'scheduled' => 'bg-gray-100 text-gray-800',
                        'live' => 'bg-green-100 text-green-800',
                        'finished' => 'bg-blue-100 text-blue-800',
                        default => 'bg-gray-100 text-gray-800'
                    } }}">
                        {{ match($match->status) {
                            'scheduled' => 'À venir',
                            'live' => 'En cours',
                            'finished' => 'Terminé',
                            default => $match->status
                        } }}
                    </span>

                    <span class="text-gray-600">
                        <strong>{{ $predictions->count() }}</strong> pronostic(s)
                    </span>
                </div>
            </div>

            <!-- Liste des pronostics -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                @if($predictions->isEmpty())
                <div class="p-8 text-center text-gray-500">
                    <div class="text-6xl mb-4"></div>
                    <p class="text-lg font-semibold">Aucun pronostic pour ce match</p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Utilisateur
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pronostic
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Vainqueur prédit
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date du pronostic
                                </th>
                                @if($match->status === 'finished')
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Points gagnés
                                </th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($predictions as $prediction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $prediction->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $prediction->user->phone }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="text-xl font-black text-soboa-blue">
                                        {{ $prediction->score_a ?? '-' }} - {{ $prediction->score_b ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $prediction->predicted_winner === 'draw' ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ match($prediction->predicted_winner) {
                                            'home' => $match->team_a,
                                            'away' => $match->team_b,
                                            'draw' => 'Match nul',
                                            default => '-'
                                        } }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($prediction->created_at)->locale('fr')->isoFormat('D MMM YYYY [à] HH:mm') }}
                                    <div class="text-xs text-gray-400">
                                        {{ \Carbon\Carbon::parse($prediction->created_at)->diffForHumans() }}
                                    </div>
                                </td>
                                @if($match->status === 'finished')
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($prediction->points_awarded !== null)
                                    <span class="px-3 py-1 inline-flex text-sm font-bold rounded-full
                                        {{ $prediction->points_awarded > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $prediction->points_awarded > 0 ? '+' : '' }}{{ $prediction->points_awarded }} pts
                                    </span>
                                    @else
                                    <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Statistiques -->
                @if($match->status === 'finished' && $predictions->isNotEmpty())
                <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                    <h3 class="font-bold text-gray-700 mb-3">Statistiques</h3>
                    <div class="grid grid-cols-4 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Pronostics corrects (vainqueur)</p>
                            <p class="text-lg font-bold text-green-600">
                                {{ $predictions->where('points_awarded', '>', 0)->count() }} / {{ $predictions->count() }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500">Scores exacts</p>
                            <p class="text-lg font-bold text-blue-600">
                                {{ $predictions->where('score_a', $match->score_a)->where('score_b', $match->score_b)->count() }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500">Points moyens</p>
                            <p class="text-lg font-bold text-soboa-orange">
                                {{ number_format($predictions->avg('points_awarded'), 1) }} pts
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500">Total points distribués</p>
                            <p class="text-lg font-bold text-purple-600">
                                {{ $predictions->sum('points_awarded') }} pts
                            </p>
                        </div>
                    </div>
                </div>
                @endif
                @endif
            </div>

        </div>
    </div>
</x-layouts.app>
