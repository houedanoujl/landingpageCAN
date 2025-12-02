<x-layouts.app title="Connexion">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-soboa-blue">Bienvenue</h1>
                <p class="text-gray-600 mt-2">Connectez-vous pour faire vos pronostics</p>
            </div>

            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            @endif

            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6" role="alert">
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            @endif

            <div id="alert-container"></div>

            <!-- Étape 1: Numéro de téléphone -->
            <div id="step-phone">
                <form id="phone-form" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Votre nom complet
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               placeholder="Jean Kouassi"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-orange focus:border-soboa-orange"
                               required>
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Numéro de téléphone
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">🇨🇮 +225</span>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   placeholder="07 XX XX XX XX"
                                   class="w-full pl-20 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-orange focus:border-soboa-orange"
                                   required>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Un code de vérification vous sera envoyé par SMS</p>
                    </div>
                    
                    <!-- reCAPTCHA container -->
                    <div id="recaptcha-container"></div>
                    
                    <button type="submit" 
                            id="send-otp-btn"
                            class="w-full bg-soboa-blue hover:bg-blue-800 text-white font-bold py-3 px-4 rounded-lg shadow transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <span id="send-btn-text">Recevoir le code</span>
                    </button>
                </form>
            </div>

            <!-- Étape 2: Vérification OTP -->
            <div id="step-otp" style="display: none;">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-soboa-blue/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-soboa-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-600">Entrez le code reçu par SMS</p>
                    <p class="text-sm text-soboa-orange font-medium" id="phone-display"></p>
                </div>

                <form id="otp-form" class="space-y-6">
                    <div>
                        <input type="text" 
                               id="otp-code" 
                               placeholder="000000"
                               maxlength="6"
                               pattern="[0-9]{6}"
                               inputmode="numeric"
                               class="w-full px-4 py-4 text-center text-3xl font-bold tracking-[0.5em] border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-orange focus:border-soboa-orange"
                               required>
                    </div>
                    
                    <button type="submit" 
                            id="verify-btn"
                            class="w-full bg-soboa-orange hover:bg-orange-600 text-white font-bold py-3 px-4 rounded-lg shadow transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span id="verify-btn-text">Vérifier</span>
                    </button>
                </form>

                <div class="mt-4 text-center">
                    <button type="button" id="back-btn" class="text-soboa-blue hover:underline text-sm">
                        ← Modifier le numéro
                    </button>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                <p class="text-sm text-gray-600">
                    En vous connectant, vous acceptez nos 
                    <a href="#" class="text-soboa-orange hover:underline">conditions d'utilisation</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    
    <script>
        // Configuration Firebase (à remplacer par vos valeurs)
        const firebaseConfig = {
            apiKey: "{{ config('services.firebase.api_key') }}",
            authDomain: "{{ config('services.firebase.project_id') }}.firebaseapp.com",
            projectId: "{{ config('services.firebase.project_id') }}",
        };

        // Initialiser Firebase
        firebase.initializeApp(firebaseConfig);
        
        let confirmationResult = null;
        let recaptchaVerifier = null;
        let userName = '';
        let userPhone = '';

        // Initialiser reCAPTCHA
        function initRecaptcha() {
            recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
                'size': 'invisible',
                'callback': (response) => {
                    // reCAPTCHA résolu
                }
            });
        }

        // Afficher une alerte
        function showAlert(message, type = 'error') {
            const container = document.getElementById('alert-container');
            const bgColor = type === 'error' ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700';
            container.innerHTML = `<div class="${bgColor} px-4 py-3 rounded-lg mb-6 border" role="alert"><span class="font-medium">${message}</span></div>`;
            setTimeout(() => container.innerHTML = '', 5000);
        }

        // Formater le numéro de téléphone
        function formatPhone(phone) {
            // Supprimer tout sauf les chiffres
            phone = phone.replace(/\D/g, '');
            // Ajouter le préfixe +225 si nécessaire
            if (phone.startsWith('0')) {
                phone = phone.substring(1);
            }
            if (!phone.startsWith('225')) {
                phone = '225' + phone;
            }
            return '+' + phone;
        }

        // Envoyer le code OTP
        document.getElementById('phone-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            userName = document.getElementById('name').value.trim();
            const phoneInput = document.getElementById('phone').value.trim();
            userPhone = formatPhone(phoneInput);
            
            if (!userName) {
                showAlert('Veuillez entrer votre nom');
                return;
            }
            
            if (phoneInput.length < 8) {
                showAlert('Numéro de téléphone invalide');
                return;
            }

            const btn = document.getElementById('send-otp-btn');
            const btnText = document.getElementById('send-btn-text');
            btn.disabled = true;
            btnText.textContent = 'Envoi en cours...';

            try {
                if (!recaptchaVerifier) {
                    initRecaptcha();
                }
                
                confirmationResult = await firebase.auth().signInWithPhoneNumber(userPhone, recaptchaVerifier);
                
                // Passer à l'étape OTP
                document.getElementById('step-phone').style.display = 'none';
                document.getElementById('step-otp').style.display = 'block';
                document.getElementById('phone-display').textContent = userPhone;
                document.getElementById('otp-code').focus();
                
            } catch (error) {
                console.error('Erreur Firebase:', error);
                let errorMessage = 'Erreur lors de l\'envoi du code.';
                
                if (error.code === 'auth/invalid-phone-number') {
                    errorMessage = 'Numéro de téléphone invalide.';
                } else if (error.code === 'auth/too-many-requests') {
                    errorMessage = 'Trop de tentatives. Réessayez plus tard.';
                } else if (error.code === 'auth/quota-exceeded') {
                    errorMessage = 'Quota SMS dépassé. Contactez l\'administrateur.';
                }
                
                showAlert(errorMessage);
                
                // Réinitialiser le reCAPTCHA
                if (recaptchaVerifier) {
                    recaptchaVerifier.clear();
                    recaptchaVerifier = null;
                }
            } finally {
                btn.disabled = false;
                btnText.textContent = 'Recevoir le code';
            }
        });

        // Vérifier le code OTP
        document.getElementById('otp-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const code = document.getElementById('otp-code').value.trim();
            
            if (code.length !== 6) {
                showAlert('Le code doit contenir 6 chiffres');
                return;
            }

            const btn = document.getElementById('verify-btn');
            const btnText = document.getElementById('verify-btn-text');
            btn.disabled = true;
            btnText.textContent = 'Vérification...';

            try {
                const result = await confirmationResult.confirm(code);
                const user = result.user;
                const idToken = await user.getIdToken();
                
                // Envoyer au backend Laravel
                const response = await fetch('/auth/firebase-callback', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        firebase_token: idToken,
                        phone: userPhone,
                        name: userName,
                        firebase_uid: user.uid
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = data.redirect || '/matches';
                } else {
                    showAlert(data.message || 'Erreur lors de la connexion');
                }
                
            } catch (error) {
                console.error('Erreur vérification:', error);
                let errorMessage = 'Code incorrect ou expiré.';
                
                if (error.code === 'auth/invalid-verification-code') {
                    errorMessage = 'Code de vérification incorrect.';
                } else if (error.code === 'auth/code-expired') {
                    errorMessage = 'Le code a expiré. Veuillez en demander un nouveau.';
                }
                
                showAlert(errorMessage);
            } finally {
                btn.disabled = false;
                btnText.textContent = 'Vérifier';
            }
        });

        // Bouton retour
        document.getElementById('back-btn').addEventListener('click', () => {
            document.getElementById('step-otp').style.display = 'none';
            document.getElementById('step-phone').style.display = 'block';
            document.getElementById('otp-code').value = '';
            
            // Réinitialiser le reCAPTCHA
            if (recaptchaVerifier) {
                recaptchaVerifier.clear();
                recaptchaVerifier = null;
            }
        });

        // Initialiser au chargement
        document.addEventListener('DOMContentLoaded', () => {
            initRecaptcha();
        });
    </script>
</x-layouts.app>
