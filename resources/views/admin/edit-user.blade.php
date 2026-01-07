<x-layouts.app title="Admin - Modifier Utilisateur">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            
            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('admin.users') }}" class="text-soboa-orange hover:underline font-bold mb-2 inline-block">
                    ← Retour aux utilisateurs
                </a>
                <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                    <span class="text-4xl">👤</span> Modifier Utilisateur
                </h1>
            </div>

            @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Layout 2 colonnes -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- COLONNE GAUCHE : Édition -->
                <div>
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-bold text-soboa-blue mb-4 flex items-center gap-2">
                            ✏️ Informations
                        </h2>
                        
                        <form action="{{ route('admin.update-user', $user->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="space-y-5">
                                <div>
                                    <label class="block text-gray-700 font-bold mb-2">Nom *</label>
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2">Téléphone *</label>
                                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required
                                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2">Email</label>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                           class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2">Rôle *</label>
                                    <select name="role" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                                        <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Utilisateur</option>
                                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrateur</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2">Points Total *</label>
                                    <div class="flex gap-3 items-center">
                                        <input type="number" name="points_total" value="{{ old('points_total', $user->points_total) }}" required min="0"
                                               class="flex-1 border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue">
                                        <button type="button" 
                                                onclick="resetUserPoints({{ $user->id }})"
                                                class="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded-lg transition whitespace-nowrap text-sm">
                                            🔄 Reset
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">
                                        💡 "Reset" mettra les points à zéro et supprimera l'historique
                                    </p>
                                </div>

                                <!-- Info -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-sm text-gray-600">
                                        <strong>Créé le :</strong> {{ $user->created_at->format('d/m/Y H:i') }}<br>
                                        <strong>Dernière connexion :</strong> {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais' }}<br>
                                        <strong>Téléphone vérifié :</strong> {{ $user->phone_verified ? 'Oui' : 'Non' }}
                                    </p>
                                </div>

                                <div class="flex justify-between items-center pt-4 border-t">
                                    <button type="button" 
                                            onclick="deleteUser({{ $user->id }})"
                                            class="text-red-600 hover:underline font-bold text-sm">
                                        🗑️ Supprimer
                                    </button>
                                    <div class="flex gap-3">
                                        <a href="{{ route('admin.users') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg transition text-sm">
                                            Annuler
                                        </a>
                                        <button type="submit" class="bg-soboa-blue hover:bg-soboa-blue/90 text-white font-bold py-2 px-4 rounded-lg transition text-sm">
                                            Enregistrer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Formulaire de suppression SÉPARÉ -->
                        <form id="delete-user-form" action="{{ route('admin.delete-user', $user->id) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>

                <!-- COLONNE DROITE : Historique des Points -->
                <div>
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        @php
                            $totalPoints = $pointLogs->sum('points');
                            $participation = $pointLogs->where('source', 'prediction_participation')->sum('points');
                            $winner = $pointLogs->where('source', 'prediction_winner')->sum('points');
                            $exact = $pointLogs->where('source', 'prediction_exact')->sum('points');
                            $admin = $pointLogs->where('source', 'admin_bonus')->sum('points');
                            $other = $pointLogs->whereNotIn('source', ['prediction_participation', 'prediction_winner', 'prediction_exact', 'admin_bonus'])->sum('points');
                        @endphp

                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-soboa-blue flex items-center gap-2">
                                📊 Historique des Points
                            </h2>
                            <div class="flex items-center gap-2">
                                <span class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">{{ $pointLogs->count() }} actions</span>
                                <span class="bg-soboa-orange text-black font-black text-lg px-3 py-1 rounded-full">{{ $totalPoints }} pts</span>
                                @if($pointLogs->count() > 0)
                                <a href="{{ route('admin.export-user-points-csv', $user->id) }}" 
                                   class="bg-green-600 hover:bg-green-700 text-white font-bold py-1 px-3 rounded-lg transition text-xs flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Export
                                </a>
                                @endif
                            </div>
                        </div>

                        @if($pointLogs->count() > 0)
                            <!-- Résumé par type -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-4">
                                <div class="bg-blue-50 rounded-lg p-2 text-center">
                                    <div class="text-xl font-black text-blue-600">{{ $participation }}</div>
                                    <div class="text-xs text-blue-700 font-medium">Participation</div>
                                </div>
                                <div class="bg-green-50 rounded-lg p-2 text-center">
                                    <div class="text-xl font-black text-green-600">{{ $winner }}</div>
                                    <div class="text-xs text-green-700 font-medium">Bon vainqueur</div>
                                </div>
                                <div class="bg-yellow-50 rounded-lg p-2 text-center">
                                    <div class="text-xl font-black text-yellow-600">{{ $exact }}</div>
                                    <div class="text-xs text-yellow-700 font-medium">Score exact</div>
                                </div>
                                <div class="bg-purple-50 rounded-lg p-2 text-center">
                                    <div class="text-xl font-black text-purple-600">{{ $admin + $other }}</div>
                                    <div class="text-xs text-purple-700 font-medium">Bonus/Autre</div>
                                </div>
                            </div>

                            <!-- Liste détaillée -->
                            <div class="overflow-x-auto max-h-[450px] overflow-y-auto border rounded-lg">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-100 sticky top-0">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-bold text-xs">Date</th>
                                            <th class="px-3 py-2 text-left font-bold text-xs">Source</th>
                                            <th class="px-3 py-2 text-left font-bold text-xs">Match</th>
                                            <th class="px-3 py-2 text-center font-bold text-xs">Pts</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($pointLogs as $log)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 text-gray-500 text-xs whitespace-nowrap">
                                                {{ $log->created_at->format('d/m H:i') }}
                                            </td>
                                            <td class="px-3 py-2">
                                                @switch($log->source)
                                                    @case('prediction_participation')
                                                        <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs font-medium">🎯 Particip.</span>
                                                        @break
                                                    @case('prediction_winner')
                                                        <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs font-medium">✅ Vainqueur</span>
                                                        @break
                                                    @case('prediction_exact')
                                                        <span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full text-xs font-medium">🎉 Exact</span>
                                                        @break
                                                    @case('admin_bonus')
                                                        <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full text-xs font-medium">🎁 Bonus</span>
                                                        @break
                                                    @case('check_in')
                                                        <span class="bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full text-xs font-medium">📍 Check-in</span>
                                                        @break
                                                    @default
                                                        <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full text-xs font-medium">{{ $log->source }}</span>
                                                @endswitch
                                            </td>
                                            <td class="px-3 py-2 text-gray-600 text-xs">
                                                @if($log->match)
                                                    {{ $log->match->homeTeam->name ?? '?' }} vs {{ $log->match->awayTeam->name ?? '?' }}
                                                @elseif($log->bar)
                                                    📍 {{ $log->bar->name }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <span class="font-bold {{ $log->points > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $log->points > 0 ? '+' : '' }}{{ $log->points }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-12 text-gray-500">
                                <div class="text-5xl mb-3">📭</div>
                                <p class="font-medium">Aucun historique de points</p>
                                <p class="text-sm">Cet utilisateur n'a pas encore gagné de points</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function deleteUser(userId) {
            if (confirm('⚠️ Êtes-vous sûr de vouloir supprimer cet utilisateur ?\n\nCette action est IRRÉVERSIBLE.')) {
                document.getElementById('delete-user-form').submit();
            }
        }

        function resetUserPoints(userId) {
            if (!confirm('⚠️ ATTENTION!\n\nCette action va:\n• Mettre les points à zéro\n• Supprimer tout l\'historique des points\n• Cette action est IRRÉVERSIBLE\n\nÊtes-vous absolument sûr ?')) {
                return;
            }

            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '⏳...';
            btn.disabled = true;

            fetch(\`/admin/users/${userId}/reset-points\`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector('input[name="points_total"]').value = 0;
                    alert('✅ Points réinitialisés avec succès!\n\n' + data.message);
                    window.location.reload();
                } else {
                    alert('❌ Erreur: ' + (data.message || 'Une erreur est survenue'));
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Erreur lors de la réinitialisation des points');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
    </script>
</x-layouts.app>
