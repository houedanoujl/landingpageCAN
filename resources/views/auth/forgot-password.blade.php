<x-layouts.app title="Mot de passe oublié">
    <div class="min-h-[calc(100vh-80px)] flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md">

            <!-- Logo et titre -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-white rounded-full mx-auto mb-4 shadow-lg flex items-center justify-center">
                    <img src="/images/logoSOBOA.png.webp" alt="SOBOA" class="w-16 h-16 object-contain">
                </div>
                <h1 class="text-3xl font-black text-soboa-blue">Mot de passe oublié</h1>
                <p class="text-gray-600 mt-2">Récupérez votre accès par SMS</p>
            </div>

            <!-- Formulaire -->
            <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8" x-data="forgotPasswordForm()">

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

                <!-- Formulaire de demande (étape 1) -->
                <div x-show="!smsSent">
                    <form @submit.prevent="resetPassword">
                        <!-- Numéro de téléphone -->
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Numéro de téléphone</label>
                            <div class="flex gap-2">
                                <div class="px-4 py-3 border-2 border-gray-200 bg-gray-50 rounded-xl text-sm font-bold text-gray-700 flex items-center">
                                    +221
                                </div>
                                <input type="tel" x-model="phone"
                                    placeholder="77 123 45 67"
                                    class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-soboa-orange focus:ring-0 text-lg"
                                    required autofocus>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                Entrez le numéro associé à votre compte
                            </p>
                        </div>

                        <!-- Info SMS -->
                        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl mb-6 text-sm">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p>Votre nouveau mot de passe sera envoyé par <strong>SMS</strong> sur ce numéro.</p>
                            </div>
                        </div>

                        <!-- Bouton -->
                        <button type="submit" :disabled="loading"
                            class="w-full bg-soboa-orange hover:bg-soboa-orange-dark disabled:bg-gray-400 text-black font-bold py-4 px-6 rounded-xl shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2 mb-4">
                            <span x-show="!loading" class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                Recevoir par SMS
                            </span>
                            <span x-show="loading" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Envoi en cours...
                            </span>
                        </button>
                    </form>

                    <!-- Retour connexion -->
                    <div class="text-center pt-4 border-t border-gray-100">
                        <a href="/login" class="text-soboa-blue hover:underline font-medium flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Retour à la connexion
                        </a>
                    </div>
                </div>

                <!-- Confirmation d'envoi SMS (étape 2) -->
                <div x-show="smsSent" x-cloak>
                    <!-- Succès animé -->
                    <div class="text-center mb-6">
                        <div class="w-24 h-24 bg-green-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <svg class="w-14 h-14 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">SMS envoyé ! </h2>
                        <p class="text-gray-600" x-text="successMessage"></p>
                    </div>

                    <!-- Carte info -->
                    <div class="bg-gradient-to-br from-soboa-blue to-blue-800 rounded-2xl p-6 text-white mb-6">
                        <div class="text-center mb-4">
                            <img src="/images/logoSOBOA.png.webp" alt="SOBOA" class="w-12 h-12 object-contain mx-auto rounded-full bg-white p-1 mb-2">
                            <p class="text-blue-200 text-sm">Message envoyé à</p>
                            <p class="text-xl font-bold" x-text="userName"></p>
                        </div>

                        <div class="bg-white/10 rounded-xl p-4 text-center">
                            <p class="text-blue-200 text-sm mb-1">Nouveau mot de passe envoyé par SMS</p>
                            <div class="flex items-center justify-center gap-2">
                                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <span class="text-lg font-bold">Vérifiez vos SMS</span>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-xl mb-6">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div class="text-sm">
                                <p class="font-bold mb-1">Conseils :</p>
                                <ul class="list-disc list-inside space-y-1 text-yellow-700">
                                    <li>Vérifiez votre boîte SMS</li>
                                    <li>Le message vient de "SOBOA FOOT TIME"</li>
                                    <li>Conservez ce mot de passe en lieu sûr</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Pas reçu ? -->
                    <div class="text-center text-sm text-gray-500 mb-6">
                        <p>Pas reçu de SMS ?</p>
                        <button @click="smsSent = false; error = ''" class="text-soboa-blue hover:underline font-medium">
                            Réessayer avec un autre numéro
                        </button>
                    </div>

                    <!-- Bouton connexion -->
                    <a href="/login"
                        class="w-full bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-4 px-6 rounded-xl shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Se connecter maintenant
                    </a>
                </div>

            </div>
        </div>
    </div>

    <script>
        function forgotPasswordForm() {
            return {
                phone: '',
                loading: false,
                error: '',
                smsSent: false,
                successMessage: '',
                userName: '',
                countryCode: '+221',

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
                    return phone.replace(/\D/g, '');
                },

                isValidPhone() {
                    const phoneDigits = this.formatPhoneNumber(this.phone);
                    return phoneDigits.length === 9 && phoneDigits.startsWith('7');
                },

                async resetPassword() {
                    if (!this.phone.trim()) {
                        this.error = 'Veuillez entrer votre numéro de téléphone.';
                        return;
                    }

                    if (!this.isValidPhone()) {
                        this.error = 'Le numéro doit contenir 9 chiffres commençant par 7 (ex: 77 123 45 67).';
                        return;
                    }

                    this.loading = true;
                    this.error = '';

                    try {
                        const response = await fetch('/auth/reset-password', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                phone: this.fullPhone
                            })
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.smsSent = true;
                            this.successMessage = data.message;
                            this.userName = data.user_name;
                        } else {
                            this.error = data.message || 'Une erreur est survenue.';
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
