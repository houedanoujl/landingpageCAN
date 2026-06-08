<x-layouts.app title="Classement Hebdomadaire - Admin">
    <div class="min-h-screen bg-gray-100">
        <!-- Header -->
        <div class="bg-gradient-to-r from-soboa-blue to-blue-800 text-white py-6">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold">Classement Hebdomadaire</h1>
                        <p class="text-blue-200 mt-1">
                            {{ $weekStart->format('d/m/Y') }} - {{ $weekEnd->format('d/m/Y') }}
                        </p>
                    </div>
                    <a href="{{ route('admin.dashboard') }}" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Retour
                    </a>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-6">
            <!-- Filtres et statistiques -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
                <!-- Sélecteur de semaine -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm p-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Sélectionner une période</label>
                        <form method="GET" action="{{ route('admin.weekly-leaderboard') }}">
                            <select name="period" onchange="this.form.submit()" class="w-full border-gray-300 rounded-lg focus:ring-soboa-orange focus:border-soboa-orange">
                                @foreach($availableWeeks as $periodValue => $periodLabel)
                                    <option value="{{ $periodValue }}" {{ $selectedWeek === $periodValue ? 'selected' : '' }}>
                                        {{ $periodLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="lg:col-span-3 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
                        <div class="text-3xl font-black text-soboa-blue">{{ $weeklyStats['total_participants'] }}</div>
                        <div class="text-sm text-gray-500">Participants</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
                        <div class="text-3xl font-black text-yellow-500">{{ number_format($weeklyStats['total_points']) }}</div>
                        <div class="text-sm text-gray-500">Points distribués</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
                        <div class="text-3xl font-black text-green-500">{{ $weeklyStats['total_predictions'] }}</div>
                        <div class="text-sm text-gray-500">Pronostics</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
                        <div class="text-3xl font-black text-purple-500">{{ $weeklyStats['total_checkins'] }}</div>
                        <div class="text-sm text-gray-500">Check-ins</div>
                    </div>
                </div>
            </div>

            <!-- Top 3 -->
            @if($weeklyStats['total_participants'] >= 3)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                @foreach($weeklyData->take(3) as $index => $topUser)
                    @php
                        $podiumColors = [
                            0 => 'from-yellow-400 to-yellow-600',
                            1 => 'from-gray-300 to-gray-500',
                            2 => 'from-orange-400 to-orange-600',
                        ];
                        $medals = ['', '', ''];
                    @endphp
                    <div class="bg-gradient-to-br {{ $podiumColors[$index] }} rounded-xl shadow-lg p-6 text-white {{ $index === 0 ? 'md:order-2' : ($index === 1 ? 'md:order-1' : 'md:order-3') }}">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-4xl">{{ $medals[$index] }}</span>
                            <span class="text-3xl font-black">{{ $topUser->weekly_points }} pts</span>
                        </div>
                        <h3 class="text-xl font-bold truncate">{{ $topUser->name }}</h3>
                        <p class="text-white/80 text-sm">{{ $topUser->phone }}</p>
                        <div class="mt-3 flex gap-4 text-sm">
                            <span>{{ $topUser->weekly_predictions }} pronostics</span>
                            <span>{{ $topUser->weekly_checkins }} check-ins</span>
                        </div>
                        <a href="{{ route('admin.weekly-leaderboard-user-details', $topUser->id) }}?period={{ $selectedWeek }}" 
                           class="mt-4 inline-block bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm transition">
                            Voir détails →
                        </a>
                    </div>
                @endforeach
            </div>
            @endif

            <!-- Actions -->
            <div class="flex justify-end mb-4">
                <a href="{{ route('admin.export-weekly-leaderboard') }}?period={{ $selectedWeek }}" 
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Exporter CSV
                </a>
            </div>

            <!-- Tableau complet -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Rang</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Utilisateur</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Contact</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Points Semaine</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Points Total</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Pronostics</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Check-ins</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($weeklyData as $user)
                                <tr class="hover:bg-gray-50 {{ $user->rank <= 3 ? 'bg-yellow-50' : ($user->rank <= 15 ? 'bg-green-50/50' : '') }}">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            @if($user->rank === 1)
                                                <span class="text-2xl"></span>
                                            @elseif($user->rank === 2)
                                                <span class="text-2xl"></span>
                                            @elseif($user->rank === 3)
                                                <span class="text-2xl"></span>
                                            @elseif($user->rank <= 15)
                                                <span class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center font-bold text-white">
                                                    {{ $user->rank }}
                                                </span>
                                            @else
                                                <span class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center font-bold text-gray-600">
                                                    {{ $user->rank }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $user->id }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm text-gray-600">{{ $user->phone }}</div>
                                        @if($user->email)
                                            <div class="text-xs text-gray-400">{{ $user->email }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-yellow-100 text-yellow-800">
                                            {{ $user->weekly_points }} pts
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-gray-600 font-medium">{{ number_format($user->points_total) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $user->weekly_predictions }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ $user->weekly_checkins }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('admin.weekly-leaderboard-user-details', $user->id) }}?period={{ $selectedWeek }}" 
                                           class="text-soboa-blue hover:text-blue-800 font-medium text-sm">
                                            Détails →
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                                        <div class="text-4xl mb-2"></div>
                                        Aucune activité cette semaine
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Légende -->
            <div class="mt-4 flex flex-wrap items-center gap-4 text-sm">
                <span class="font-bold text-gray-700">Légende :</span>
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 bg-yellow-50 border border-yellow-200 rounded"></span>
                    <span class="text-gray-600">Top 3 (podium)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 bg-green-50 border border-green-200 rounded"></span>
                    <span class="text-gray-600">Top 4-15 (gagnants)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 bg-white border border-gray-200 rounded"></span>
                    <span class="text-gray-600">Autres participants</span>
                </div>
            </div>

            <!-- Info mise à jour -->
            <div class="mt-6 text-center text-sm text-gray-500">
                <p>Les données sont mises à jour en temps réel. Dernière consultation : {{ now()->format('d/m/Y à H:i') }}</p>
                <p class="mt-1 text-green-600 font-medium">Les 15 premiers de chaque semaine sont gagnants</p>
            </div>
        </div>
    </div>
</x-layouts.app>
