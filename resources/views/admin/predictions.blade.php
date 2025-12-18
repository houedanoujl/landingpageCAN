<x-layouts.app title="Admin - Pronostics">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">

            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                        <span class="text-4xl">🎯</span> Pronostics
                    </h1>
                    <p class="text-gray-600 mt-2">Consultez tous les pronostics des utilisateurs</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg transition">
                    ← Retour
                </a>
            </div>

            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
            @endif

            <!-- Statistiques de points -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-soboa-blue to-blue-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium mb-1">Total Pronostics</p>
                            <p class="text-4xl font-black">{{ $totalPredictions }}</p>
                        </div>
                        <div class="text-5xl opacity-20">🎯</div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-soboa-orange to-orange-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm font-medium mb-1">Points Totaux</p>
                            <p class="text-4xl font-black">{{ number_format($totalPointsAwarded) }}</p>
                        </div>
                        <div class="text-5xl opacity-20">⭐</div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium mb-1">Moyenne pts/pari</p>
                            <p class="text-4xl font-black">{{ $avgPointsPerPrediction }}</p>
                        </div>
                        <div class="text-5xl opacity-20">📊</div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium mb-1">Matchs Terminés</p>
                            <p class="text-4xl font-black">{{ $finishedPredictions }}</p>
                        </div>
                        <div class="text-5xl opacity-20">✅</div>
                    </div>
                </div>
            </div>

            <!-- Onglets -->
            <div class="bg-white rounded-t-xl shadow-lg border-b-2 border-gray-200">
                <div class="flex gap-2 p-2">
                    <a href="{{ route('admin.predictions', array_merge(request()->except('status'), ['status' => 'all'])) }}"
                       class="flex-1 text-center py-3 px-4 rounded-lg font-bold transition-all {{ $status === 'all' ? 'bg-soboa-blue text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Tous ({{ $totalPredictions }})
                    </a>
                    <a href="{{ route('admin.predictions', array_merge(request()->except('status'), ['status' => 'upcoming'])) }}"
                       class="flex-1 text-center py-3 px-4 rounded-lg font-bold transition-all {{ $status === 'upcoming' ? 'bg-soboa-orange text-black shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        À venir ({{ $upcomingPredictions }})
                    </a>
                    <a href="{{ route('admin.predictions', array_merge(request()->except('status'), ['status' => 'finished'])) }}"
                       class="flex-1 text-center py-3 px-4 rounded-lg font-bold transition-all {{ $status === 'finished' ? 'bg-green-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Terminés ({{ $finishedPredictions }})
                    </a>
                </div>
            </div>

            <!-- Filtres -->
            <div class="bg-white shadow-lg p-4 border-b border-gray-200">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-gray-700 font-bold mb-2 text-sm">🏆 Filtrer par match</label>
                        <select name="match_id" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-soboa-orange">
                            <option value="">Tous les matchs</option>
                            @foreach($matches as $match)
                            <option value="{{ $match->id }}" {{ request('match_id') == $match->id ? 'selected' : '' }}>
                                {{ $match->team_a }} vs {{ $match->team_b }} ({{ $match->match_date->format('d/m') }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-gray-700 font-bold mb-2 text-sm">👤 Filtrer par utilisateur</label>
                        <select name="user_id" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-soboa-orange">
                            <option value="">Tous les utilisateurs</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->phone }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bg-soboa-blue hover:bg-soboa-blue/90 text-white font-bold py-2 px-6 rounded-lg transition-colors">
                        Filtrer
                    </button>
                    @if(request('match_id') || request('user_id'))
                    <a href="{{ route('admin.predictions', ['status' => $status]) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded-lg transition-colors">
                        Réinitialiser
                    </a>
                    @endif
                </form>
            </div>

            <!-- Bulk Delete Section -->
            <form id="bulkDeleteForm" action="{{ route('admin.bulk-delete-predictions') }}" method="POST" class="mb-6">
                @csrf
                <div id="bulkActionsBar" class="hidden bg-yellow-50 border-2 border-yellow-300 rounded-lg p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-gray-700">
                            <span id="selectedCount">0</span> pronostic(s) sélectionné(s)
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="document.getElementById('bulkDeleteForm').reset(); updateBulkActionsBar(); location.reload()"
                                class="bg-gray-500 hover:bg-gray-600 text-white font-bold px-4 py-2 rounded-lg transition-colors">
                            Annuler
                        </button>
                        <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer les pronostics sélectionnés ?')"
                                class="bg-red-500 hover:bg-red-600 text-white font-bold px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                            🗑️ Supprimer les pronostics sélectionnés
                        </button>
                    </div>
                </div>

            <!-- Liste -->
            <div class="bg-white rounded-b-xl shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-center p-4 font-bold text-gray-700 w-10">
                                <input type="checkbox" id="selectAllPredictions" class="cursor-pointer" onchange="toggleAllPredictions()">
                            </th>
                            <th class="text-left p-4 font-bold text-gray-700">Utilisateur</th>
                            <th class="text-left p-4 font-bold text-gray-700">Match</th>
                            <th class="text-center p-4 font-bold text-gray-700">Pronostic</th>
                            <th class="text-center p-4 font-bold text-gray-700">Score Réel</th>
                            <th class="text-center p-4 font-bold text-gray-700">
                                <span class="flex items-center justify-center gap-1">
                                    ⭐ Points
                                </span>
                            </th>
                            <th class="text-center p-4 font-bold text-gray-700">Date</th>
                            <th class="text-center p-4 font-bold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($predictions as $prediction)
                        <tr class="border-t hover:bg-gray-50 transition-colors">
                            <td class="p-4 text-center">
                                <input type="checkbox" name="prediction_ids[]" value="{{ $prediction->id }}" class="predictionCheckbox cursor-pointer" onchange="updateBulkActionsBar()">
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-soboa-blue to-blue-600 rounded-full flex items-center justify-center font-bold text-white text-sm shadow-md">
                                        {{ mb_substr($prediction->user->name ?? '?', 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800 text-sm">{{ $prediction->user->name ?? 'Inconnu' }}</p>
                                        <p class="text-xs text-gray-500">{{ $prediction->user->phone ?? '' }}</p>
                                        <p class="text-xs text-soboa-orange font-bold">{{ $prediction->user->points_total ?? 0 }} pts total</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        @if($prediction->match->homeTeam)
                                        <img src="https://flagcdn.com/w20/{{ $prediction->match->homeTeam->iso_code }}.png" class="w-5 h-4 rounded shadow">
                                        @endif
                                        <span class="text-sm font-medium">{{ $prediction->match->team_a }}</span>
                                        <span class="text-gray-400 text-xs">vs</span>
                                        <span class="text-sm font-medium">{{ $prediction->match->team_b }}</span>
                                        @if($prediction->match->awayTeam)
                                        <img src="https://flagcdn.com/w20/{{ $prediction->match->awayTeam->iso_code }}.png" class="w-5 h-4 rounded shadow">
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($prediction->match->status === 'finished')
                                        <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-bold rounded-full">Terminé</span>
                                        @elseif($prediction->match->status === 'live')
                                        <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded-full animate-pulse">Live</span>
                                        @else
                                        <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">À venir</span>
                                        @endif
                                        <span class="text-xs text-gray-500">{{ $prediction->match->match_date->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <span class="font-bold text-xl text-soboa-blue">
                                    {{ $prediction->score_a }} - {{ $prediction->score_b }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                @if($prediction->match->status === 'finished')
                                <span class="font-bold text-xl text-green-600">
                                    {{ $prediction->match->score_a }} - {{ $prediction->match->score_b }}
                                </span>
                                @else
                                <span class="text-gray-400 text-lg">-</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if($prediction->match->status === 'finished')
                                    @if($prediction->points_earned > 0)
                                    <div class="inline-flex items-center justify-center">
                                        <div class="bg-gradient-to-r from-soboa-orange to-orange-600 text-white font-black text-2xl px-4 py-2 rounded-lg shadow-lg transform hover:scale-110 transition-transform">
                                            +{{ $prediction->points_earned }}
                                        </div>
                                    </div>
                                    @else
                                    <span class="text-gray-400 text-xl font-bold">0</span>
                                    @endif
                                @else
                                <span class="text-gray-300 text-sm">En attente</span>
                                @endif
                            </td>
                            <td class="p-4 text-center text-gray-500 text-sm">
                                {{ $prediction->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="p-4 text-center">
                                <form action="{{ route('admin.delete-prediction', $prediction->id) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce pronostic ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold px-3 py-1.5 rounded-lg text-sm transition-colors">
                                        🗑️
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="p-12 text-center">
                                <div class="text-gray-400">
                                    <div class="text-6xl mb-4">🎯</div>
                                    <p class="text-xl font-bold text-gray-600">Aucun pronostic trouvé</p>
                                    <p class="text-sm text-gray-500 mt-2">
                                        @if($status === 'upcoming')
                                        Aucun pronostic pour les matchs à venir
                                        @elseif($status === 'finished')
                                        Aucun pronostic pour les matchs terminés
                                        @else
                                        Aucun pronostic enregistré
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </form>

            <script>
                function toggleAllPredictions() {
                    const selectAllCheckbox = document.getElementById('selectAllPredictions');
                    const predictionCheckboxes = document.querySelectorAll('.predictionCheckbox');
                    predictionCheckboxes.forEach(checkbox => {
                        checkbox.checked = selectAllCheckbox.checked;
                    });
                    updateBulkActionsBar();
                }

                function updateBulkActionsBar() {
                    const checkboxes = document.querySelectorAll('.predictionCheckbox:checked');
                    const bulkActionsBar = document.getElementById('bulkActionsBar');
                    const selectedCount = document.getElementById('selectedCount');

                    selectedCount.textContent = checkboxes.length;

                    if (checkboxes.length > 0) {
                        bulkActionsBar.classList.remove('hidden');
                    } else {
                        bulkActionsBar.classList.add('hidden');
                    }

                    // Update select all checkbox state
                    const allCheckboxes = document.querySelectorAll('.predictionCheckbox');
                    const selectAllCheckbox = document.getElementById('selectAllPredictions');
                    selectAllCheckbox.checked = checkboxes.length === allCheckboxes.length && allCheckboxes.length > 0;
                }
            </script>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $predictions->withQueryString()->links() }}
            </div>

        </div>
    </div>
</x-layouts.app>
