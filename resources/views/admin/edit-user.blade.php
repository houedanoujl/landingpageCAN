<x-layouts.app title="Admin - Modifier Utilisateur">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-2xl mx-auto px-4">
            
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

            <div class="bg-white rounded-xl shadow-lg p-6">
                <form action="{{ route('admin.update-user', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
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
                                        class="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded-lg transition whitespace-nowrap">
                                    🔄 Réinitialiser
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                💡 Le bouton "Réinitialiser" mettra les points à zéro et supprimera l'historique des points
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
                                    class="text-red-600 hover:underline font-bold">
                                🗑️ Supprimer
                            </button>
                            <div class="flex gap-4">
                                <a href="{{ route('admin.users') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-lg transition">
                                    Annuler
                                </a>
                                <button type="submit" class="bg-soboa-blue hover:bg-soboa-blue/90 text-white font-bold py-3 px-6 rounded-lg transition">
                                    Enregistrer
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                
                <!-- Formulaire de suppression SÉPARÉ (en dehors du formulaire principal) -->
                <form id="delete-user-form" action="{{ route('admin.delete-user', $user->id) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
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

            // Afficher un loader
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '⏳ En cours...';
            btn.disabled = true;

            // Envoyer la requête
            fetch(`/admin/users/${userId}/reset-points`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour le champ de points
                    document.querySelector('input[name="points_total"]').value = 0;
                    
                    // Afficher un message de succès
                    alert('✅ Points réinitialisés avec succès!\n\n' + data.message);
                    
                    // Recharger la page pour avoir les données à jour
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
