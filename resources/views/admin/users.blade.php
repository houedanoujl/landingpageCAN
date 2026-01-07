<x-layouts.app title="Admin - Utilisateurs">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="text-soboa-orange hover:underline text-sm font-bold mb-2 inline-block">← Retour au dashboard</a>
                    <h1 class="text-3xl font-black text-soboa-blue">Utilisateurs</h1>
                    <p class="text-gray-600 mt-2">{{ $users->total() }} utilisateurs {{ $search ? 'trouvés' : 'inscrits' }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.point-logs') }}" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Historique
                    </a>
                    <a href="{{ route('admin.export-users-csv') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export CSV
                    </a>
                </div>
            </div>

            <!-- Search Box -->
            <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
                <form action="{{ route('admin.users') }}" method="GET" class="flex gap-4">
                    <div class="flex-1 relative">
                        <input type="text" 
                               name="search" 
                               value="{{ $search ?? '' }}" 
                               placeholder="Rechercher par nom, téléphone ou email..." 
                               class="w-full border border-gray-300 rounded-lg p-3 pl-10 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <button type="submit" class="bg-soboa-blue hover:bg-soboa-blue/90 text-white font-bold py-3 px-6 rounded-lg transition">
                        Rechercher
                    </button>
                    @if($search)
                    <a href="{{ route('admin.users') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-lg transition">
                        Effacer
                    </a>
                    @endif
                </form>
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

            <!-- Users Table -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-soboa-blue text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-bold">Rang</th>
                            <th class="px-4 py-3 text-left text-sm font-bold">Nom</th>
                            <th class="px-4 py-3 text-left text-sm font-bold">Téléphone</th>
                            <th class="px-4 py-3 text-center text-sm font-bold">Points</th>
                            <th class="px-4 py-3 text-center text-sm font-bold">Rôle</th>
                            <th class="px-4 py-3 text-left text-sm font-bold">Inscrit le</th>
                            <th class="px-4 py-3 text-center text-sm font-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($users as $index => $user)
                        <tr class="hover:bg-gray-50 {{ $user->role === 'admin' ? 'bg-yellow-50' : '' }}">
                            <td class="px-4 py-4">
                                @php $rank = ($users->currentPage() - 1) * $users->perPage() + $index + 1; @endphp
                                @if($rank == 1)
                                <span class="text-2xl">🥇</span>
                                @elseif($rank == 2)
                                <span class="text-2xl">🥈</span>
                                @elseif($rank == 3)
                                <span class="text-2xl">🥉</span>
                                @else
                                <span class="font-bold text-gray-600">{{ $rank }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-soboa-blue/20 rounded-full flex items-center justify-center font-bold text-soboa-blue">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </div>
                                    <span class="font-bold">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-gray-600">{{ $user->phone }}</td>
                            <td class="px-4 py-4 text-center">
                                <span class="font-black text-soboa-orange text-xl">{{ $user->points_total }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($user->role === 'admin')
                                <span class="bg-purple-100 text-purple-700 font-bold px-3 py-1 rounded-full text-sm">Admin</span>
                                @else
                                <span class="bg-gray-100 text-gray-600 font-bold px-3 py-1 rounded-full text-sm">User</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-gray-500 text-sm">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-4 text-center">
                                <a href="{{ route('admin.edit-user', $user->id) }}" class="text-soboa-orange hover:underline text-sm font-bold">
                                    Modifier
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $users->links() }}
            </div>

        </div>
    </div>
</x-layouts.app>
