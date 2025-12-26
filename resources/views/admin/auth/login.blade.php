<x-layouts.app title="Connexion Administrateur">
    <div class="min-h-[calc(100vh-80px)] flex items-center justify-center px-4 py-8 bg-gradient-to-br from-gray-900 to-gray-800">
        <div class="w-full max-w-md">

            <!-- Logo et titre -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-red-600 rounded-full mx-auto mb-4 shadow-lg flex items-center justify-center">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                </div>
                <h1 class="text-3xl font-black text-white">Administration</h1>
                <p class="text-gray-300 mt-2">Accès réservé aux administrateurs</p>
            </div>

            <!-- Formulaire -->
            <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8" x-data="adminLoginForm()">

                <!-- Messages d'erreur -->
                <div x-show="error" x-cloak
                    class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span x-text="error" class="font-medium"></span>
                    </div>
                </div>

                <!-- Message de succès -->
                <div x-show="success" x-cloak
                    class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span x-text="success" class="font-medium"></span>
                    </div>
                </div>

                <!-- Formulaire de connexion -->
                <form @submit.prevent="login">
                    <!-- Nom d'utilisateur -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nom d'utilisateur</label>
                        <input type="text" x-model="username" placeholder="Entrez votre nom d'utilisateur"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-red-600 focus:ring-0 text-lg"
                            required autocomplete="username">
                    </div>

                    <!-- Mot de passe -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Mot de passe</label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" x-model="password" placeholder="••••••••"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-red-600 focus:ring-0 text-lg pr-12"
                                required autocomplete="current-password">
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg x-show="!showPassword" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg x-show="showPassword" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Bouton de connexion -->
                    <button type="submit" :disabled="loading"
                        class="w-full bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white font-bold py-4 px-6 rounded-xl shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2">
                        <span x-show="!loading">Se connecter</span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Connexion en cours...
                        </span>
                    </button>
                </form>

                <!-- Retour -->
                <div class="text-center mt-6">
                    <a href="/" class="text-sm text-gray-500 hover:text-gray-700">
                        ← Retour au site public
                    </a>
                </div>
            </div>

            <!-- Avertissement -->
            <div class="mt-6 text-center bg-red-900 bg-opacity-50 p-4 rounded-xl">
                <p class="text-sm text-white font-bold">
                    🔐 Zone d'administration sécurisée
                </p>
                <p class="text-xs text-gray-300 mt-2">
                    Toutes les actions sont enregistrées et surveillées
                </p>
            </div>
        </div>
    </div>

    <script>
        function adminLoginForm() {
            return {
                username: '',
                password: '',
                loading: false,
                error: '',
                success: '',
                showPassword: false,

                async login() {
                    if (!this.username.trim() || !this.password.trim()) {
                        this.error = 'Veuillez remplir tous les champs.';
                        return;
                    }

                    this.loading = true;
                    this.error = '';
                    this.success = '';

                    try {
                        const response = await fetch('/admin/login', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                username: this.username,
                                password: this.password
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.success = data.message || 'Connexion réussie !';
                            setTimeout(() => {
                                window.location.href = data.redirect || '/admin';
                            }, 500);
                        } else {
                            this.error = data.message || 'Identifiants incorrects.';
                        }
                    } catch (err) {
                        console.error('Erreur:', err);
                        this.error = 'Erreur de connexion. Veuillez réessayer.';
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }
    </script>
</x-layouts.app>
