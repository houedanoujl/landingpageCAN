<x-layouts.app title="Admin - Modération des commentaires">
    <div class="bg-gray-50 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4" x-data="{ tab: '{{ request('mc_page') ? 'wall' : 'predictions' }}' }">

            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                        <span class="text-3xl">💬</span> Modération des commentaires
                    </h1>
                    <p class="text-gray-600 mt-1">Gérez et modérez les commentaires sur les pronostics et le mur des matchs</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg transition">← Retour</a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6">{{ session('success') }}</div>
            @endif

            <!-- Onglets -->
            <div class="flex gap-2 mb-6">
                <button type="button" @click="tab = 'predictions'"
                        :class="tab === 'predictions' ? 'bg-soboa-blue text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-100'"
                        class="px-6 py-3 rounded-xl font-bold transition-all">
                    🎯 Commentaires pronostics ({{ $comments->total() }})
                </button>
                <button type="button" @click="tab = 'wall'"
                        :class="tab === 'wall' ? 'bg-soboa-orange text-black shadow-md' : 'bg-white text-gray-600 hover:bg-gray-100'"
                        class="px-6 py-3 rounded-xl font-bold transition-all">
                    📣 Mur des matchs ({{ $matchComments->total() }})
                </button>
            </div>

            <!-- Commentaires sur les pronostics -->
            <div x-show="tab === 'predictions'" class="bg-white rounded-2xl shadow overflow-hidden">
                @if($comments->isEmpty())
                    <div class="p-12 text-center text-gray-400">
                        <p class="text-lg font-medium">Aucun commentaire pour l'instant.</p>
                    </div>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-soboa-blue text-white text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">Utilisateur</th>
                                <th class="px-4 py-3 text-left">Commentaire</th>
                                <th class="px-4 py-3 text-left">Pronostic (match)</th>
                                <th class="px-4 py-3 text-center">Date</th>
                                <th class="px-4 py-3 text-center">🚩</th>
                                <th class="px-4 py-3 text-center">Statut</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($comments as $comment)
                                <tr class="hover:bg-gray-50 transition {{ $comment->is_moderated ? 'opacity-50' : '' }}">
                                    <td class="px-4 py-3 font-bold text-gray-700">{{ $comment->user->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600 max-w-xs">{{ Str::limit($comment->body, 100) }}</td>
                                    <td class="px-4 py-3 text-gray-500 text-xs">
                                        @if($comment->prediction && $comment->prediction->match)
                                            {{ $comment->prediction->match->team_a }} vs {{ $comment->prediction->match->team_b }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-400 text-xs">{{ $comment->created_at->diffForHumans() }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if(($comment->reports_count ?? 0) > 0)
                                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-600">🚩 {{ $comment->reports_count }}</span>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-1 text-xs font-bold rounded-full {{ $comment->is_moderated ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-700' }}">
                                            {{ $comment->is_moderated ? 'Masqué' : 'Visible' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <form method="POST" action="{{ route('admin.prediction-comments.moderate', $comment) }}">
                                                @csrf
                                                <button type="submit" class="text-xs font-bold text-soboa-blue hover:underline">
                                                    {{ $comment->is_moderated ? 'Réactiver' : 'Masquer' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.prediction-comments.destroy', $comment) }}" onsubmit="return confirm('Supprimer ce commentaire ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs font-bold text-red-500 hover:underline">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="p-4">{{ $comments->links() }}</div>
                @endif
            </div>

            <!-- Commentaires du mur des matchs -->
            <div x-show="tab === 'wall'" x-cloak class="bg-white rounded-2xl shadow overflow-hidden">
                @if($matchComments->isEmpty())
                    <div class="p-12 text-center text-gray-400">
                        <p class="text-lg font-medium">Aucun commentaire sur le mur pour l'instant.</p>
                    </div>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-soboa-orange text-black text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">Utilisateur</th>
                                <th class="px-4 py-3 text-left">Commentaire</th>
                                <th class="px-4 py-3 text-left">Match</th>
                                <th class="px-4 py-3 text-center">Date</th>
                                <th class="px-4 py-3 text-center">🚩</th>
                                <th class="px-4 py-3 text-center">Statut</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($matchComments as $comment)
                                <tr class="hover:bg-gray-50 transition {{ $comment->is_moderated ? 'opacity-50' : '' }}">
                                    <td class="px-4 py-3 font-bold text-gray-700">{{ $comment->user->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600 max-w-xs">{{ Str::limit($comment->body, 100) }}</td>
                                    <td class="px-4 py-3 text-gray-500 text-xs">
                                        @if($comment->match)
                                            {{ $comment->match->team_a }} vs {{ $comment->match->team_b }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-400 text-xs">{{ $comment->created_at->diffForHumans() }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if(($comment->reports_count ?? 0) > 0)
                                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-600">🚩 {{ $comment->reports_count }}</span>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-1 text-xs font-bold rounded-full {{ $comment->is_moderated ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-700' }}">
                                            {{ $comment->is_moderated ? 'Masqué' : 'Visible' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <form method="POST" action="{{ route('admin.match-comments.moderate', $comment) }}">
                                                @csrf
                                                <button type="submit" class="text-xs font-bold text-soboa-blue hover:underline">
                                                    {{ $comment->is_moderated ? 'Réactiver' : 'Masquer' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.match-comments.destroy', $comment) }}" onsubmit="return confirm('Supprimer ce commentaire ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs font-bold text-red-500 hover:underline">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="p-4">{{ $matchComments->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</x-layouts.app>
