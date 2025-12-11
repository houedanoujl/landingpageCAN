<x-layouts.app title="Admin - Utilisateurs">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            
            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('admin.dashboard') }}" class="text-soboa-orange hover:underline text-sm font-bold mb-2 inline-block">← Retour au dashboard</a>
                <h1 class="text-3xl font-black text-soboa-blue">Utilisateurs</h1>
                <p class="text-gray-600 mt-2">{{ $users->total() }} utilisateurs inscrits</p>
            </div>

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
                            <td class="px-4 py-4 text-gray-600">{{ $user->phone_number }}</td>
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
