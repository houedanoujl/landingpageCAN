<x-layouts.app title="Admin - Pronostics du Match">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">

            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('admin.matches') }}" class="text-soboa-orange hover:underline font-bold mb-2 inline-block">
                    ← Retour aux matchs
                </a>
                <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                    <span class="text-4xl">📊</span> Pronostics du Match
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
                                <span class="text-xl" style="display:none;">🏴</span>
                            @else
                                <span class="text-xl">🏴</span>
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
                                <span class="text-xl" style="display:none;">🏴</span>
                            @else
                                <span class="text-xl">🏴</span>
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
                            'round_of_32' => '1/16e de finale',
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
                    <div class="text-6xl mb-4">🤷</div>
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
                                            <button type="button"
                                                onclick="showPointsHistory({{ $prediction->user->id }}, '{{ addslashes($prediction->user->name) }}')"
                                                class="mt-1 text-xs text-soboa-orange hover:underline font-semibold">
                                                📜 Historique des points
                                            </button>
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
                                @php
                                    $breakdown = $pointsBreakdown[$prediction->user_id] ?? collect();
                                    $earned = (int) $prediction->points_earned;
                                @endphp
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-3 py-1 inline-flex text-sm font-bold rounded-full
                                        {{ $earned > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                        +{{ $earned }} pts
                                    </span>
                                    <div class="mt-1 flex flex-wrap gap-1 justify-center">
                                        @if($breakdown->get('prediction_participation'))
                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">Participation +{{ $breakdown->get('prediction_participation') }}</span>
                                        @endif
                                        @if($breakdown->get('prediction_winner'))
                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">Vainqueur +{{ $breakdown->get('prediction_winner') }}</span>
                                        @endif
                                        @if($breakdown->get('prediction_exact'))
                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-orange-100 text-orange-800">Score exact +{{ $breakdown->get('prediction_exact') }}</span>
                                        @endif
                                    </div>
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
                    @php
                        $winnerCount = $pointsBreakdown->filter(fn ($b) => $b->get('prediction_winner'))->count();
                        $exactCount = $pointsBreakdown->filter(fn ($b) => $b->get('prediction_exact'))->count();
                        $totalDistributed = (int) $predictions->sum('points_earned');
                    @endphp
                    <div class="grid grid-cols-4 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Pronostics corrects (vainqueur)</p>
                            <p class="text-lg font-bold text-green-600">
                                {{ $winnerCount }} / {{ $predictions->count() }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500">Scores exacts</p>
                            <p class="text-lg font-bold text-blue-600">
                                {{ $exactCount }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500">Points moyens</p>
                            <p class="text-lg font-bold text-soboa-orange">
                                {{ $predictions->count() ? number_format($totalDistributed / $predictions->count(), 1) : '0.0' }} pts
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500">Total points distribués</p>
                            <p class="text-lg font-bold text-purple-600">
                                {{ $totalDistributed }} pts
                            </p>
                        </div>
                    </div>
                </div>
                @endif
                @endif
            </div>

        </div>
    </div>

    <!-- Modale historique des points -->
    <div id="pointsHistoryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <div>
                    <h3 class="text-lg font-black text-soboa-blue">📜 Historique des points</h3>
                    <p id="pointsHistoryUser" class="text-sm text-gray-500"></p>
                </div>
                <button type="button" onclick="closePointsHistory()" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
            </div>
            <div id="pointsHistoryBody" class="overflow-y-auto px-6 py-4 flex-1">
                <p class="text-center text-gray-400 py-8">Chargement…</p>
            </div>
        </div>
    </div>

    <script>
        function closePointsHistory() {
            const modal = document.getElementById('pointsHistoryModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        async function showPointsHistory(userId, userName) {
            const modal = document.getElementById('pointsHistoryModal');
            const body = document.getElementById('pointsHistoryBody');
            const userEl = document.getElementById('pointsHistoryUser');

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            userEl.textContent = userName;
            body.innerHTML = '<p class="text-center text-gray-400 py-8">Chargement…</p>';

            try {
                const res = await fetch(`/admin/users/${userId}/points-history`, {
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();

                userEl.textContent = `${data.user.name} · ${data.user.phone} · ${data.user.points_total} pts au total`;

                if (!data.logs.length) {
                    body.innerHTML = '<p class="text-center text-gray-400 py-8">Aucun point enregistré.</p>';
                    return;
                }

                const rows = data.logs.map(log => `
                    <tr class="border-b border-gray-100">
                        <td class="py-2 pr-3 text-sm text-gray-500 whitespace-nowrap">${log.date}</td>
                        <td class="py-2 pr-3">
                            <div class="text-sm font-medium text-gray-800">${log.source_label}</div>
                            ${log.match ? `<div class="text-xs text-gray-400">${log.match}</div>` : ''}
                            ${log.bar ? `<div class="text-xs text-gray-400">${log.bar}</div>` : ''}
                        </td>
                        <td class="py-2 text-right font-bold ${log.points > 0 ? 'text-green-600' : 'text-red-600'}">${log.points > 0 ? '+' : ''}${log.points}</td>
                    </tr>
                `).join('');

                body.innerHTML = `
                    <table class="min-w-full">
                        <thead>
                            <tr class="text-left text-xs uppercase text-gray-400">
                                <th class="py-2 pr-3">Date</th>
                                <th class="py-2 pr-3">Source</th>
                                <th class="py-2 text-right">Points</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                `;
            } catch (e) {
                body.innerHTML = '<p class="text-center text-red-500 py-8">Erreur de chargement de l\'historique.</p>';
            }
        }

        document.getElementById('pointsHistoryModal').addEventListener('click', function (e) {
            if (e.target === this) closePointsHistory();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closePointsHistory();
        });
    </script>
</x-layouts.app>
