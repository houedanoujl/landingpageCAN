<x-layouts.app title="Connexion">
    <div class="min-h-[calc(100vh-80px)] flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md">

            <!-- Logo et titre -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-white rounded-full mx-auto mb-4 shadow-lg flex items-center justify-center">
                    <img src="/images/logoSOBOA.png.webp" alt="SOBOA" class="w-16 h-16 object-contain">
                </div>
                <h1 class="text-3xl font-black text-soboa-blue">Connexion</h1>
                <p class="text-gray-600 mt-2">Entrez vos identifiants</p>
            </div>

            <!-- Formulaire de connexion -->
            <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8" x-data="loginForm()">

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

                <!-- Messages de succès -->
                <div x-show="success" x-cloak
                    class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span x-text="success" class="font-medium"></span>
                    </div>
                </div>

                <form @submit.prevent="login">
                    <!-- Numéro de téléphone -->
                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Numéro de téléphone</label>
                        <div class="flex gap-2">
                            <!-- Indicatif (Sénégal, ou Côte d'Ivoire en mode test) -->
                            <div class="px-4 py-3 border-2 border-gray-200 bg-gray-50 rounded-xl text-sm font-bold text-gray-700 flex items-center">
                                {{ ($testMode ?? false) ? '+225' : '+221' }}
                            </div>
                            <input type="tel" x-model="phone"
                                placeholder="{{ ($testMode ?? false) ? '07 12 34 56 78' : '77 123 45 67' }}"
                                class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-soboa-orange focus:ring-0 text-lg"
                                required autofocus>
                        </div>
                        @if($testMode ?? false)
                        <p class="text-xs text-orange-600 font-semibold mt-2">🧪 Mode test activé — numéros Côte d'Ivoire (+225) autorisés.</p>
                        @endif
                    </div>

                    <!-- Mot de passe -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Mot de passe</label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" x-model="password"
                                placeholder="Votre mot de passe"
                                class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-xl focus:border-soboa-orange focus:ring-0 text-lg"
                                required>
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                        <a href="/forgot-password" class="text-xs text-soboa-orange hover:underline mt-2 inline-block">
                            Mot de passe oublié ?
                        </a>
                    </div>

                    <!-- Bouton de connexion -->
                    <button type="submit" :disabled="loading"
                        class="w-full bg-soboa-orange hover:bg-soboa-orange-dark disabled:bg-gray-400 text-black font-bold py-4 px-6 rounded-xl shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2 mb-4">
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
                            Connexion...
                        </span>
                    </button>

                    <!-- Lien inscription -->
                    <div class="text-center pt-4 border-t border-gray-100">
                        <p class="text-sm text-gray-600 mb-2">Pas encore de compte ?</p>
                        <a href="/register" class="text-soboa-orange hover:underline font-bold">
                            Créer un compte
                        </a>
                    </div>
                </form>

                <!-- Conditions -->
                <p class="text-xs text-gray-500 text-center mt-6">
                    En vous connectant, vous acceptez nos
                    <a href="/conditions" class="text-soboa-blue hover:underline">conditions d'utilisation</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function loginForm() {
            return {
                phone: '',
                password: '',
                showPassword: false,
                loading: false,
                error: '',
                success: '',
                countryCode: @js($testMode ?? false ? '+225' : '+221'),
                testMode: @js((bool) ($testMode ?? false)),

                init() {
                    // Restaurer le numéro si sauvegardé
                    const savedPhone = localStorage.getItem('user_phone');
                    if (savedPhone) {
                        this.phone = savedPhone;
                    }
                },

                get fullPhone() {
                    return this.countryCode + this.formatPhoneNumber(this.phone);
                },

                formatPhoneNumber(phone) {
                    // Supprimer tout sauf les chiffres
                    return phone.replace(/\D/g, '');
                },

                // Valider le format du numéro
                // Mode test : Côte d'Ivoire (10 chiffres commençant par 0)
                // Sinon : Sénégal (9 chiffres commençant par 7)
                isValidPhone() {
                    const phoneDigits = this.formatPhoneNumber(this.phone);
                    if (this.testMode) {
                        return phoneDigits.length === 10 && phoneDigits.startsWith('0');
                    }
                    return phoneDigits.length === 9 && phoneDigits.startsWith('7');
                },

                async login() {
                    if (!this.phone.trim() || !this.password.trim()) {
                        this.error = 'Veuillez remplir tous les champs.';
                        return;
                    }

                    // Valider le format du numéro
                    if (!this.isValidPhone()) {
                        this.error = this.testMode
                            ? 'Le numéro doit contenir 10 chiffres commençant par 0 (ex: 07 12 34 56 78).'
                            : 'Le numéro doit contenir 9 chiffres commençant par 7 (ex: 77 123 45 67).';
                        return;
                    }

                    this.loading = true;
                    this.error = '';
                    this.success = '';

                    try {
                        const response = await fetch('/auth/login', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                phone: this.fullPhone,
                                password: this.password
                            })
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            // Sauvegarder le numéro pour la prochaine fois
                            localStorage.setItem('user_phone', this.phone);

                            this.success = 'Connexion réussie !';

                            // Redirection après succès
                            setTimeout(() => {
                                window.location.href = data.redirect || '/matches';
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
    <style>[x-cloak] { display: none !important; }</style>
</x-layouts.app>
