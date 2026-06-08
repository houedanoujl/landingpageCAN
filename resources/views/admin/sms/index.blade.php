<x-layouts.app title="Admin - Envoi SMS">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4">
            
            <!-- Header -->
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                        <span class="text-4xl"></span> Envoi de SMS
                    </h1>
                    <p class="text-gray-600 mt-2">Envoyez des SMS aux utilisateurs de l'application</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.sms.logs') }}" class="bg-soboa-blue hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
                        Journal des SMS
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Retour
                    </a>
                </div>
            </div>

            <!-- Messages de notification -->
            @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div x-data="smsForm()" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Formulaire d'envoi -->
                <div class="lg:col-span-2">
                    <form action="{{ route('admin.sms.send') }}" method="POST" class="bg-white rounded-xl shadow-lg p-6">
                        @csrf

                        <!-- Type de destinataires -->
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-3">Type de destinataires</label>
                            <div class="flex flex-wrap gap-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="recipient_type" value="selected" x-model="recipientType"
                                        class="w-4 h-4 text-soboa-blue focus:ring-soboa-blue">
                                    <span class="ml-2 text-gray-700">Utilisateurs sélectionnés</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="recipient_type" value="all" x-model="recipientType"
                                        class="w-4 h-4 text-soboa-blue focus:ring-soboa-blue">
                                    <span class="ml-2 text-gray-700">Tous les utilisateurs ({{ $users->count() }})</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="recipient_type" value="manual" x-model="recipientType"
                                        class="w-4 h-4 text-soboa-blue focus:ring-soboa-blue">
                                    <span class="ml-2 text-gray-700">Numéros manuels</span>
                                </label>
                            </div>
                        </div>

                        <!-- Sélection d'utilisateurs -->
                        <div x-show="recipientType === 'selected'" x-cloak class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-3">
                                Sélectionner les destinataires
                                <span class="text-gray-500 font-normal">(<span x-text="selectedUsers.length"></span> sélectionnés)</span>
                            </label>
                            
                            <!-- Recherche -->
                            <input type="text" x-model="searchQuery" placeholder="Rechercher un utilisateur..."
                                class="w-full px-4 py-2 mb-3 border border-gray-300 rounded-lg focus:ring-soboa-blue focus:border-soboa-blue">
                            
                            <!-- Boutons sélection -->
                            <div class="flex gap-2 mb-3">
                                <button type="button" @click="selectAll()" class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded">
                                    Tout sélectionner
                                </button>
                                <button type="button" @click="deselectAll()" class="px-3 py-1 text-sm bg-gray-200 hover:bg-gray-300 rounded">
                                    Tout désélectionner
                                </button>
                            </div>

                            <!-- Liste des utilisateurs -->
                            <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg">
                                @foreach($users as $user)
                                <label x-show="'{{ strtolower($user->name . ' ' . $user->phone) }}'.includes(searchQuery.toLowerCase())"
                                    class="flex items-center p-3 hover:bg-gray-50 border-b border-gray-100 cursor-pointer">
                                    <input type="checkbox" name="recipients[]" value="{{ $user->id }}"
                                        x-model="selectedUsers"
                                        class="w-4 h-4 text-soboa-blue focus:ring-soboa-blue rounded">
                                    <span class="ml-3">
                                        <span class="font-medium text-gray-800">{{ $user->name ?? 'Sans nom' }}</span>
                                        <span class="text-gray-500 text-sm ml-2">{{ $user->phone }}</span>
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Numéros manuels -->
                        <div x-show="recipientType === 'manual'" x-cloak class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Numéros de téléphone
                                <span class="text-gray-500 font-normal">(un par ligne ou séparés par virgule)</span>
                            </label>
                            <textarea name="manual_numbers" rows="4" placeholder="+2250545029721&#10;+2250707123456&#10;+221771234567"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-soboa-blue focus:border-soboa-blue"></textarea>
                        </div>

                        <!-- Message -->
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Message SMS
                                <span class="text-gray-500 font-normal">(<span x-text="message.length"></span>/1600 caractères)</span>
                            </label>
                            <textarea name="message" rows="5" x-model="message" required
                                placeholder="Tapez votre message ici..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-soboa-blue focus:border-soboa-blue"
                                maxlength="1600"></textarea>
                            
                            <!-- Compteur SMS -->
                            <div class="mt-2 text-sm text-gray-500">
                                <span x-text="Math.ceil(message.length / 160) || 0"></span> SMS
                                <span class="text-gray-400">(160 caractères par SMS)</span>
                            </div>
                        </div>

                        <!-- Modèles de messages -->
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Modèles de messages</label>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="useTemplate('match')"
                                    class="px-3 py-1 text-sm bg-soboa-blue text-white rounded-full hover:opacity-80">
                                    Match
                                </button>
                                <button type="button" @click="useTemplate('promo')"
                                    class="px-3 py-1 text-sm bg-soboa-orange text-white rounded-full hover:opacity-80">
                                    Promotion
                                </button>
                                <button type="button" @click="useTemplate('reminder')"
                                    class="px-3 py-1 text-sm bg-green-600 text-white rounded-full hover:opacity-80">
                                    Rappel
                                </button>
                                <button type="button" @click="useTemplate('winner')"
                                    class="px-3 py-1 text-sm bg-yellow-500 text-gray-800 rounded-full hover:opacity-80">
                                    Gagnant
                                </button>
                            </div>
                        </div>

                        <!-- Boutons d'envoi -->
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="showTestModal = true"
                                class="px-6 py-3 bg-gray-500 text-white font-bold rounded-xl hover:bg-gray-600 transition">
                                Test
                            </button>
                            <button type="submit"
                                class="px-6 py-3 bg-soboa-blue text-white font-bold rounded-xl hover:opacity-90 transition">
                                Envoyer les SMS
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Sidebar avec stats -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Statistiques -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Statistiques</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Utilisateurs avec téléphone</span>
                                <span class="font-bold text-soboa-blue">{{ $users->count() }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Sélectionnés</span>
                                <span class="font-bold text-soboa-blue" x-text="recipientType === 'all' ? {{ $users->count() }} : selectedUsers.length"></span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Coût estimé</span>
                                <span class="font-bold text-soboa-orange" x-text="'~' + estimatedCost + ' €'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Aide -->
                    <div class="bg-blue-50 rounded-xl p-6 border border-blue-200">
                        <h3 class="text-lg font-bold text-blue-800 mb-3">Conseils</h3>
                        <ul class="text-sm text-blue-700 space-y-2">
                            <li>• Un SMS = 160 caractères max</li>
                            <li>• Évitez les caractères spéciaux</li>
                            <li>• Testez avant d'envoyer en masse</li>
                            <li>• Les numéros ivoiriens commencent par +225</li>
                        </ul>
                    </div>
                </div>

                <!-- Modal de test -->
                <div x-show="showTestModal" x-cloak
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                    @click.self="showTestModal = false">
                    <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Envoyer un SMS de test</h3>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Numéro de test</label>
                            <input type="text" x-model="testPhone" placeholder="+2250545029721"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-soboa-blue focus:border-soboa-blue">
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Message</label>
                            <textarea x-model="message" rows="3" readonly
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50"></textarea>
                        </div>

                        <div x-show="testResult" x-cloak class="mb-4 p-3 rounded-lg" :class="testSuccess ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                            <span x-text="testResult"></span>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button type="button" @click="showTestModal = false; testResult = ''"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                Annuler
                            </button>
                            <button type="button" @click="sendTest()" :disabled="testLoading"
                                class="px-4 py-2 bg-soboa-blue text-white rounded-lg hover:opacity-90 disabled:opacity-50">
                                <span x-show="!testLoading">Envoyer le test</span>
                                <span x-show="testLoading">Envoi...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function smsForm() {
            return {
                recipientType: 'selected',
                selectedUsers: [],
                searchQuery: '',
                message: '',
                showTestModal: false,
                testPhone: '+2250545029721',
                testLoading: false,
                testResult: '',
                testSuccess: false,

                get estimatedCost() {
                    let count = this.recipientType === 'all' ? {{ $users->count() }} : this.selectedUsers.length;
                    let smsCount = Math.ceil(this.message.length / 160) || 1;
                    return (count * smsCount * 0.05).toFixed(2);
                },

                selectAll() {
                    this.selectedUsers = @json($users->pluck('id')->map(fn($id) => (string)$id));
                },

                deselectAll() {
                    this.selectedUsers = [];
                },

                useTemplate(type) {
                    const templates = {
                        match: "Ne manquez pas le match de ce soir ! Rejoignez-nous dans l'un de nos bars partenaires pour vivre l'ambiance. SOBOA - Le gout de la victoire !",
                        promo: "PROMO SPECIALE ! Profitez de -20% sur toutes les bieres SOBOA dans nos bars partenaires ce weekend. A consommer avec moderation.",
                        reminder: "RAPPEL: Votre pronostic pour le match de ce soir expire dans 1 heure ! Connectez-vous vite pour valider votre prediction. Bonne chance !",
                        winner: "FELICITATIONS ! Vous avez gagne [PRIX] grace a vos pronostics ! Contactez-nous pour recuperer votre lot. SOBOA - Le gout de la victoire !"
                    };
                    this.message = templates[type] || '';
                },

                async sendTest() {
                    if (!this.testPhone || !this.message) {
                        this.testResult = 'Veuillez remplir tous les champs';
                        this.testSuccess = false;
                        return;
                    }

                    this.testLoading = true;
                    this.testResult = '';

                    try {
                        const response = await fetch('{{ route("admin.sms.test") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                phone: this.testPhone,
                                message: this.message
                            })
                        });

                        const data = await response.json();
                        this.testResult = data.message;
                        this.testSuccess = data.success;
                    } catch (err) {
                        this.testResult = 'Erreur de connexion';
                        this.testSuccess = false;
                    } finally {
                        this.testLoading = false;
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-layouts.app>
