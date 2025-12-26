<x-layouts.app title="Admin - Modifier Équipe">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-2xl mx-auto px-4">
            
            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('admin.teams') }}" class="text-soboa-orange hover:underline font-bold mb-2 inline-block">
                    ← Retour aux équipes
                </a>
                <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                    <img src="https://flagcdn.com/w40/{{ $team->iso_code }}.png" class="w-10 h-7 rounded shadow">
                    Modifier {{ $team->name }}
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
                <form action="{{ route('admin.update-team', $team->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Nom de l'équipe *</label>
                            <input type="text" name="name" value="{{ old('name', $team->name) }}" required
                                   class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue"
                                   placeholder="Ex: Côte d'Ivoire">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Code ISO (2 lettres) *</label>
                            <input type="text" name="iso_code" value="{{ old('iso_code', $team->iso_code) }}" required
                                   maxlength="2" pattern="[A-Za-z]{2}"
                                   class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue uppercase"
                                   placeholder="Ex: ci">
                            <p class="text-gray-500 text-sm mt-1">Code pays ISO 3166-1 alpha-2 (pour afficher le drapeau)</p>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Groupe</label>
                            <input type="text" name="group_name" value="{{ old('group_name', $team->group_name) }}"
                                   class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-soboa-blue focus:border-soboa-blue"
                                   placeholder="Ex: Groupe A">
                        </div>

                        <div class="flex justify-between items-center pt-4 border-t">
                            <button type="button" 
                                    onclick="deleteTeam()"
                                    class="text-red-600 hover:underline font-bold">
                                🗑️ Supprimer
                            </button>
                            <div class="flex gap-4">
                                <a href="{{ route('admin.teams') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded-lg transition">
                                    Annuler
                                </a>
                                <button type="submit" class="bg-soboa-blue hover:bg-soboa-blue/90 text-white font-bold py-3 px-6 rounded-lg transition">
                                    Enregistrer
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                
                <!-- Formulaire de suppression SÉPARÉ -->
                <form id="delete-team-form" action="{{ route('admin.delete-team', $team->id) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>

        </div>
    </div>
    
    <script>
        function deleteTeam() {
            if (confirm('⚠️ Êtes-vous sûr de vouloir supprimer cette équipe ?\n\nCette action est IRRÉVERSIBLE.')) {
                document.getElementById('delete-team-form').submit();
            }
        }
    </script>
</x-layouts.app>
