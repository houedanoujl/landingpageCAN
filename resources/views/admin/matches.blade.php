<x-layouts.app title="Admin - Matchs">
    <div class="bg-gray-100 min-h-screen py-8" x-data="importMatchesApp()">
        <div class="max-w-7xl mx-auto px-4">
            
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="text-soboa-orange hover:underline text-sm font-bold mb-2 inline-block">← Retour au dashboard</a>
                    <h1 class="text-3xl font-black text-soboa-blue">Gestion des Matchs</h1>
                </div>
                <div class="flex gap-3">
                    <button @click="showImportModal = true" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Importer (JSON)
                    </button>
                    <a href="{{ route('admin.create-match') }}" class="bg-soboa-blue hover:bg-soboa-blue/90 text-white font-bold py-2 px-4 rounded-lg transition flex items-center gap-2">
                        <span>+</span> Nouveau Match
                    </a>
                </div>
            </div>

            <!-- Modal Import JSON -->
            <div x-show="showImportModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
                 @click.self="showImportModal = false"
                 x-cloak>
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden" @click.stop>
                    <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-white">Importer des matchs (JSON)</h3>
                            <p class="text-green-100 text-sm">Collez le JSON des matchs terminés</p>
                        </div>
                        <button @click="showImportModal = false" class="text-white/70 hover:text-white p-2 rounded-full hover:bg-white/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Format attendu :</label>
                            <pre class="bg-gray-100 p-3 rounded-lg text-xs overflow-x-auto text-gray-600">{
  "matchs_termines": [
    {"date": "2026-06-11", "groupe": "A", "equipe_1": "Mexico", "score_1": 2, "equipe_2": "South Africa", "score_2": 0},
    ...
  ]
}</pre>
                        </div>
                        <div class="mb-4">
                            <label for="jsonData" class="block text-sm font-bold text-gray-700 mb-2">Données JSON :</label>
                            <textarea 
                                id="jsonData"
                                x-model="jsonData"
                                rows="12"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent font-mono text-sm"
                                placeholder='{"matchs_termines": [...]}'></textarea>
                        </div>
                        <div x-show="importMessage" class="mb-4 p-3 rounded-lg" :class="importSuccess ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                            <span x-text="importMessage"></span>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button @click="showImportModal = false" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-lg">
                                Annuler
                            </button>
                            <button @click="importMatches()" :disabled="importing" class="px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white font-bold rounded-lg flex items-center gap-2">
                                <svg x-show="importing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="importing ? 'Import en cours...' : 'Importer'"></span>
                            </button>
                        </div>
                    </div>
                </div>
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
                            Rechercher un match
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
                            Supprimer les matchs sélectionnés
                        </button>
                    </div>
                </div>

                @php
                    // Grouper les matches par phase
                    $matchesByPhase = $matches->groupBy('phase');

                    // Ordre des phases
                    $phaseOrder = ['group_stage', 'round_of_32', 'round_of_16', 'quarter_final', 'semi_final', 'third_place', 'final'];

                    // Noms des phases
                    $phaseNames = [
                        'group_stage' => 'Phase de Poules',
                        'round_of_16' => 'Huitièmes de finale',
                        'quarter_final' => 'Quarts de finale',
                        'semi_final' => 'Demi-finales',
                        'third_place' => 'Match pour la 3ème place',
                        'final' => 'FINALE',
                    ];
                @endphp

                @foreach($phaseOrder as $phase)
                    @if(isset($matchesByPhase[$phase]))
                        <!-- Phase Section -->
                        <div class="mb-8">
                            <div class="bg-gradient-to-r from-soboa-blue to-blue-600 rounded-t-xl px-6 py-4">
                                <h2 class="text-xl font-black text-white">{{ $phaseNames[$phase] }}</h2>
                            </div>

                            @if($phase === 'group_stage')
                                @php
                                    // Grouper par groupe pour la phase de poules
                                    $matchesByGroup = $matchesByPhase[$phase]->groupBy('group_name')->sortKeys();
                                @endphp

                                @foreach($matchesByGroup as $groupName => $groupMatches)
                                    <div class="bg-white border-x border-b border-gray-200 @if($loop->last) rounded-b-xl @endif">
                                        <div class="bg-soboa-orange/10 px-6 py-3 border-b border-gray-200">
                                            <h3 class="text-lg font-bold text-soboa-blue">Groupe {{ $groupName }}</h3>
                                        </div>
                                        <div class="overflow-hidden">
                                            <table class="w-full">
                                                <thead class="bg-gray-100 border-b border-gray-200">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left text-xs font-bold w-10">
                                                            <input type="checkbox" class="cursor-pointer selectGroupCheckbox" data-group="{{ $groupName }}" onchange="toggleGroupMatches(this, '{{ $groupName }}')">
                                                        </th>
                                                        <th class="px-4 py-2 text-left text-xs font-bold">Date</th>
                                                        <th class="px-4 py-2 text-left text-xs font-bold">Match</th>
                                                        <th class="px-4 py-2 text-center text-xs font-bold">Score</th>
                                                        <th class="px-4 py-2 text-center text-xs font-bold">Statut</th>
                                                        <th class="px-4 py-2 text-center text-xs font-bold">PDV</th>
                                                        <th class="px-4 py-2 text-right text-xs font-bold">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                    @foreach($groupMatches as $match)
                                                    <tr class="hover:bg-gray-50 group-{{ $groupName }}">
                                                        <td class="px-4 py-3 text-center">
                                                            <input type="checkbox" name="match_ids[]" value="{{ $match->id }}" class="matchCheckbox group-{{ $groupName }}-checkbox cursor-pointer" onchange="updateBulkActionsBar()">
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <span class="font-medium text-sm">{{ $match->match_date->format('d/m/Y') }}</span>
                                                            <span class="text-gray-500 text-xs block">{{ $match->match_date->format('H:i') }}</span>
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <div class="flex items-center gap-1" id="match-teams-{{ $match->id }}">
                                                                <!-- Équipe domicile -->
                                                                <div class="flex items-center gap-1">
                                                                    <img id="flag-home-{{ $match->id }}" src="{{ $match->homeTeam?->flag_url ?? '' }}" alt="" loading="lazy" class="w-5 h-3 rounded shadow" onerror="this.style.display='none'">
                                                                    <select 
                                                                        class="team-select text-xs font-bold border border-gray-200 rounded px-1 py-0.5 bg-white hover:border-soboa-orange focus:border-soboa-orange focus:ring-1 focus:ring-soboa-orange cursor-pointer"
                                                                        onchange="updateMatchTeam({{ $match->id }}, 'home', this.value)"
                                                                        style="max-width: 100px;"
                                                                    >
                                                                        <option value="">--</option>
                                                                        @foreach($teams as $team)
                                                                        <option value="{{ $team->id }}" data-iso="{{ $team->iso_code }}" {{ $match->home_team_id == $team->id ? 'selected' : '' }}>
                                                                            {{ $team->display_name }}
                                                                        </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <span class="text-gray-400 text-xs mx-1">vs</span>
                                                                <!-- Équipe extérieur -->
                                                                <div class="flex items-center gap-1">
                                                                    <select 
                                                                        class="team-select text-xs font-bold border border-gray-200 rounded px-1 py-0.5 bg-white hover:border-soboa-orange focus:border-soboa-orange focus:ring-1 focus:ring-soboa-orange cursor-pointer"
                                                                        onchange="updateMatchTeam({{ $match->id }}, 'away', this.value)"
                                                                        style="max-width: 100px;"
                                                                    >
                                                                        <option value="">--</option>
                                                                        @foreach($teams as $team)
                                                                        <option value="{{ $team->id }}" data-iso="{{ $team->iso_code }}" {{ $match->away_team_id == $team->id ? 'selected' : '' }}>
                                                                            {{ $team->display_name }}
                                                                        </option>
                                                                        @endforeach
                                                                    </select>
                                                                    <img id="flag-away-{{ $match->id }}" src="{{ $match->awayTeam?->flag_url ?? '' }}" alt="" loading="lazy" class="w-5 h-3 rounded shadow" onerror="this.style.display='none'">
                                                                </div>
                                                                <span id="save-indicator-{{ $match->id }}" class="hidden text-green-500 text-xs ml-1"></span>
                                                            </div>
                                                        </td>
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            @if($match->status === 'finished')
                                                            <span class="font-black text-lg">{{ $match->score_a }} - {{ $match->score_b }}</span>
                                                            @else
                                                            <span class="text-gray-400">--</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            @if($match->status === 'finished')
                                                            <span class="bg-green-100 text-green-700 font-bold px-2 py-1 rounded-full text-xs">Terminé</span>
                                                            @elseif($match->status === 'live')
                                                            <span class="bg-red-100 text-red-700 font-bold px-2 py-1 rounded-full text-xs animate-pulse">En cours</span>
                                                            @else
                                                            <span class="bg-yellow-100 text-yellow-700 font-bold px-2 py-1 rounded-full text-xs">À venir</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            <button type="button" onclick="openVenueModal({{ $match->id }})" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-2 py-1 rounded text-xs transition-colors">
                                                                <span class="venue-count-{{ $match->id }}">{{ $match->animations->count() }}</span>
                                                            </button>
                                                        </td>
                                                        <td class="px-4 py-3 text-right">
                                                            <div class="flex items-center justify-end gap-1">
                                                                <a href="{{ route('admin.match-predictions', $match->id) }}"
                                                                   class="bg-purple-600 hover:bg-purple-700 text-white font-bold px-2 py-1 rounded text-xs transition-colors"
                                                                   title="Voir les pronostics"><svg class="w-4 h-4 inline-block align-middle" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                                    
                                                                </a>
                                                                <a href="{{ route('admin.edit-match', $match->id) }}"
                                                                   class="bg-soboa-orange hover:bg-soboa-orange/90 text-black font-bold px-2 py-1 rounded text-xs transition-colors"
                                                                   title="Modifier"><svg class="w-4 h-4 inline-block align-middle" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                                    
                                                                </a>
                                                                <button type="button" onclick="duplicateMatch({{ $match->id }})"
                                                                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold px-2 py-1 rounded text-xs transition-colors"
                                                                        title="Dupliquer"><svg class="w-4 h-4 inline-block align-middle" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                                    
                                                                </button>
                                                                @if($match->status === 'finished')
                                                                <button type="button" onclick="calculatePoints({{ $match->id }})"
                                                                        class="bg-soboa-blue hover:bg-soboa-blue/90 text-white font-bold px-2 py-1 rounded text-xs transition-colors"
                                                                        title="Recalculer les points"><svg class="w-4 h-4 inline-block align-middle" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                                    
                                                                </button>
                                                                @endif
                                                                <button type="button" onclick="deleteMatch({{ $match->id }})"
                                                                        class="bg-red-500 hover:bg-red-600 text-white font-bold px-2 py-1 rounded text-xs transition-colors"
                                                                        title="Supprimer"><svg class="w-4 h-4 inline-block align-middle" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                    
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <!-- Autres phases (sans groupes) -->
                                <div class="bg-white rounded-b-xl shadow-lg overflow-hidden">
                                    <table class="w-full">
                                        <thead class="bg-gray-100 border-b border-gray-200">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-sm font-bold w-10">
                                                    <input type="checkbox" class="cursor-pointer" onchange="togglePhaseMatches(this, '{{ $phase }}')">
                                                </th>
                                                <th class="px-4 py-3 text-left text-sm font-bold">Date</th>
                                                <th class="px-4 py-3 text-left text-sm font-bold">Match</th>
                                                <th class="px-4 py-3 text-center text-sm font-bold">Score</th>
                                                <th class="px-4 py-3 text-center text-sm font-bold">Statut</th>
                                                <th class="px-4 py-3 text-center text-sm font-bold">PDV</th>
                                                <th class="px-4 py-3 text-right text-sm font-bold">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @foreach($matchesByPhase[$phase] as $match)
                                            <tr class="hover:bg-gray-50 phase-{{ $phase }}">
                                                <td class="px-4 py-4 text-center">
                                                    <input type="checkbox" name="match_ids[]" value="{{ $match->id }}" class="matchCheckbox phase-{{ $phase }}-checkbox cursor-pointer" onchange="updateBulkActionsBar()">
                                                </td>
                                                <td class="px-4 py-4">
                                                    <span class="font-medium">{{ $match->match_date->format('d/m/Y') }}</span>
                                                    <span class="text-gray-500 text-sm block">{{ $match->match_date->format('H:i') }}</span>
                                                </td>
                                                <td class="px-4 py-4">
                                                    <div class="flex items-center gap-2" id="match-teams-knockout-{{ $match->id }}">
                                                        <!-- Équipe domicile -->
                                                        <div class="flex items-center gap-1">
                                                            <img id="flag-home-ko-{{ $match->id }}" src="{{ $match->homeTeam?->flag_url ?? '' }}" alt="" loading="lazy" class="w-6 h-4 rounded shadow" onerror="this.style.display='none'">
                                                            <select 
                                                                class="team-select text-sm font-bold border border-gray-200 rounded px-2 py-1 bg-white hover:border-soboa-orange focus:border-soboa-orange focus:ring-1 focus:ring-soboa-orange cursor-pointer"
                                                                onchange="updateMatchTeam({{ $match->id }}, 'home', this.value)"
                                                                style="max-width: 130px;"
                                                            >
                                                                <option value="">-- Équipe --</option>
                                                                @foreach($teams as $team)
                                                                <option value="{{ $team->id }}" data-iso="{{ $team->iso_code }}" {{ $match->home_team_id == $team->id ? 'selected' : '' }}>
                                                                    {{ $team->display_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <span class="text-gray-400 mx-1">vs</span>
                                                        <!-- Équipe extérieur -->
                                                        <div class="flex items-center gap-1">
                                                            <select 
                                                                class="team-select text-sm font-bold border border-gray-200 rounded px-2 py-1 bg-white hover:border-soboa-orange focus:border-soboa-orange focus:ring-1 focus:ring-soboa-orange cursor-pointer"
                                                                onchange="updateMatchTeam({{ $match->id }}, 'away', this.value)"
                                                                style="max-width: 130px;"
                                                            >
                                                                <option value="">-- Équipe --</option>
                                                                @foreach($teams as $team)
                                                                <option value="{{ $team->id }}" data-iso="{{ $team->iso_code }}" {{ $match->away_team_id == $team->id ? 'selected' : '' }}>
                                                                    {{ $team->display_name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            <img id="flag-away-ko-{{ $match->id }}" src="{{ $match->awayTeam?->flag_url ?? '' }}" alt="" loading="lazy" class="w-6 h-4 rounded shadow" onerror="this.style.display='none'">
                                                        </div>
                                                        <span id="save-indicator-ko-{{ $match->id }}" class="hidden text-green-500 text-sm ml-1"></span>
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
                                                <td class="px-4 py-4 text-center">
                                                    <button type="button" onclick="openVenueModal({{ $match->id }})" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-3 py-1.5 rounded text-sm transition-colors">
                                                        <span class="venue-count-{{ $match->id }}">{{ $match->animations->count() }}</span> PDV
                                                    </button>
                                                </td>
                                                <td class="px-4 py-4 text-right">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <a href="{{ route('admin.match-predictions', $match->id) }}"
                                                           class="bg-purple-600 hover:bg-purple-700 text-white font-bold px-3 py-1.5 rounded text-sm transition-colors"
                                                           title="Voir les pronostics"><svg class="w-4 h-4 inline-block align-middle" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                            Pronostics
                                                        </a>
                                                        <a href="{{ route('admin.edit-match', $match->id) }}"
                                                           class="bg-soboa-orange hover:bg-soboa-orange/90 text-black font-bold px-3 py-1.5 rounded text-sm transition-colors"
                                                           title="Modifier"><svg class="w-4 h-4 inline-block align-middle" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                            Modifier
                                                        </a>
                                                        <button type="button" onclick="duplicateMatch({{ $match->id }})"
                                                                class="bg-blue-500 hover:bg-blue-600 text-white font-bold px-3 py-1.5 rounded text-sm transition-colors"
                                                                title="Dupliquer ce match">
                                                            Dupliquer
                                                        </button>
                                                        @if($match->status === 'finished')
                                                        <button type="button" onclick="calculatePoints({{ $match->id }})"
                                                                class="bg-soboa-blue hover:bg-soboa-blue/90 text-white font-bold px-3 py-1.5 rounded text-sm transition-colors"
                                                                title="Recalculer les points">
                                                            Recalculer
                                                        </button>
                                                        @endif
                                                        <button type="button" onclick="deleteMatch({{ $match->id }})"
                                                                class="bg-red-500 hover:bg-red-600 text-white font-bold px-3 py-1.5 rounded text-sm transition-colors"
                                                                title="Supprimer"><svg class="w-4 h-4 inline-block align-middle" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Supprimer
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </form>

            <!-- Pagination -->
            @if($matches->hasPages())
                <div class="mt-6">
                    {{ $matches->links() }}
                </div>
            @endif

            <!-- Modal pour gérer les PDV -->
            <div id="venueModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[9999] flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4 flex items-center justify-between">
                        <h3 class="text-xl font-black text-white">Attribuer des Points de Vente</h3>
                        <button type="button" onclick="closeVenueModal()" class="text-white hover:text-gray-200 text-2xl font-bold">×</button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 overflow-y-auto max-h-[70vh]">
                        <div id="venueModalContent" class="space-y-4">
                            <p class="text-center text-gray-500">Chargement...</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t">
                        <button type="button" onclick="closeVenueModal()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold px-6 py-2 rounded-lg transition-colors">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>

            <script>
                // Build flag URL — mirrors App\Models\Team::flagUrl().
                // flagcdn.com only supports ISO 3166-1; subdivisions (gb-eng, gb-sct…) need flagicons.lipis.dev.
                function buildFlagUrl(iso) {
                    iso = (iso || '').toLowerCase();
                    if (!iso) return '';
                    if (iso.includes('-')) return `https://flagicons.lipis.dev/flags/4x3/${iso}.svg`;
                    return `https://flagcdn.com/w40/${iso}.png`;
                }

                function toggleGroupMatches(checkbox, groupName) {
                    const groupCheckboxes = document.querySelectorAll('.group-' + groupName + '-checkbox');
                    groupCheckboxes.forEach(cb => {
                        cb.checked = checkbox.checked;
                    });
                    updateBulkActionsBar();
                }

                function togglePhaseMatches(checkbox, phase) {
                    const phaseCheckboxes = document.querySelectorAll('.phase-' + phase + '-checkbox');
                    phaseCheckboxes.forEach(cb => {
                        cb.checked = checkbox.checked;
                    });
                    updateBulkActionsBar();
                }

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
                }

                // Venue Modal Functions
                let currentMatchId = null;

                function openVenueModal(matchId) {
                    currentMatchId = matchId;
                    document.getElementById('venueModal').classList.remove('hidden');
                    loadVenues(matchId);
                }

                function closeVenueModal() {
                    document.getElementById('venueModal').classList.add('hidden');
                    currentMatchId = null;
                }

                async function loadVenues(matchId) {
                    const content = document.getElementById('venueModalContent');
                    content.innerHTML = '<p class="text-center text-gray-500">Chargement...</p>';

                    try {
                        const response = await fetch(`/admin/matches/${matchId}/venues`);
                        const data = await response.json();

                        if (data.success) {
                            renderVenues(data.match, data.venues, data.assignedVenueIds);
                        } else {
                            content.innerHTML = '<p class="text-center text-red-500">Erreur lors du chargement</p>';
                        }
                    } catch (error) {
                        content.innerHTML = '<p class="text-center text-red-500">Erreur de connexion</p>';
                    }
                }

                function renderVenues(match, venues, assignedVenueIds) {
                    const content = document.getElementById('venueModalContent');

                    let html = `
                        <div class="mb-6 p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                            <h4 class="font-bold text-lg text-indigo-900 mb-1">Match: ${match.team_a} vs ${match.team_b}</h4>
                            <p class="text-sm text-gray-600">${match.match_date} • ${assignedVenueIds.length} PDV assigné(s)</p>
                        </div>

                        <div class="mb-4">
                            <input type="text" id="venueSearch" onkeyup="filterVenues()"
                                   placeholder="Rechercher un point de vente..."
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="venuesList">
                    `;

                    venues.forEach(venue => {
                        const isAssigned = assignedVenueIds.includes(venue.id);
                        html += `
                            <div class="venue-item p-3 border rounded-lg hover:bg-gray-50 transition ${isAssigned ? 'bg-green-50 border-green-300' : 'bg-white border-gray-200'}"
                                 data-venue-name="${venue.name.toLowerCase()}" data-venue-zone="${(venue.zone || '').toLowerCase()}">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="checkbox"
                                           ${isAssigned ? 'checked' : ''}
                                           onchange="toggleVenue(${venue.id}, this.checked)"
                                           class="mt-1 w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500">
                                    <div class="flex-1">
                                        <div class="font-bold text-sm">${venue.name}</div>
                                        <div class="text-xs text-gray-500">${venue.zone || 'Sans zone'}</div>
                                    </div>
                                </label>
                            </div>
                        `;
                    });

                    html += '</div>';
                    content.innerHTML = html;
                }

                function filterVenues() {
                    const search = document.getElementById('venueSearch').value.toLowerCase();
                    const venues = document.querySelectorAll('.venue-item');

                    venues.forEach(venue => {
                        const name = venue.dataset.venueName;
                        const zone = venue.dataset.venueZone;

                        if (name.includes(search) || zone.includes(search)) {
                            venue.style.display = 'block';
                        } else {
                            venue.style.display = 'none';
                        }
                    });
                }

                async function toggleVenue(venueId, isChecked) {
                    try {
                        const url = isChecked
                            ? `/admin/matches/${currentMatchId}/venues/${venueId}/assign`
                            : `/admin/matches/${currentMatchId}/venues/${venueId}/unassign`;

                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Update counter
                            document.querySelector(`.venue-count-${currentMatchId}`).textContent = data.venueCount;
                        } else {
                            alert(data.message || 'Erreur lors de la modification');
                            // Reload to reset checkbox state
                            loadVenues(currentMatchId);
                        }
                    } catch (error) {
                        alert('Erreur de connexion');
                        loadVenues(currentMatchId);
                    }
                }

                // Close modal on Escape key
                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape' && currentMatchId) {
                        closeVenueModal();
                    }
                });

                // Delete match function
                function deleteMatch(matchId) {
                    if (!confirm('Supprimer ce match et tous ses pronostics ?')) {
                        return;
                    }

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/matches/${matchId}`;

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';

                    form.appendChild(csrfInput);
                    form.appendChild(methodInput);
                    document.body.appendChild(form);
                    form.submit();
                }

                // Duplicate match function
                function duplicateMatch(matchId) {
                    if (!confirm('Dupliquer ce match avec ses animations ?')) {
                        return;
                    }

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/matches/${matchId}/duplicate`;

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;

                    form.appendChild(csrfInput);
                    document.body.appendChild(form);
                    form.submit();
                }

                // Calculate points function
                function calculatePoints(matchId) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/matches/${matchId}/calculate-points`;

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;

                    form.appendChild(csrfInput);
                    document.body.appendChild(form);
                    form.submit();
                }

                // Update match team (AJAX)
                async function updateMatchTeam(matchId, teamType, teamId) {
                    if (!teamId) return;

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    
                    // Préparer les données
                    const data = {};
                    if (teamType === 'home') {
                        data.home_team_id = teamId;
                    } else {
                        data.away_team_id = teamId;
                    }

                    try {
                        const response = await fetch(`/admin/matches/${matchId}/quick-update`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(data),
                        });

                        const result = await response.json();

                        if (result.success) {
                            // Mettre à jour le drapeau
                            const updated = result.updated;
                            if (teamType === 'home' && updated.home_team) {
                                const flagImg = document.getElementById(`flag-home-${matchId}`) || document.getElementById(`flag-home-ko-${matchId}`);
                                if (flagImg) {
                                    flagImg.src = buildFlagUrl(updated.home_team.iso_code);
                                    flagImg.style.display = '';
                                }
                            }
                            if (teamType === 'away' && updated.away_team) {
                                const flagImg = document.getElementById(`flag-away-${matchId}`) || document.getElementById(`flag-away-ko-${matchId}`);
                                if (flagImg) {
                                    flagImg.src = buildFlagUrl(updated.away_team.iso_code);
                                    flagImg.style.display = '';
                                }
                            }

                            // Afficher l'indicateur de sauvegarde
                            const indicator = document.getElementById(`save-indicator-${matchId}`) || document.getElementById(`save-indicator-ko-${matchId}`);
                            if (indicator) {
                                indicator.classList.remove('hidden');
                                setTimeout(() => {
                                    indicator.classList.add('hidden');
                                }, 2000);
                            }
                        } else {
                            alert('Erreur: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Erreur:', error);
                        alert('Erreur lors de la mise à jour');
                    }
                }

                // Alpine.js app for import modal
                function importMatchesApp() {
                    return {
                        showImportModal: false,
                        jsonData: '',
                        importing: false,
                        importMessage: '',
                        importSuccess: false,

                        async importMatches() {
                            if (!this.jsonData.trim()) {
                                this.importMessage = 'Veuillez coller les données JSON';
                                this.importSuccess = false;
                                return;
                            }

                            this.importing = true;
                            this.importMessage = '';

                            try {
                                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                                const response = await fetch('/admin/matches/import-json', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                        'Accept': 'application/json',
                                    },
                                    body: JSON.stringify({ json_data: this.jsonData }),
                                });

                                const result = await response.json();

                                if (result.success) {
                                    this.importSuccess = true;
                                    this.importMessage = result.message;
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 2000);
                                } else {
                                    this.importSuccess = false;
                                    this.importMessage = result.message || 'Erreur lors de l\'import';
                                }
                            } catch (error) {
                                console.error('Erreur:', error);
                                this.importSuccess = false;
                                this.importMessage = 'Erreur technique lors de l\'import';
                            } finally {
                                this.importing = false;
                            }
                        }
                    };
                }
            </script>

        </div>
    </div>
</x-layouts.app>
