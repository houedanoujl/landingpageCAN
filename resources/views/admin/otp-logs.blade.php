<x-layouts.app title="Logs OTP - Administration">
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <!-- En-tête -->
            <div class="mb-8">
                <h1 class="text-4xl font-black text-gray-900">Historique des codes OTP</h1>
                <p class="text-gray-600 mt-2">Suivez tous les codes d'accès administrateur envoyés et les tentatives de connexion</p>
            </div>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Codes envoyés</p>
                            <p class="text-3xl font-bold text-blue-600">{{ $stats['total_sent'] }}</p>
                        </div>
                        <div class="text-4xl"></div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Connexions réussies</p>
                            <p class="text-3xl font-bold text-green-600">{{ $stats['total_verified'] }}</p>
                        </div>
                        <div class="text-4xl"></div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Envois échoués</p>
                            <p class="text-3xl font-bold text-red-600">{{ $stats['total_failed'] }}</p>
                        </div>
                        <div class="text-4xl"></div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Codes expirés</p>
                            <p class="text-3xl font-bold text-orange-600">{{ $stats['total_expired'] }}</p>
                        </div>
                        <div class="text-4xl"></div>
                    </div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <form method="GET" class="space-y-4 md:space-y-0 md:flex md:gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filtrer par statut</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Tous les statuts</option>
                            <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Envoyé</option>
                            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Vérifié</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Échoué</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expiré</option>
                        </select>
                    </div>

                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filtrer par téléphone</label>
                        <input type="text" name="phone" placeholder="Ex: 0748348221" value="{{ request('phone') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                            Filtrer
                        </button>
                        <a href="{{ route('admin.otp-logs') }}" class="px-6 py-2 bg-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-400 transition">
                            Réinitialiser
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tableau des logs -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Téléphone</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Code</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Statut</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Envoyé à</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Vérifié à</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Tentatives</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Erreur</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($otpLogs as $log)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $log->phone }}</td>
                                    <td class="px-6 py-4 text-sm font-mono font-bold text-blue-600 bg-gray-50">{{ $log->code }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($log->status === 'sent')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                                Envoyé
                                            </span>
                                        @elseif($log->status === 'verified')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                Vérifié
                                            </span>
                                        @elseif($log->status === 'failed')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                Échoué
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                                Expiré
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $log->otp_sent_at->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        @if($log->otp_verified_at)
                                            {{ $log->otp_verified_at->format('d/m/Y H:i:s') }}
                                            <br>
                                            <span class="text-xs text-gray-500">
                                                ({{ $log->otp_verified_at->diffInSeconds($log->otp_sent_at) }}s)
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold">
                                        @if($log->verification_attempts > 0)
                                            <span class="text-orange-600">{{ $log->verification_attempts }}</span>
                                        @else
                                            <span class="text-green-600">0</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-red-600">
                                        @if($log->error_message)
                                            <details class="cursor-pointer">
                                                <summary class="font-medium">Voir erreur</summary>
                                                <p class="mt-2 p-2 bg-red-50 rounded text-xs break-words">
                                                    {{ $log->error_message }}
                                                </p>
                                            </details>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        <div class="text-4xl mb-2"></div>
                                        <p>Aucun log OTP trouvé</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($otpLogs->hasPages())
                <div class="mt-8 flex justify-center">
                    {{ $otpLogs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
