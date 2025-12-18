<x-layouts.app title="Admin - Points de Vente">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                        <span class="text-4xl">📍</span> Points de Vente
                    </h1>
                    <p class="text-gray-600 mt-2">Gérez les points de vente partenaires</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg transition">
                        ← Retour
                    </a>
                    <button onclick="document.getElementById('import-modal').classList.remove('hidden')"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition flex items-center gap-2">
                        <span>📥</span> Importer CSV
                    </button>
                    <a href="{{ route('admin.create-bar') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition flex items-center gap-2">
                        <span>+</span> Nouveau Point de Vente
                    </a>
                </div>
            </div>

            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
            @endif

            <!-- Recherche et Filtres -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <form method="GET" action="{{ route('admin.bars') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Recherche générale -->
                        <div class="md:col-span-2">
                            <label for="search" class="block text-sm font-bold text-gray-700 mb-2">
                                🔍 Rechercher
                            </label>
                            <input
                                type="text"
                                id="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Nom, zone, adresse..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-orange focus:border-transparent"
                            >
                        </div>

                        <!-- Filtre par zone -->
                        <div>
                            <label for="zone" class="block text-sm font-bold text-gray-700 mb-2">
                                📍 Zone
                            </label>
                            <select
                                id="zone"
                                name="zone"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-orange focus:border-transparent"
                            >
                                <option value="">Toutes les zones</option>
                                @foreach($zones as $zone)
                                <option value="{{ $zone }}" {{ request('zone') === $zone ? 'selected' : '' }}>{{ $zone }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtre par statut -->
                        <div>
                            <label for="status" class="block text-sm font-bold text-gray-700 mb-2">
                                Statut
                            </label>
                            <select
                                id="status"
                                name="status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-orange focus:border-transparent"
                            >
                                <option value="">Tous</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actifs</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactifs</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Filtre par matches assignés -->
                        <div>
                            <label for="has_matches" class="block text-sm font-bold text-gray-700 mb-2">
                                ⚽ Matches
                            </label>
                            <select
                                id="has_matches"
                                name="has_matches"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-orange focus:border-transparent"
                            >
                                <option value="">Tous</option>
                                <option value="yes" {{ request('has_matches') === 'yes' ? 'selected' : '' }}>Avec matches</option>
                                <option value="no" {{ request('has_matches') === 'no' ? 'selected' : '' }}>Sans matches</option>
                            </select>
                        </div>

                        <!-- Boutons -->
                        <div class="md:col-span-3 flex items-end gap-3">
                            <button
                                type="submit"
                                class="bg-soboa-orange hover:bg-soboa-orange/90 text-black font-bold px-6 py-2 rounded-lg transition-colors"
                            >
                                Rechercher
                            </button>
                            @if(request()->hasAny(['search', 'zone', 'status', 'has_matches']))
                            <a
                                href="{{ route('admin.bars') }}"
                                class="bg-gray-500 hover:bg-gray-600 text-white font-bold px-6 py-2 rounded-lg transition-colors"
                            >
                                Réinitialiser
                            </a>
                            @endif
                        </div>
                    </div>

                    @if(request()->hasAny(['search', 'zone', 'status', 'has_matches']))
                    <div class="pt-2 text-sm text-gray-600">
                        <strong>{{ $bars->total() }}</strong> résultat(s) trouvé(s)
                        @if(request('search'))
                        pour "<strong>{{ request('search') }}</strong>"
                        @endif
                    </div>
                    @endif
                </form>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl p-4 shadow">
                    <p class="text-2xl font-black text-soboa-blue">{{ $bars->total() }}</p>
                    <p class="text-gray-500 text-sm">Total</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow">
                    <p class="text-2xl font-black text-green-600">{{ $bars->where('is_active', true)->count() }}</p>
                    <p class="text-gray-500 text-sm">Actifs</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow">
                    <p class="text-2xl font-black text-red-600">{{ $bars->where('is_active', false)->count() }}</p>
                    <p class="text-gray-500 text-sm">Inactifs</p>
                </div>
            </div>

            <!-- Liste -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-4 font-bold text-gray-700">Nom</th>
                            <th class="text-left p-4 font-bold text-gray-700">Zone</th>
                            <th class="text-left p-4 font-bold text-gray-700">Matchs Assignés</th>
                            <th class="text-center p-4 font-bold text-gray-700">Statut</th>
                            <th class="text-center p-4 font-bold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bars as $bar)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-soboa-blue/10 rounded-full flex items-center justify-center">
                                        <span class="text-xl">📍</span>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-800">{{ $bar->name }}</div>
                                        <div class="text-xs text-gray-500 font-mono">
                                            {{ number_format($bar->latitude, 4) }}, {{ number_format($bar->longitude, 4) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                @if($bar->zone)
                                    <span class="text-gray-700 font-medium">{{ $bar->zone }}</span>
                                @else
                                    <span class="text-gray-400 italic text-sm">Non définie</span>
                                @endif
                            </td>
                            <td class="p-4">
                                @if($bar->animations->count() > 0)
                                    <div class="space-y-1">
                                        @foreach($bar->animations->take(2) as $animation)
                                            <div class="flex items-center gap-2 text-sm">
                                                <span class="text-xs">⚽</span>
                                                <span class="font-medium text-gray-700">
                                                    @if($animation->match->homeTeam && $animation->match->awayTeam)
                                                        {{ $animation->match->homeTeam->name }} vs {{ $animation->match->awayTeam->name }}
                                                    @else
                                                        {{ $animation->match->team_a }} vs {{ $animation->match->team_b }}
                                                    @endif
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    ({{ \Carbon\Carbon::parse($animation->animation_date)->format('d/m') }})
                                                </span>
                                            </div>
                                        @endforeach
                                        @if($bar->animations->count() > 2)
                                            <div class="text-xs text-soboa-blue font-bold">
                                                +{{ $bar->animations->count() - 2 }} autre(s)
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400 italic text-sm">Aucun match assigné</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if($bar->is_active)
                                <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">Actif</span>
                                @else
                                <span class="bg-red-100 text-red-700 text-xs font-bold px-3 py-1 rounded-full">Inactif</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-center gap-2 flex-wrap">
                                    <a href="{{ route('admin.bar-animations', $bar->id) }}"
                                       class="text-soboa-blue hover:underline text-sm font-bold whitespace-nowrap">
                                        📅 Animations
                                    </a>
                                    <span class="text-gray-300">|</span>
                                    <form action="{{ route('admin.toggle-bar', $bar->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-{{ $bar->is_active ? 'red' : 'green' }}-600 hover:underline text-sm font-bold whitespace-nowrap">
                                            {{ $bar->is_active ? 'Désactiver' : 'Activer' }}
                                        </button>
                                    </form>
                                    <span class="text-gray-300">|</span>
                                    <a href="{{ route('admin.edit-bar', $bar->id) }}" class="text-soboa-orange hover:underline text-sm font-bold">
                                        Modifier
                                    </a>
                                    <span class="text-gray-300">|</span>
                                    <form action="{{ route('admin.delete-bar', $bar->id) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce point de vente ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-sm font-bold">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-500">
                                Aucun point de vente trouvé.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $bars->links() }}
            </div>

        </div>
    </div>

    <!-- Modal Import CSV -->
    <div id="import-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick="if(event.target === this) this.classList.add('hidden')">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <!-- Header -->
            <div class="bg-blue-600 text-white p-6 rounded-t-xl">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold flex items-center gap-2">
                        <span>📥</span> Importer des points de vente (CSV)
                    </h2>
                    <button onclick="document.getElementById('import-modal').classList.add('hidden')"
                            class="text-white hover:text-gray-200 text-2xl">
                        ×
                    </button>
                </div>
            </div>

            <!-- Body -->
            <div class="p-6">
                <!-- Instructions -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
                    <h3 class="font-bold text-blue-900 mb-2">📋 Format du fichier CSV</h3>
                    <p class="text-sm text-blue-800 mb-3">Votre fichier CSV doit contenir les colonnes suivantes dans cet ordre :</p>
                    <ul class="text-sm text-blue-800 space-y-1 ml-4">
                        <li>• <strong>nom</strong> - Nom du point de vente</li>
                        <li>• <strong>adresse</strong> - Adresse complète</li>
                        <li>• <strong>latitude</strong> - Coordonnée latitude (ex: 14.692778)</li>
                        <li>• <strong>longitude</strong> - Coordonnée longitude (ex: -17.447938)</li>
                    </ul>
                </div>

                <!-- Exemple -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200">
                    <h4 class="font-bold text-gray-700 mb-2 text-sm">Exemple de fichier CSV :</h4>
                    <pre class="text-xs bg-white p-3 rounded border border-gray-300 overflow-x-auto font-mono">nom,adresse,latitude,longitude
Bar Le Sphinx,Rue 10 x Avenue Hassan II Dakar,14.692778,-17.447938
Chez Fatou,Place de l'Indépendance Dakar,14.693350,-17.448830
Le Djoloff,Corniche Ouest Dakar,14.716677,-17.481383</pre>
                </div>

                <!-- Télécharger modèle -->
                <div class="mb-6">
                    <a href="{{ route('admin.download-bars-template') }}"
                       class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg transition border border-gray-300">
                        <span>📄</span> Télécharger le modèle CSV
                    </a>
                </div>

                <!-- Formulaire d'upload -->
                <form action="{{ route('admin.import-bars') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Fichier CSV *</label>
                        <input type="file"
                               name="csv_file"
                               accept=".csv"
                               required
                               class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-gray-500 text-sm mt-1">Format accepté : CSV (UTF-8)</p>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                        <p class="text-sm text-yellow-800">
                            <strong>⚠️ Important :</strong> Tous les points de vente importés seront activés par défaut.
                        </p>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <button type="button"
                                onclick="document.getElementById('import-modal').classList.add('hidden')"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-lg transition">
                            Annuler
                        </button>
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                            📥 Importer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
