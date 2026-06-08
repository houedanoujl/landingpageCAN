<x-layouts.app title="Admin - Équipes">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                        <span class="text-4xl"></span> Équipes
                    </h1>
                    <p class="text-gray-600 mt-2">Gérez les équipes participantes à SOBOA FOOT TIME</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg transition">
                        ← Retour
                    </a>
                    <a href="{{ route('admin.create-team') }}" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition flex items-center gap-2">
                        <span>+</span> Nouvelle Équipe
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

            <!-- Liste -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 p-6">
                    @forelse($teams as $team)
                    <div class="bg-gray-50 rounded-xl p-4 text-center hover:shadow-lg transition group">
                        <div class="flag-container mx-auto mb-3">
                            <img src="https://flagicons.lipis.dev/flags/4x3/{{ $team->iso_code }}.svg"
                                 alt="{{ $team->name }}"
                                 class="w-16 h-12 object-cover rounded shadow"
                                 onerror="this.parentElement.innerHTML='<div class=\'w-16 h-12 bg-gradient-to-br from-soboa-blue to-blue-600 rounded shadow flex items-center justify-center\'><span class=\'text-white text-xs font-black\'>{{ mb_substr($team->iso_code, 0, 2) }}</span></div>'">
                        </div>
                        <p class="font-bold text-gray-800">{{ $team->name }}</p>
                        <p class="text-xs text-gray-500 uppercase">{{ $team->iso_code }}</p>
                        @if($team->group_name)
                        <span class="inline-block mt-2 bg-soboa-blue/10 text-soboa-blue text-xs font-bold px-2 py-1 rounded">
                            {{ $team->group_name }}
                        </span>
                        @endif
                        <div class="mt-3 flex justify-center gap-2 opacity-0 group-hover:opacity-100 transition">
                            <a href="{{ route('admin.edit-team', $team->id) }}" class="text-soboa-orange hover:underline text-xs font-bold">
                                Modifier
                            </a>
                            <span class="text-gray-300">|</span>
                            <form action="{{ route('admin.delete-team', $team->id) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-xs font-bold">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full p-8 text-center text-gray-500">
                        Aucune équipe trouvée.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $teams->links() }}
            </div>

        </div>
    </div>
</x-layouts.app>
