<x-layouts.app title="Détails {{ $user->name }} - Classement Hebdomadaire">
    <div class="min-h-screen bg-gray-100">
        <!-- Header -->
        <div class="bg-gradient-to-r from-soboa-blue to-blue-800 text-white py-6">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold">{{ $user->name }}</h1>
                        <p class="text-blue-200 mt-1">
                            Période du {{ $weekStart->format('d/m/Y') }} au {{ $weekEnd->format('d/m/Y') }}
                        </p>
                    </div>
                    <a href="{{ route('admin.weekly-leaderboard') }}?period={{ $selectedWeek }}" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Retour au classement
                    </a>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-6">
            <!-- Carte profil -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Informations utilisateur -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-16 h-16 bg-gradient-to-br from-soboa-blue to-blue-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                            <p class="text-gray-500">ID: {{ $user->id }}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3 border-t pt-4">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Téléphone</span>
                            <span class="font-medium">{{ $user->phone }}</span>
                        </div>
                        @if($user->email)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Email</span>
                            <span class="font-medium text-sm">{{ $user->email }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-500">Points totaux</span>
                            <span class="font-bold text-soboa-blue">{{ number_format($user->points_total) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Inscrit le</span>
                            <span class="font-medium">{{ $user->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Dernière connexion</span>
                            <span class="font-medium">{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Statistiques de la semaine -->
                <div class="lg:col-span-2">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-xl p-4 text-white">
                            <div class="text-3xl font-black">{{ $userWeeklyStats['total_points'] }}</div>
                            <div class="text-yellow-100 text-sm">Points cette semaine</div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-4">
                            <div class="text-3xl font-black text-gray-900">{{ $rank }}<sup class="text-lg">e</sup></div>
                            <div class="text-gray-500 text-sm">Position</div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-4">
                            <div class="text-3xl font-black text-blue-600">{{ $userWeeklyStats['predictions_count'] }}</div>
                            <div class="text-gray-500 text-sm">Pronostics</div>
                            @if($userWeeklyStats['predictions_count'] > 0)
                                <div class="text-xs text-green-600 mt-1">
                                    {{ $userWeeklyStats['correct_predictions'] }} corrects
                                </div>
                            @endif
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-4">
                            <div class="text-3xl font-black text-purple-600">{{ $userWeeklyStats['checkins_count'] }}</div>
                            <div class="text-gray-500 text-sm">Check-ins</div>
                        </div>
                    </div>
                    
                    <!-- Résumé activité -->
                    <div class="bg-white rounded-xl shadow-sm p-4 mt-4">
                        <h3 class="font-bold text-gray-700 mb-3">Résumé de l'activité</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                            <div>
                                <div class="text-lg font-bold text-green-600">{{ $userWeeklyStats['daily_logins'] }}</div>
                                <div class="text-xs text-gray-500">Connexions quotidiennes</div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-blue-600">{{ $pointLogs->where('source', 'prediction')->sum('points') }}</div>
                                <div class="text-xs text-gray-500">Points pronostics</div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-purple-600">{{ $pointLogs->where('source', 'check_in')->sum('points') }}</div>
                                <div class="text-xs text-gray-500">Points check-ins</div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-orange-600">{{ $pointLogs->whereNotIn('source', ['prediction', 'check_in', 'daily_login'])->sum('points') }}</div>
                                <div class="text-xs text-gray-500">Autres bonus</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historique des points -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Points gagnés -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b">
                        <h3 class="font-bold text-gray-700">Historique des points ({{ $pointLogs->count() }} entrées)</h3>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500">Source</th>
                                    <th class="px-4 py-2 text-right text-xs font-bold text-gray-500">Points</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($pointLogs as $log)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm text-gray-600">
                                            {{ $log->created_at->format('d/m H:i') }}
                                        </td>
                                        <td class="px-4 py-2">
                                            @php
                                                $sourceLabels = [
                                                    'prediction' => ['label' => 'Pronostic', 'color' => 'blue'],
                                                    'check_in' => ['label' => 'Check-in', 'color' => 'purple'],
                                                    'daily_login' => ['label' => 'Connexion', 'color' => 'green'],
                                                    'bonus' => ['label' => 'Bonus', 'color' => 'yellow'],
                                                    'perfect_prediction' => ['label' => 'Score exact', 'color' => 'orange'],
                                                ];
                                                $source = $sourceLabels[$log->source] ?? ['label' => $log->source, 'color' => 'gray'];
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $source['color'] }}-100 text-{{ $source['color'] }}-800">
                                                {{ $source['label'] }}
                                            </span>
                                            @if($log->match)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ $log->match->homeTeam->name ?? '?' }} vs {{ $log->match->awayTeam->name ?? '?' }}
                                                </div>
                                            @endif
                                            @if($log->bar)
                                                <div class="text-xs text-gray-400">{{ $log->bar->name }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <span class="font-bold {{ $log->points > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $log->points > 0 ? '+' : '' }}{{ $log->points }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                                            Aucun point gagné cette semaine
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pronostics -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b">
                        <h3 class="font-bold text-gray-700">Pronostics ({{ $predictions->count() }})</h3>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-bold text-gray-500">Match</th>
                                    <th class="px-4 py-2 text-center text-xs font-bold text-gray-500">Pronostic</th>
                                    <th class="px-4 py-2 text-center text-xs font-bold text-gray-500">Résultat</th>
                                    <th class="px-4 py-2 text-right text-xs font-bold text-gray-500">Points</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($predictions as $prediction)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $prediction->match->homeTeam->name ?? '?' }} vs {{ $prediction->match->awayTeam->name ?? '?' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $prediction->created_at->format('d/m H:i') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <span class="font-bold text-soboa-blue">
                                                {{ $prediction->predicted_home_score }} - {{ $prediction->predicted_away_score }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            @if($prediction->match->status === 'finished')
                                                <span class="font-bold text-gray-700">
                                                    {{ $prediction->match->home_score }} - {{ $prediction->match->away_score }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 text-sm">En attente</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            @if($prediction->points_earned !== null)
                                                <span class="font-bold {{ $prediction->points_earned > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                                    {{ $prediction->points_earned > 0 ? '+' . $prediction->points_earned : '0' }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                            Aucun pronostic cette semaine
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            @if($isAdmin)
            <div class="mt-6 flex gap-4 justify-center">
                <a href="{{ route('admin.edit-user', $user->id) }}" class="bg-soboa-blue hover:bg-blue-800 text-white px-6 py-2 rounded-lg transition">
                    Modifier l'utilisateur
                </a>
                <a href="{{ route('admin.point-logs') }}?user_id={{ $user->id }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition">
                    Voir tout l'historique
                </a>
            </div>
            @endif
        </div>
    </div>
</x-layouts.app>
