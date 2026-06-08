<x-layouts.app title="Admin - Journal SMS">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">

            <!-- Header -->
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                        <span class="text-4xl"></span> Journal des SMS
                    </h1>
                    <p class="text-gray-600 mt-2">Historique des SMS envoyés via l'application</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.sms') }}" class="bg-soboa-blue hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
                        Envoyer un SMS
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition">
                        Retour
                    </a>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow p-5 text-center">
                    <div class="text-3xl font-black text-soboa-blue">{{ $stats['total'] }}</div>
                    <div class="text-gray-500 text-sm uppercase tracking-wide">Total</div>
                </div>
                <div class="bg-white rounded-xl shadow p-5 text-center">
                    <div class="text-3xl font-black text-green-600">{{ $stats['sent'] }}</div>
                    <div class="text-gray-500 text-sm uppercase tracking-wide">Envoyés</div>
                </div>
                <div class="bg-white rounded-xl shadow p-5 text-center">
                    <div class="text-3xl font-black text-red-600">{{ $stats['failed'] }}</div>
                    <div class="text-gray-500 text-sm uppercase tracking-wide">Échoués</div>
                </div>
            </div>

            <!-- Filtres -->
            <form method="GET" class="mb-6 flex flex-wrap gap-3 items-end bg-white rounded-xl shadow p-4">
                <div>
                    <label class="block text-gray-600 text-sm font-bold mb-1">Statut</label>
                    <select name="status" class="border border-gray-300 rounded-lg p-2">
                        <option value="">Tous</option>
                        <option value="sent" @selected(request('status') === 'sent')>Envoyés</option>
                        <option value="failed" @selected(request('status') === 'failed')>Échoués</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-600 text-sm font-bold mb-1">Origine</label>
                    <select name="context" class="border border-gray-300 rounded-lg p-2">
                        <option value="">Toutes</option>
                        <option value="admin_bulk" @selected(request('context') === 'admin_bulk')>Envoi admin</option>
                        <option value="test" @selected(request('context') === 'test')>Test</option>
                    </select>
                </div>
                <button type="submit" class="bg-soboa-blue text-white px-4 py-2 rounded-lg font-medium">Filtrer</button>
                @if(request('status') || request('context'))
                <a href="{{ route('admin.sms.logs') }}" class="text-gray-500 px-2 py-2">Réinitialiser</a>
                @endif
            </form>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="text-left p-3">Date</th>
                                <th class="text-left p-3">Destinataire</th>
                                <th class="text-left p-3">Message</th>
                                <th class="text-left p-3">Statut</th>
                                <th class="text-left p-3">Origine</th>
                                <th class="text-left p-3">Par</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($logs as $log)
                            <tr class="hover:bg-gray-50 align-top">
                                <td class="p-3 whitespace-nowrap text-gray-500">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                <td class="p-3 font-mono">{{ $log->to_number }}</td>
                                <td class="p-3 max-w-md">
                                    <div class="text-gray-800">{{ \Illuminate\Support\Str::limit($log->message, 120) }}</div>
                                    @if($log->error)
                                    <div class="text-red-500 text-xs mt-1">{{ \Illuminate\Support\Str::limit($log->error, 120) }}</div>
                                    @endif
                                </td>
                                <td class="p-3">
                                    @if($log->status === 'sent')
                                    <span class="inline-block px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">Envoyé</span>
                                    @else
                                    <span class="inline-block px-2 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold">Échoué</span>
                                    @endif
                                </td>
                                <td class="p-3 text-gray-500">{{ $log->context ?? '—' }}</td>
                                <td class="p-3 text-gray-500">{{ $log->sender->name ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-400">Aucun SMS envoyé pour le moment.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $logs->links() }}
            </div>

        </div>
    </div>
</x-layouts.app>
