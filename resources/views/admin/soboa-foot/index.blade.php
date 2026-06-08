<x-layouts.app title="Admin - Animation SOBOA FOOT">
    <div class="bg-gray-50 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">

            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                        <span class="text-4xl"></span> Animation SOBOA FOOT
                    </h1>
                    <p class="text-gray-600 mt-1">Gérez les contenus du volet Animation SOBOA FOOT</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg transition">← Retour</a>
                    <a href="{{ route('admin.soboa-foot.create') }}" class="bg-soboa-orange hover:bg-soboa-orange-secondary text-white font-bold py-2 px-5 rounded-lg transition flex items-center gap-2">
                        + Nouveau contenu
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-2xl shadow overflow-hidden">
                @if($contents->isEmpty())
                    <div class="p-12 text-center text-gray-400">
                        <p class="text-lg font-medium">Aucun contenu. Créez le premier !</p>
                    </div>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-soboa-blue text-white text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">#</th>
                                <th class="px-4 py-3 text-left">Titre</th>
                                <th class="px-4 py-3 text-left">Type</th>
                                <th class="px-4 py-3 text-center">Ordre</th>
                                <th class="px-4 py-3 text-center">Statut</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($contents as $content)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-gray-400">{{ $content->id }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-800">{{ $content->title }}</div>
                                        @if($content->body)
                                            <div class="text-gray-400 text-xs">{{ Str::limit($content->body, 60) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 bg-soboa-blue/10 text-soboa-blue text-xs font-bold rounded-full">
                                            {{ \App\Models\SoboaContent::$types[$content->type] ?? $content->type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-600">{{ $content->sort_order }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <form method="POST" action="{{ route('admin.soboa-foot.toggle', $content) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 text-xs font-bold rounded-full transition
                                                {{ $content->is_published ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-200 text-gray-500 hover:bg-gray-300' }}">
                                                {{ $content->is_published ? 'Publié' : 'Brouillon' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.soboa-foot.edit', $content) }}" class="text-soboa-blue hover:underline text-xs font-bold">Modifier</a>
                                            <form method="POST" action="{{ route('admin.soboa-foot.destroy', $content) }}" onsubmit="return confirm('Supprimer ce contenu ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:underline text-xs font-bold">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="p-4">{{ $contents->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
