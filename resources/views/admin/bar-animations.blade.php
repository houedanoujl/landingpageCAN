<x-layouts.app title="Admin - Animations de {{ $bar->name }}">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">

            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                        <span class="text-4xl">📅</span> Animations - {{ $bar->name }}
                    </h1>
                    <p class="text-gray-600 mt-2">
                        @if($bar->zone)
                            <span class="font-medium">Zone: {{ $bar->zone }}</span> •
                        @endif
                        {{ $bar->address }}
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.bars') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg transition">
                        ← Retour aux points de vente
                    </a>
                    <a href="{{ route('admin.create-animation') }}?bar_id={{ $bar->id }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition flex items-center gap-2">
                        <span>+</span> Nouvelle Animation
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

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl p-4 shadow">
                    <p class="text-2xl font-black text-soboa-blue">{{ $animations->count() }}</p>
                    <p class="text-gray-500 text-sm">Total Animations</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow">
                    <p class="text-2xl font-black text-green-600">{{ $animations->where('is_active', true)->count() }}</p>
                    <p class="text-gray-500 text-sm">Actives</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow">
                    <p class="text-2xl font-black text-red-600">{{ $animations->where('is_active', false)->count() }}</p>
                    <p class="text-gray-500 text-sm">Inactives</p>
                </div>
            </div>

            <!-- Liste des animations -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-4 font-bold text-gray-700">Match</th>
                            <th class="text-left p-4 font-bold text-gray-700">Date</th>
                            <th class="text-left p-4 font-bold text-gray-700">Heure</th>
                            <th class="text-center p-4 font-bold text-gray-700">Statut</th>
                            <th class="text-center p-4 font-bold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($animations as $animation)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-soboa-blue/10 rounded-full flex items-center justify-center">
                                        <span class="text-xl">⚽</span>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-800">
                                            @if($animation->match->homeTeam && $animation->match->awayTeam)
                                                {{ $animation->match->homeTeam->name }} vs {{ $animation->match->awayTeam->name }}
                                            @else
                                                {{ $animation->match->team_a }} vs {{ $animation->match->team_b }}
                                            @endif
                                        </div>
                                        @if($animation->match->phase)
                                            <div class="text-xs text-gray-500">
                                                {{ ucfirst(str_replace('_', ' ', $animation->match->phase)) }}
                                                @if($animation->match->group_name)
                                                    - {{ $animation->match->group_name }}
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="text-gray-700 font-medium">
                                    {{ \Carbon\Carbon::parse($animation->animation_date)->format('d/m/Y') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($animation->animation_date)->locale('fr')->isoFormat('dddd') }}
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="text-gray-700 font-medium">{{ $animation->animation_time }}</span>
                            </td>
                            <td class="p-4 text-center">
                                @if($animation->is_active)
                                <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">Active</span>
                                @else
                                <span class="bg-red-100 text-red-700 text-xs font-bold px-3 py-1 rounded-full">Inactive</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-center gap-2 flex-wrap">
                                    <form action="{{ route('admin.toggle-animation', $animation->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-{{ $animation->is_active ? 'red' : 'green' }}-600 hover:underline text-sm font-bold whitespace-nowrap">
                                            {{ $animation->is_active ? 'Désactiver' : 'Activer' }}
                                        </button>
                                    </form>
                                    <span class="text-gray-300">|</span>
                                    <a href="{{ route('admin.edit-animation', $animation->id) }}" class="text-soboa-orange hover:underline text-sm font-bold">
                                        Modifier
                                    </a>
                                    <span class="text-gray-300">|</span>
                                    <form action="{{ route('admin.delete-animation', $animation->id) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette animation ?')">
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
                                <div class="flex flex-col items-center gap-4">
                                    <span class="text-6xl">📅</span>
                                    <div>
                                        <p class="font-bold text-lg">Aucune animation pour ce point de vente</p>
                                        <p class="text-sm mt-2">Cliquez sur "Nouvelle Animation" pour en ajouter une.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-layouts.app>
