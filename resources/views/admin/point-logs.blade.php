<x-layouts.app title="Historique des points">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            <div class="space-y-6">
                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Historique des interactions</h1>
                        <p class="text-gray-600">Connexions, pronostics, check-ins et attributions de points</p>
                    </div>
                    <a href="/admin" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        ← Retour au Dashboard
                    </a>
                </div>

        <!-- Statistiques -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-2xl font-bold text-soboa-blue">{{ number_format($stats['total']) }}</div>
                <div class="text-sm text-gray-500">Total interactions</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-2xl font-bold text-green-600">{{ number_format($stats['today']) }}</div>
                <div class="text-sm text-gray-500">Aujourd'hui</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-2xl font-bold text-orange-600">{{ number_format($stats['predictions']) }}</div>
                <div class="text-sm text-gray-500">Pronostics</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-2xl font-bold text-purple-600">{{ number_format($stats['checkins']) }}</div>
                <div class="text-sm text-gray-500">Check-ins GPS</div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-lg shadow p-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Utilisateur</label>
                    <select name="user_id" class="w-full border border-gray-300 rounded-lg p-2">
                        <option value="">Tous</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" class="w-full border border-gray-300 rounded-lg p-2">
                        <option value="">Tous</option>
                        <option value="prediction" {{ request('type') == 'prediction' ? 'selected' : '' }}>Pronostic</option>
                        <option value="check_in" {{ request('type') == 'check_in' ? 'selected' : '' }}>Check-in GPS</option>
                        <option value="bonus" {{ request('type') == 'bonus' ? 'selected' : '' }}>Bonus</option>
                        <option value="manual" {{ request('type') == 'manual' ? 'selected' : '' }}>Manuel</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date début</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border border-gray-300 rounded-lg p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date fin</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border border-gray-300 rounded-lg p-2">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-soboa-blue text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        Filtrer
                    </button>
                    <a href="{{ route('admin.point-logs') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tableau des logs -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Points</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Match / Bar</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    {{ $log->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($log->user)
                                        <div class="text-sm font-medium text-gray-900">{{ $log->user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $log->user->phone }}</div>
                                    @else
                                        <span class="text-gray-400">Utilisateur supprimé</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @switch($log->source)
                                        @case('prediction')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Pronostic
                                            </span>
                                            @break
                                        @case('check_in')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                Check-in GPS
                                            </span>
                                            @break
                                        @case('bonus')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Bonus
                                            </span>
                                            @break
                                        @default
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ $log->source ?? 'Autre' }}
                                            </span>
                                    @endswitch
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-sm font-bold {{ $log->points > 0 ? 'text-green-600' : 'text-gray-500' }}">
                                        {{ $log->points > 0 ? '+' : '' }}{{ $log->points }} pts
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($log->match)
                                        <div class="text-sm text-gray-900">
                                            {{ $log->match->homeTeam->name ?? $log->match->team_a }} vs {{ $log->match->awayTeam->name ?? $log->match->team_b }}
                                        </div>
                                    @endif
                                    @if($log->bar)
                                        <div class="text-xs text-purple-600">
                                            📍 {{ $log->bar->name }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate">
                                    {{ $log->description ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    Aucune interaction trouvée
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $logs->links() }}
            </div>
        </div>
            </div>
        </div>
    </div>
</x-layouts.app>
