<x-layouts.app title="Connexion">
    <div class="min-h-[calc(100vh-80px)] flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md">

            <!-- Logo et titre -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-white rounded-full mx-auto mb-4 shadow-lg flex items-center justify-center">
                    <img src="/images/logoGazelle.jpeg" alt="SOBOA" class="w-16 h-16 object-contain">
                </div>
                <h1 class="text-3xl font-black text-soboa-blue">Connexion</h1>
                <p class="text-gray-600 mt-2">Entrez votre numéro pour jouer</p>
            </div>

            <!-- Formulaire -->
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

                <!-- Étape 1: Nom et téléphone -->
                <div x-show="step === 1">
                    <form @submit.prevent="sendOtp">
                        <!-- Nom complet -->
                        <div class="mb-5">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Votre nom complet</label>
                            <input type="text" x-model="name" placeholder="Jean Dupont"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-soboa-orange focus:ring-0 text-lg"
                                required>
                        </div>

                        <!-- Téléphone -->
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Numéro de téléphone</label>
                            <div class="flex gap-2">
                                <!-- Sélecteur de pays -->
                                <select x-model="countryCode"
                                    class="px-3 py-3 border-2 border-gray-200 bg-white rounded-xl text-sm font-bold text-gray-700 focus:border-soboa-orange focus:ring-0 cursor-pointer">
                                    <option value="+225">🇨🇮 +225</option>
                                    <option value="+221">🇸🇳 +221</option>
                                    <option value="+33">🇫🇷 +33</option>
                                </select>
                                <input type="tel" x-model="phone" :placeholder="getPlaceholder()"
                                    class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-soboa-orange focus:ring-0 text-lg"
                                    required>
                            </div>
                            <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                <svg class="w-4 h-4 text-green-500" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                </svg>
                                <span>Un code vous sera envoyé sur <strong>WhatsApp</strong></span>
                            </p>
                        </div>

                        <!-- CAPTCHA Mathématique -->
                        <div class="mb-6 p-4 bg-blue-50 rounded-xl border-2 border-blue-200">
                            <label class="block text-sm font-bold text-gray-700 mb-3">Sécurité: Résolvez cette
                                opération</label>
                            <div class="flex items-center gap-3 mb-4">
                                <div class="text-lg font-bold text-gray-800">
                                    <span x-text="captchaNum1"></span>
                                    <span x-text="captchaOp" class="mx-2"></span>
                                    <span x-text="captchaNum2"></span>
                                    <span class="mx-2">=</span>
                                    <span class="text-blue-600">?</span>
                                </div>
                            </div>
                            <input type="number" x-model="captchaAnswer" placeholder="Votre réponse"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-soboa-orange focus:ring-0 text-lg"
                                required>
                        </div>

                        <!-- Erreur CAPTCHA -->
                        <div x-show="captchaError" x-cloak
                            class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Réponse CAPTCHA incorrecte</span>
                            </div>
                        </div>

                        <!-- Bouton -->
                        <button type="submit" :disabled="loading"
                            class="w-full bg-soboa-orange hover:bg-soboa-orange-dark disabled:bg-gray-400 text-white font-bold py-4 px-6 rounded-xl shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2">
                            <span x-show="!loading">Recevoir le code</span>
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
                </div>

                <!-- Étape 2: Vérification du code -->
                <div x-show="step === 2" x-cloak>
                    <form @submit.prevent="verifyOtp">
                        <div class="text-center mb-6">
                            <div
                                class="w-16 h-16 bg-green-500 rounded-full mx-auto mb-4 flex items-center justify-center">
                                <svg class="w-10 h-10 text-white" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Code envoyé sur WhatsApp !</h2>
                            <p class="text-gray-600 mt-1">
                                Vérifiez vos messages WhatsApp au <span class="font-bold text-green-600"
                                    x-text="fullPhone"></span>
                            </p>
                        </div>

                        <!-- Champ code -->
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Code de vérification</label>
                            <input type="text" x-model="code" placeholder="000000" maxlength="6"
                                class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:ring-0 text-2xl text-center tracking-[0.5em] font-bold"
                                required>
                        </div>

                        <!-- Boutons -->
                        <button type="submit" :disabled="loading || code.length !== 6"
                            class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-bold py-4 px-6 rounded-xl shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2 mb-4">
                            <span x-show="!loading">Vérifier le code</span>
                            <span x-show="loading" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Vérification...
                            </span>
                        </button>

                        <!-- Renvoyer le code -->
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-2">Vous n'avez pas reçu le code ?</p>
                            <button type="button" @click="resendOtp" :disabled="resendCooldown > 0"
                                class="text-soboa-orange hover:underline font-bold disabled:text-gray-400">
                                <span x-show="resendCooldown === 0">Renvoyer le code</span>
                                <span x-show="resendCooldown > 0">Réessayer dans <span
                                        x-text="resendCooldown"></span>s</span>
                            </button>
                        </div>

                        <!-- Changer de numéro -->
                        <div class="text-center mt-4 pt-4 border-t">
                            <button type="button" @click="step = 1; code = ''; error = ''"
                                class="text-sm text-gray-500 hover:text-gray-700">
                                ← Modifier le numéro
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Conditions -->
                <p class="text-xs text-gray-500 text-center mt-6">
                    En vous connectant, vous acceptez nos
                    <a href="/conditions" class="text-soboa-blue hover:underline">conditions d'utilisation</a>
                </p>
            </div>

            <!-- Infos supplémentaires -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    🔞 Ce jeu est réservé aux personnes de 18 ans et plus
                </p>
                <p class="text-xs text-gray-500 mt-2">
                    🎮 Gagnez des points à chaque match!<br>
                    <span class="text-soboa-orange font-bold">+1</span> pronostic •
                    <span class="text-soboa-orange font-bold">+4</span> bon vainqueur •
                    <span class="text-soboa-orange font-bold">+7</span> score exact
                </p>
            </div>
        </div>
    </div>

    <script>
        function loginForm() {
            return {
                step: 1,
                name: '',
                phone: '',
                countryCode: '+225', // Côte d'Ivoire par défaut
                code: '',
                loading: false,
                error: '',
                captchaError: false,
                captchaNum1: 0,
                captchaNum2: 0,
                captchaOp: '+',
                captchaAnswer: '',
                captchaCorrectAnswer: 0,
                resendCooldown: 0,

                init() {
                    const savedName = localStorage.getItem('user_name');
                    if (savedName) {
                        this.name = savedName;
                    }
                    this.generateCaptcha();
                },

                generateCaptcha() {
                    this.captchaNum1 = Math.floor(Math.random() * 10) + 1;
                    this.captchaNum2 = Math.floor(Math.random() * 10) + 1;
                    this.captchaOp = '+';
                    this.captchaAnswer = '';
                    this.captchaCorrectAnswer = this.captchaNum1 + this.captchaNum2;
                },

                get fullPhone() {
                    return this.countryCode + this.formatPhoneNumber(this.phone);
                },

                getPlaceholder() {
                    switch (this.countryCode) {
                        case '+225': return '07 XX XX XX XX';
                        case '+221': return '77 XXX XX XX';
                        case '+33': return '06 XX XX XX XX';
                        default: return '07 XX XX XX XX';
                    }
                },

                formatPhoneNumber(phone) {
                    // Supprimer tout sauf les chiffres
                    let digits = phone.replace(/\D/g, '');

                    // France (+33) et Sénégal (+221): Retirer le 0 initial
                    if (this.countryCode === '+33' || this.countryCode === '+221') {
                        if (digits.startsWith('0')) {
                            digits = digits.substring(1);
                        }
                    }

                    // Côte d'Ivoire (+225): GARDER le 0 initial car format 10 chiffres (07...)
                    // Si l'utilisateur a oublié le 0, on pourrait potentiellement l'ajouter, 
                    // mais ici on laisse tel quel pour validation backend.

                    return digits;
                },

                async sendOtp() {
                    if (!this.name.trim() || !this.phone.trim()) {
                        this.error = 'Veuillez remplir tous les champs.';
                        return;
                    }

                    // Vérifier le CAPTCHA mathématique
                    if (!this.captchaAnswer || parseInt(this.captchaAnswer) !== this.captchaCorrectAnswer) {
                        this.captchaError = true;
                        this.error = '';
                        this.generateCaptcha();
                        return;
                    }
                    this.captchaError = false;

                    this.loading = true;
                    this.error = '';

                    console.log('=== ENVOI OTP ===');
                    console.log('Numéro original:', this.phone);
                    console.log('Code pays:', this.countryCode);
                    console.log('Numéro formaté:', this.fullPhone);

                    try {
                        const response = await fetch('/auth/send-otp', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                name: this.name,
                                phone: this.fullPhone
                            })
                        });

                        const data = await response.json();

                        console.log('=== RÉPONSE SERVEUR ===');
                        console.log('Status HTTP:', response.status);
                        console.log('Data:', data);

                        if (data.success) {
                            this.step = 2;
                            this.startResendCooldown();
                            this.generateCaptcha();
                            console.log('WhatsApp OTP envoyé avec succès !');
                        } else {
                            this.error = data.message || 'Erreur lors de l\'envoi du code.';
                            this.generateCaptcha();
                            if (data.error) {
                                console.error('Erreur WhatsApp:', data.error);
                            }
                        }
                    } catch (err) {
                        console.error('Erreur réseau:', err);
                        this.error = 'Erreur de connexion. Veuillez réessayer.';
                        this.generateCaptcha();
                    } finally {
                        this.loading = false;
                    }
                },

                async verifyOtp() {
                    if (this.code.length !== 6) {
                        this.error = 'Le code doit contenir 6 chiffres.';
                        return;
                    }

                    this.loading = true;
                    this.error = '';

                    try {
                        const response = await fetch('/auth/verify-otp', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                phone: this.fullPhone,
                                code: this.code
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Sauvegarder le nom pour la prochaine fois
                            localStorage.setItem('user_name', this.name);

                            // Redirection après succès
                            window.location.href = data.redirect || '/matches';
                        } else {
                            this.error = data.message || 'Code incorrect.';
                        }
                    } catch (err) {
                        console.error('Erreur:', err);
                        this.error = 'Erreur de connexion. Veuillez réessayer.';
                    } finally {
                        this.loading = false;
                    }
                },

                async resendOtp() {
                    if (this.resendCooldown > 0) return;

                    this.loading = true;
                    this.error = '';

                    try {
                        const response = await fetch('/auth/send-otp', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                name: this.name,
                                phone: this.fullPhone
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.startResendCooldown();
                            this.generateCaptcha();
                        } else {
                            this.error = data.message || 'Erreur lors du renvoi.';
                            this.generateCaptcha();
                        }
                    } catch (err) {
                        this.error = 'Erreur de connexion.';
                    } finally {
                        this.loading = false;
                    }
                },

                startResendCooldown() {
                    this.resendCooldown = 60;
                    const interval = setInterval(() => {
                        this.resendCooldown--;
                        if (this.resendCooldown <= 0) {
                            clearInterval(interval);
                        }
                    }, 1000);
                }
            };
        }
    </script>
</x-layouts.app>