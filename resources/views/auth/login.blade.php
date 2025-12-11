<x-layouts.app title="Connexion">
    <div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full">
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div
                        class="w-20 h-20 bg-gradient-to-br from-soboa-orange to-orange-400 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <span class="text-4xl">⚽</span>
                    </div>
                    <h1 class="text-3xl font-black text-soboa-blue">Bienvenue</h1>
                    <p class="text-gray-600 mt-2">Connectez-vous pour faire vos pronostics CAN 2025</p>
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

                <!-- Step 1: Phone Number with Country Selector -->
                <div id="step-phone">
                    <form id="phone-form" class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Votre nom complet
                            </label>
                            <input type="text" id="name" name="name" placeholder="Jean Kouassi"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-soboa-orange focus:border-soboa-orange transition-colors font-medium"
                                required>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                Numéro de téléphone
                            </label>

                            <!-- Country Selector -->
                            <div class="flex gap-2 mb-2">
                                <select id="country-code"
                                    class="px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-soboa-orange focus:border-soboa-orange bg-white font-semibold">
                                    <option value="+225">🇨🇮 Côte d'Ivoire (+225)</option>
                                    <option value="+221">🇸🇳 Sénégal (+221)</option>
                                </select>
                            </div>

                            <input type="tel" id="phone" name="phone" placeholder="07 XX XX XX XX"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-soboa-orange focus:border-soboa-orange transition-colors font-medium"
                                required>
                            <p class="text-xs text-gray-500 mt-2">Un code de vérification vous sera envoyé par SMS</p>
                        </div>

                        <!-- reCAPTCHA container -->
                        <div id="recaptcha-container"></div>

                        <button type="submit" id="send-otp-btn"
                            class="w-full bg-soboa-blue hover:bg-soboa-blue-dark text-white font-bold py-4 px-4 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span id="send-btn-text">Recevoir le code</span>
                        </button>
                    </form>
                </div>

                <!-- Step 2: OTP Verification -->
                <div id="step-otp" style="display: none;">
                    <div class="text-center mb-6">
                        <div
                            class="w-16 h-16 bg-soboa-blue/10 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-soboa-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                </path>
                            </svg>
                        </div>
                        <p class="text-gray-600">Entrez le code reçu par SMS</p>
                        <p class="text-sm text-soboa-orange font-bold mt-1" id="phone-display"></p>
                    </div>

                    <form id="otp-form" class="space-y-6">
                        <div>
                            <input type="text" id="otp-code" placeholder="000000" maxlength="6" pattern="[0-9]{6}"
                                inputmode="numeric"
                                class="w-full px-4 py-4 text-center text-3xl font-black tracking-[0.5em] border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-soboa-orange focus:border-soboa-orange"
                                required>
                        </div>

                        <button type="submit" id="verify-btn"
                            class="w-full bg-soboa-orange hover:bg-soboa-orange-dark text-white font-bold py-4 px-4 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span id="verify-btn-text">Vérifier</span>
                        </button>
                    </form>

                    <!-- Resend SMS Section -->
                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-500 mb-2">Vous n'avez pas reçu le code ?</p>
                        <button type="button" id="resend-btn" disabled
                            class="text-soboa-blue hover:text-soboa-orange font-semibold text-sm transition-colors disabled:text-gray-400 disabled:cursor-not-allowed">
                            <span id="resend-btn-text">Renvoyer le code dans <span id="countdown">60</span>s</span>
                        </button>
                    </div>

                    <div class="mt-4 text-center">
                        <button type="button" id="back-btn"
                            class="text-soboa-blue hover:underline text-sm font-semibold">
                            ← Modifier le numéro
                        </button>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                    <p class="text-sm text-gray-600">
                        En vous connectant, vous acceptez nos
                        <a href="/conditions" class="text-soboa-orange hover:underline font-semibold">conditions d'utilisation</a>
                    </p>
                    <p class="text-xs text-gray-400 mt-2">
                        🔞 Ce jeu est réservé aux personnes de 18 ans et plus
                    </p>
                </div>
            </div>

            <!-- Points Info -->
            <div class="mt-6 bg-soboa-blue rounded-xl p-4 text-white text-center">
                <p class="font-bold">🎮 Gagnez des points à chaque match!</p>
                <p class="text-sm text-white/80 mt-1">+1 pronostic • +3 bon vainqueur • +3 score exact</p>
            </div>
            
            <!-- Debug Panel (visible en mode développement) -->
            @if(config('app.debug'))
            <div class="mt-6" x-data="{ open: false }">
                <button @click="open = !open" class="w-full text-left bg-gray-800 text-white px-4 py-2 rounded-t-xl flex justify-between items-center text-sm">
                    <span>🔧 Debug Panel (Firebase Logs)</span>
                    <span x-text="open ? '▼' : '▶'"></span>
                </button>
                <div x-show="open" class="bg-gray-900 rounded-b-xl p-4 max-h-60 overflow-y-auto">
                    <div id="debug-panel-content" class="font-mono text-xs space-y-1">
                        <div class="text-gray-500">En attente des logs...</div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-700 flex gap-2">
                        <button onclick="document.getElementById('debug-panel-content').innerHTML = '<div class=\'text-gray-500\'>Logs effacés</div>'" 
                                class="text-xs bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded">
                            Effacer logs
                        </button>
                        <button onclick="copyLogs()" 
                                class="text-xs bg-blue-600 hover:bg-blue-500 text-white px-3 py-1 rounded">
                            Copier logs
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>

    <script>
        // ============================================
        // DEBUG MODE - Afficher les logs dans la console
        // ============================================
        const DEBUG = true;
        
        function log(type, message, data = null) {
            const timestamp = new Date().toLocaleTimeString();
            const prefix = `[${timestamp}] [Firebase Auth]`;
            
            if (DEBUG) {
                if (type === 'error') {
                    console.error(`${prefix} ❌ ${message}`, data || '');
                } else if (type === 'success') {
                    console.log(`${prefix} ✅ ${message}`, data || '');
                } else if (type === 'info') {
                    console.info(`${prefix} ℹ️ ${message}`, data || '');
                } else {
                    console.log(`${prefix} ${message}`, data || '');
                }
            }
            
            // Ajouter au panneau de debug visible
            addToDebugPanel(type, message, data);
        }
        
        function addToDebugPanel(type, message, data) {
            const panel = document.getElementById('debug-panel-content');
            if (!panel) return;
            
            const colors = {
                'error': 'text-red-600',
                'success': 'text-green-600',
                'info': 'text-blue-600',
                'default': 'text-gray-600'
            };
            
            const color = colors[type] || colors.default;
            const time = new Date().toLocaleTimeString();
            const dataStr = data ? `<br><small class="text-gray-400">${JSON.stringify(data, null, 2)}</small>` : '';
            
            panel.innerHTML = `<div class="${color} text-xs mb-1"><span class="text-gray-400">[${time}]</span> ${message}${dataStr}</div>` + panel.innerHTML;
        }

        // ============================================
        // NUMÉROS DE TEST FIREBASE
        // Ajoutez ici les mêmes numéros que dans Firebase Console
        // ============================================
        const TEST_PHONE_NUMBERS = {
            '+2250700000000': '123456',
            '+2250748348221': '123456',  // Numéro de test
            '+2250747970627': '123456',  // Jean Luc Houédanou
            '+221770000000': '123456',   // Sénégal test
        };
        
        function isTestPhoneNumber(phone) {
            return TEST_PHONE_NUMBERS.hasOwnProperty(phone);
        }
        
        function getTestCode(phone) {
            return TEST_PHONE_NUMBERS[phone] || null;
        }

        // Firebase Configuration
        const firebaseConfig = {
            apiKey: "{{ config('services.firebase.api_key') }}",
            authDomain: "{{ config('services.firebase.project_id') }}.firebaseapp.com",
            projectId: "{{ config('services.firebase.project_id') }}",
        };

        log('info', 'Configuration Firebase', { 
            projectId: firebaseConfig.projectId,
            authDomain: firebaseConfig.authDomain 
        });

        // Initialize Firebase
        try {
            firebase.initializeApp(firebaseConfig);
            log('success', 'Firebase initialisé avec succès');
        } catch (e) {
            log('error', 'Erreur initialisation Firebase', e.message);
        }
        
        let confirmationResult = null;
        let recaptchaVerifier = null;
        let userName = '';
        let userPhone = '';
        let countdownInterval = null;

        // Countdown timer for resend button - DEFINED FIRST
        function startResendCountdown() {
            const resendBtn = document.getElementById('resend-btn');
            const resendBtnText = document.getElementById('resend-btn-text');
            const countdownSpan = document.getElementById('countdown');
            let seconds = 60;
            
            resendBtn.disabled = true;
            resendBtnText.innerHTML = 'Renvoyer le code dans <span id="countdown">60</span>s';
            
            // Clear any existing interval
            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
            
            countdownInterval = setInterval(() => {
                seconds--;
                const newCountdownSpan = document.getElementById('countdown');
                if (newCountdownSpan) {
                    newCountdownSpan.textContent = seconds;
                }
                
                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    countdownInterval = null;
                    resendBtn.disabled = false;
                    resendBtnText.innerHTML = '🔄 Renvoyer le code';
                }
            }, 1000);
        }

        // Initialize reCAPTCHA
        function initRecaptcha() {
            log('info', 'Initialisation reCAPTCHA...');
            try {
                recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
                    'size': 'invisible',
                    'callback': (response) => {
                        log('success', 'reCAPTCHA résolu', { tokenLength: response?.length });
                    },
                    'expired-callback': () => {
                        log('error', 'reCAPTCHA expiré - rechargez la page');
                    }
                });
                log('success', 'reCAPTCHA initialisé');
            } catch (e) {
                log('error', 'Erreur initialisation reCAPTCHA', e.message);
            }
        }

        // Show alert
        function showAlert(message, type = 'error') {
            const container = document.getElementById('alert-container');
            const bgColor = type === 'error'
               ? 'bg-red-100 border-red-400 text-red-700'
               : 'bg-green-100 border-green-400 text-green-700';
            container.innerHTML = `<div class="${bgColor} px-4 py-3 rounded-lg mb-6 border" role="alert"><span class="font-medium">${message}</span></div>`;
            setTimeout(() => container.innerHTML = '', 5000);
        }
        
        // Afficher la bannière avec le code de test
        function showTestCodeBanner(code) {
            const container = document.getElementById('alert-container');
            container.innerHTML = `
                <div class="bg-yellow-100 border-2 border-yellow-400 text-yellow-800 px-4 py-4 rounded-lg mb-6" role="alert">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-2xl">🧪</span>
                        <span class="font-bold text-lg">MODE TEST</span>
                    </div>
                    <p class="text-sm mb-2">Numéro de test détecté. Votre code de vérification est :</p>
                    <div class="bg-white rounded-lg px-4 py-3 text-center">
                        <span class="text-3xl font-black tracking-widest text-yellow-700">${code}</span>
                    </div>
                    <p class="text-xs mt-2 text-yellow-600">⚠️ En production, le code sera envoyé par SMS</p>
                </div>
            `;
        }

        // Format phone number with selected country code
        function formatPhone(phone) {
            const countryCode = document.getElementById('country-code').value;
            // Remove all non-digits
            phone = phone.replace(/\D/g, '');
            
            // Pour la Côte d'Ivoire (+225):
            // Format depuis 2021: 10 chiffres commençant par 01, 05, 07, 27, etc.
            // Exemple: 0758585858 -> +2250758585858
            if (countryCode === '+225') {
                // Si le numéro ne commence pas par 0, l'ajouter
                if (!phone.startsWith('0')) {
                    phone = '0' + phone;
                }
            }
            
            // Pour le Sénégal (+221):
            // Format: 9 chiffres sans le 0
            // Exemple: 0771234567 -> +221771234567
            if (countryCode === '+221') {
                if (phone.startsWith('0')) {
                    phone = phone.substring(1);
                }
            }
            
            // Return with country code
            return countryCode + phone;
        }

        // Send OTP
        document.getElementById('phone-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            userName = document.getElementById('name').value.trim();
            const phoneInput = document.getElementById('phone').value.trim();

            if (!userName) {
                showAlert('Veuillez entrer votre nom');
                return;
            }

            // Remove all non-digits for validation
            const digitsOnly = phoneInput.replace(/\D/g, '');
            const countryCode = document.getElementById('country-code').value;

            // Validation selon le pays
            let minDigits, maxDigits, exampleNum;
            if (countryCode === '+225') {
                // Côte d'Ivoire: 10 chiffres avec le 0, ou 9 sans le 0
                // Le formatPhone() ajoutera le 0 si nécessaire
                minDigits = 9;
                maxDigits = 10;
                exampleNum = '07 58 58 58 58';
            } else {
                // Sénégal: 9 chiffres sans le 0, ou 10 avec le 0
                minDigits = 9;
                maxDigits = 10;
                exampleNum = '77 123 45 67';
            }

            if (digitsOnly.length < minDigits) {
                showAlert(`Numéro trop court. Entrez au moins ${minDigits} chiffres (ex: ${exampleNum})`);
                return;
            }
            
            if (digitsOnly.length > maxDigits) {
                showAlert(`Numéro trop long. Maximum ${maxDigits} chiffres.`);
                return;
            }

            userPhone = formatPhone(phoneInput);
            
            // Log for debugging
            log('info', 'Numéro saisi', { input: phoneInput, digitsOnly, length: digitsOnly.length });
            log('info', 'Numéro formaté', { formatted: userPhone, length: userPhone.length });

            // Validation finale du format E.164
            // CI: +225 + 10 chiffres = 14 caractères
            // SN: +221 + 9 chiffres = 13 caractères
            const expectedLength = countryCode === '+225' ? 14 : 13;
            if (userPhone.length !== expectedLength) {
                log('error', 'Longueur invalide', { actual: userPhone.length, expected: expectedLength });
                showAlert(`Format incorrect. Numéro attendu: ${countryCode} ${exampleNum}`);
                return;
            }
            
            log('success', 'Validation du numéro OK', { phone: userPhone, format: 'E.164' });
            
            // Vérifier si c'est un numéro de test
            const isTestNumber = isTestPhoneNumber(userPhone);
            if (isTestNumber) {
                log('info', '🧪 NUMÉRO DE TEST DÉTECTÉ', { phone: userPhone });
            }

            const btn = document.getElementById('send-otp-btn');
            const btnText = document.getElementById('send-btn-text');
            btn.disabled = true;
            btnText.textContent = 'Envoi en cours...';

            try {
                log('info', 'Vérification reCAPTCHA...');
                if (!recaptchaVerifier) {
                    initRecaptcha();
                }

                log('info', 'Appel Firebase signInWithPhoneNumber...', { phone: userPhone });
                confirmationResult = await firebase.auth().signInWithPhoneNumber(userPhone, recaptchaVerifier);
                log('success', '✅ SMS envoyé avec succès !', { verificationId: confirmationResult?.verificationId?.substring(0, 20) + '...' });

                // Switch to OTP step
                document.getElementById('step-phone').style.display = 'none';
                document.getElementById('step-otp').style.display = 'block';
                document.getElementById('phone-display').textContent = userPhone;
                
                // Si c'est un numéro de test, afficher le code automatiquement
                if (isTestNumber) {
                    const testCode = getTestCode(userPhone);
                    log('success', '🧪 CODE DE TEST', { code: testCode });
                    showTestCodeBanner(testCode);
                    // Pré-remplir le code de test
                    document.getElementById('otp-code').value = testCode;
                }
                
                document.getElementById('otp-code').focus();
                
                // Start countdown for resend button
                startResendCountdown();

            } catch (error) {
                log('error', 'Erreur Firebase', { 
                    code: error.code, 
                    message: error.message,
                    fullError: error.toString()
                });
                
                let errorMessage = 'Erreur lors de l\'envoi du code.';
                let debugInfo = '';

                if (error.code === 'auth/invalid-phone-number' || error.message.includes('TOO_SHORT')) {
                    errorMessage = 'Numéro de téléphone invalide. Vérifiez le format.';
                    debugInfo = 'Le numéro ne respecte pas le format E.164';
                } else if (error.code === 'auth/too-many-requests') {
                    errorMessage = 'Trop de tentatives. Réessayez dans quelques minutes.';
                    debugInfo = 'Rate limiting Firebase activé';
                } else if (error.code === 'auth/quota-exceeded') {
                    errorMessage = 'Quota SMS dépassé. Contactez l\'administrateur.';
                    debugInfo = 'Quota Firebase SMS épuisé';
                } else if (error.code === 'auth/operation-not-allowed') {
                    errorMessage = 'SMS non disponible pour cette région.';
                    debugInfo = 'La région (CI/SN) n\'est pas activée dans Firebase Console > Authentication > Settings > SMS region policy';
                } else if (error.code === 'auth/captcha-check-failed') {
                    errorMessage = 'Vérification de sécurité échouée. Rechargez la page.';
                    debugInfo = 'reCAPTCHA failed - vérifiez que localhost est dans les domaines autorisés';
                } else if (error.message.includes('reCAPTCHA')) {
                    errorMessage = 'Erreur de vérification. Rechargez la page.';
                    debugInfo = 'Problème reCAPTCHA';
                } else if (error.code === 'auth/missing-phone-number') {
                    errorMessage = 'Numéro de téléphone manquant.';
                    debugInfo = 'Le numéro n\'a pas été transmis correctement';
                } else if (error.code === 'auth/invalid-app-credential') {
                    errorMessage = 'Configuration Firebase invalide.';
                    debugInfo = 'Vérifiez API Key et Project ID dans .env';
                }
                
                log('error', `Diagnostic: ${debugInfo}`);
                showAlert(errorMessage);

                showAlert(errorMessage);

                // Reset reCAPTCHA
                if (recaptchaVerifier) {
                    recaptchaVerifier.clear();
                    recaptchaVerifier = null;
                }
            } finally {
                btn.disabled = false;
                btnText.textContent = 'Recevoir le code';
            }
        });

        // Verify OTP
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

                // Send to Laravel backend
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
                console.error('Verification Error:', error);
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

        // Back button
        document.getElementById('back-btn').addEventListener('click', () => {
            document.getElementById('step-otp').style.display = 'none';
            document.getElementById('step-phone').style.display = 'block';
            document.getElementById('otp-code').value = '';
            
            // Stop countdown
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }

            // Reset reCAPTCHA
            if (recaptchaVerifier) {
                recaptchaVerifier.clear();
                recaptchaVerifier = null;
            }
        });

        // Resend SMS button
        document.getElementById('resend-btn').addEventListener('click', async () => {
            const resendBtn = document.getElementById('resend-btn');
            const resendBtnText = document.getElementById('resend-btn-text');
            
            resendBtn.disabled = true;
            resendBtnText.innerHTML = '⏳ Envoi en cours...';
            
            try {
                log('info', 'Renvoi du SMS...');
                
                // Supprimer et recréer le container reCAPTCHA
                const oldContainer = document.getElementById('recaptcha-container');
                if (oldContainer) {
                    oldContainer.innerHTML = '';
                }
                
                // Reset reCAPTCHA verifier
                if (recaptchaVerifier) {
                    try {
                        recaptchaVerifier.clear();
                    } catch (e) {
                        log('info', 'reCAPTCHA déjà nettoyé');
                    }
                    recaptchaVerifier = null;
                }
                
                // Créer un nouveau reCAPTCHA
                recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
                    'size': 'invisible',
                    'callback': (response) => {
                        log('success', 'reCAPTCHA résolu (resend)');
                    }
                });
                
                // Wait a bit for reCAPTCHA to initialize
                await new Promise(resolve => setTimeout(resolve, 300));
                
                log('info', 'Appel Firebase pour renvoi...', { phone: userPhone });
                confirmationResult = await firebase.auth().signInWithPhoneNumber(userPhone, recaptchaVerifier);
                log('success', '✅ SMS renvoyé avec succès !');
                
                showAlert('✅ Un nouveau code a été envoyé !', 'success');
                
                // Restart countdown
                startResendCountdown();
                
            } catch (error) {
                log('error', 'Erreur renvoi SMS', { code: error.code, message: error.message });
                let errorMessage = 'Erreur lors du renvoi du code.';
                
                if (error.code === 'auth/too-many-requests') {
                    errorMessage = 'Trop de tentatives. Attendez quelques minutes.';
                } else if (error.code === 'auth/quota-exceeded') {
                    errorMessage = 'Limite de SMS atteinte. Réessayez plus tard.';
                }
                
                showAlert(errorMessage);
                resendBtn.disabled = false;
                resendBtnText.innerHTML = '🔄 Renvoyer le code';
            }
        });
        
        // Fonction pour copier les logs
        function copyLogs() {
            const panel = document.getElementById('debug-panel-content');
            if (panel) {
                const text = panel.innerText;
                navigator.clipboard.writeText(text).then(() => {
                    alert('Logs copiés dans le presse-papier !');
                });
            }
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            log('info', 'Page chargée, initialisation...');
            initRecaptcha();
        });
    </script>
</x-layouts.app>