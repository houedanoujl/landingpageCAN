<x-layouts.app title="Admin - Matchs">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="text-soboa-orange hover:underline text-sm font-bold mb-2 inline-block">← Retour au dashboard</a>
                    <h1 class="text-3xl font-black text-soboa-blue">Gestion des Matchs</h1>
                </div>
                <a href="{{ route('admin.create-match') }}" class="bg-soboa-blue hover:bg-soboa-blue/90 text-white font-bold py-2 px-4 rounded-lg transition flex items-center gap-2">
                    <span>+</span> Nouveau Match
                </a>
            </div>

            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6" role="alert">
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            @endif

            <!-- Search Bar -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <form method="GET" action="{{ route('admin.matches') }}" class="flex gap-4 items-end">
                    <div class="flex-1">
                        <label for="search" class="block text-sm font-bold text-gray-700 mb-2">
                            🔍 Rechercher un match
                        </label>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Nom d'équipe, groupe, date..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-orange focus:border-transparent"
                        >
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-bold text-gray-700 mb-2">
                            Statut
                        </label>
                        <select
                            id="status"
                            name="status"
                            class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-orange focus:border-transparent"
                        >
                            <option value="">Tous</option>
                            <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>À venir</option>
                            <option value="live" {{ request('status') === 'live' ? 'selected' : '' }}>En cours</option>
                            <option value="finished" {{ request('status') === 'finished' ? 'selected' : '' }}>Terminés</option>
                        </select>
                    </div>
                    <button
                        type="submit"
                        class="bg-soboa-orange hover:bg-soboa-orange/90 text-black font-bold px-6 py-3 rounded-lg transition-colors"
                    >
                        Rechercher
                    </button>
                    @if(request('search') || request('status'))
                    <a
                        href="{{ route('admin.matches') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-bold px-6 py-3 rounded-lg transition-colors"
                    >
                        Réinitialiser
                    </a>
                    @endif
                </form>
                @if(request('search') || request('status'))
                <div class="mt-4 text-sm text-gray-600">
                    <strong>{{ $matches->count() }}</strong> résultat(s) trouvé(s)
                    @if(request('search'))
                    pour "<strong>{{ request('search') }}</strong>"
                    @endif
                    @if(request('status'))
                    ({{ request('status') === 'scheduled' ? 'À venir' : (request('status') === 'live' ? 'En cours' : 'Terminés') }})
                    @endif
                </div>
                @endif
            </div>

            <!-- Bulk Delete Section -->
            <form id="bulkDeleteForm" action="{{ route('admin.bulk-delete-matches') }}" method="POST" class="mb-6">
                @csrf
                <div id="bulkActionsBar" class="hidden bg-yellow-50 border-2 border-yellow-300 rounded-lg p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-gray-700">
                            <span id="selectedCount">0</span> match(es) sélectionné(s)
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="document.getElementById('bulkDeleteForm').reset(); updateBulkActionsBar(); location.reload()"
                                class="bg-gray-500 hover:bg-gray-600 text-white font-bold px-4 py-2 rounded-lg transition-colors">
                            Annuler
                        </button>
                        <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer les matchs sélectionnés et leurs pronostics associés ?')"
                                class="bg-red-500 hover:bg-red-600 text-white font-bold px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                            🗑️ Supprimer les matchs sélectionnés
                        </button>
                    </div>
                </div>

                <!-- Matches Table -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-soboa-blue text-white">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-bold w-10">
                                    <input type="checkbox" id="selectAllMatches" class="cursor-pointer" onchange="toggleAllMatches()">
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-bold">Date</th>
                            <th class="px-4 py-3 text-left text-sm font-bold">Groupe</th>
                            <th class="px-4 py-3 text-left text-sm font-bold">Match</th>
                            <th class="px-4 py-3 text-center text-sm font-bold">Score</th>
                            <th class="px-4 py-3 text-center text-sm font-bold">Statut</th>
                            <th class="px-4 py-3 text-right text-sm font-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($matches as $match)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="match_ids[]" value="{{ $match->id }}" class="matchCheckbox cursor-pointer" onchange="updateBulkActionsBar()">
                            </td>
                            <td class="px-4 py-4">
                                <span class="font-medium">{{ $match->match_date->format('d/m/Y') }}</span>
                                <span class="text-gray-500 text-sm block">{{ $match->match_date->format('H:i') }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="bg-soboa-blue/10 text-soboa-blue font-bold px-2 py-1 rounded text-sm">{{ $match->group_name }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    @if($match->homeTeam)
                                    <img src="https://flagcdn.com/w40/{{ $match->homeTeam->iso_code }}.png" class="w-8 h-6 rounded shadow">
                                    @endif
                                    <span class="font-bold">{{ $match->team_a }}</span>
                                    <span class="text-gray-400">vs</span>
                                    <span class="font-bold">{{ $match->team_b }}</span>
                                    @if($match->awayTeam)
                                    <img src="https://flagcdn.com/w40/{{ $match->awayTeam->iso_code }}.png" class="w-8 h-6 rounded shadow">
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($match->status === 'finished')
                                <span class="font-black text-xl">{{ $match->score_a }} - {{ $match->score_b }}</span>
                                @else
                                <span class="text-gray-400">--</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($match->status === 'finished')
                                <span class="bg-green-100 text-green-700 font-bold px-3 py-1 rounded-full text-sm">Terminé</span>
                                @elseif($match->status === 'live')
                                <span class="bg-red-100 text-red-700 font-bold px-3 py-1 rounded-full text-sm animate-pulse">En cours</span>
                                @else
                                <span class="bg-yellow-100 text-yellow-700 font-bold px-3 py-1 rounded-full text-sm">À venir</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.match-predictions', $match->id) }}"
                                       class="bg-purple-600 hover:bg-purple-700 text-white font-bold px-3 py-1.5 rounded text-sm transition-colors">
                                        📊 Pronostics
                                    </a>
                                    <a href="{{ route('admin.edit-match', $match->id) }}"
                                       class="bg-soboa-orange hover:bg-soboa-orange/90 text-black font-bold px-3 py-1.5 rounded text-sm transition-colors">
                                        ✏️ Modifier
                                    </a>
                                    @if($match->status === 'finished')
                                    <form action="{{ route('admin.calculate-points', $match->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="bg-soboa-blue hover:bg-soboa-blue/90 text-white font-bold px-3 py-1.5 rounded text-sm transition-colors">
                                            🔄 Recalculer
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('admin.delete-match', $match->id) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce match et tous ses pronostics ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="bg-red-500 hover:bg-red-600 text-white font-bold px-3 py-1.5 rounded text-sm transition-colors">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                    </div>
                </form>

            <script>
                function toggleAllMatches() {
                    const selectAllCheckbox = document.getElementById('selectAllMatches');
                    const matchCheckboxes = document.querySelectorAll('.matchCheckbox');
                    matchCheckboxes.forEach(checkbox => {
                        checkbox.checked = selectAllCheckbox.checked;
                    });
                    updateBulkActionsBar();
                }

                function updateBulkActionsBar() {
                    const checkboxes = document.querySelectorAll('.matchCheckbox:checked');
                    const bulkActionsBar = document.getElementById('bulkActionsBar');
                    const selectedCount = document.getElementById('selectedCount');

                    selectedCount.textContent = checkboxes.length;

                    if (checkboxes.length > 0) {
                        bulkActionsBar.classList.remove('hidden');
                    } else {
                        bulkActionsBar.classList.add('hidden');
                    }

                    // Update select all checkbox state
                    const allCheckboxes = document.querySelectorAll('.matchCheckbox');
                    const selectAllCheckbox = document.getElementById('selectAllMatches');
                    selectAllCheckbox.checked = checkboxes.length === allCheckboxes.length && allCheckboxes.length > 0;
                }
            </script>

        </div>
    </div>
</x-layouts.app>
