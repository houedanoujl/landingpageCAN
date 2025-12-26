<x-layouts.app title="Connexion Administrateur"><x-layouts.app title="Connexion Administrateur"><x-layouts.app title="Connexion Administrateur">

    <div class="min-h-[calc(100vh-80px)] flex items-center justify-center px-4 py-8 bg-gradient-to-br from-gray-900 to-gray-800">

        <div class="w-full max-w-md">    <div class="min-h-[calc(100vh-80px)] flex items-center justify-center px-4 py-8 bg-gradient-to-br from-gray-900 to-gray-800">    <div class="min-h-[calc(100vh-80px)] flex items-center justify-center px-4 py-8 bg-gradient-to-br from-gray-900 to-gray-800">



            <!-- Logo et titre -->        <div class="w-full max-w-md">        <div class="w-full max-w-md">

            <div class="text-center mb-8">

                <div class="w-20 h-20 bg-red-600 rounded-full mx-auto mb-4 shadow-lg flex items-center justify-center">

                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"            <!-- Logo et titre -->            <!-- Logo et titre -->

                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">

                        </path>            <div class="text-center mb-8">            <div class="text-center mb-8">

                    </svg>

                </div>                <div class="w-20 h-20 bg-red-600 rounded-full mx-auto mb-4 shadow-lg flex items-center justify-center">                <div class="w-20 h-20 bg-red-600 rounded-full mx-auto mb-4 shadow-lg flex items-center justify-center">

                <h1 class="text-3xl font-black text-white">Administration</h1>

                <p class="text-gray-300 mt-2">Accès réservé aux administrateurs</p>                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">

            </div>

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"

            <!-- Formulaire -->

            <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8" x-data="{ showPassword: false }">                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">



                <!-- Messages d'erreur -->                        </path>                        </path>

                @if($errors->any())

                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">                    </svg>                    </svg>

                    <div class="flex items-center gap-2">

                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">                </div>                </div>

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"

                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>                <h1 class="text-3xl font-black text-white">Administration</h1>                <h1 class="text-3xl font-black text-white">Administration</h1>

                        </svg>

                        <span class="font-medium">{{ $errors->first() }}</span>                <p class="text-gray-300 mt-2">Accès réservé aux administrateurs</p>                <p class="text-gray-300 mt-2">Accès réservé aux administrateurs</p>

                    </div>

                </div>            </div>            </div>

                @endif



                @if(session('error'))

                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">            <!-- Formulaire -->            <!-- Formulaire -->

                    <div class="flex items-center gap-2">

                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">            <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">            <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8" x-data="adminLoginForm()">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"

                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>

                        </svg>

                        <span class="font-medium">{{ session('error') }}</span>                <!-- Messages d'erreur -->                <!-- Messages d'erreur -->

                    </div>

                </div>                @if($errors->any())                <div x-show="error" x-cloak

                @endif

                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">                    class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">

                <form action="{{ route('admin.auth.login') }}" method="POST">

                    @csrf                    <div class="flex items-center gap-2">                    <div class="flex items-center gap-2">



                    <!-- Username -->                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                    <div class="mb-6">

                        <label class="block text-sm font-bold text-gray-700 mb-2">Nom d'utilisateur</label>                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"

                        <div class="relative">

                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>

                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>                        </svg>                        </svg>

                                </svg>

                            </span>                        <span class="font-medium">{{ $errors->first() }}</span>                        <span x-text="error" class="font-medium"></span>

                            <input type="text" name="username" value="{{ old('username') }}" placeholder="Entrez votre nom d'utilisateur"

                                class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-red-600 focus:ring-0 text-lg"                    </div>                    </div>

                                required autofocus>

                        </div>                </div>                </div>

                    </div>

                @endif

                    <!-- Password -->

                    <div class="mb-6">                <!-- Étape 1: Téléphone -->

                        <label class="block text-sm font-bold text-gray-700 mb-2">Mot de passe</label>

                        <div class="relative">                @if(session('error'))                <div x-show="step === 1">

                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">

                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">                    <form @submit.prevent="sendOtp">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>

                                </svg>                    <div class="flex items-center gap-2">                        <!-- Téléphone -->

                            </span>

                            <input :type="showPassword ? 'text' : 'password'" name="password" placeholder="Entrez votre mot de passe"                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">                        <div class="mb-6">

                                class="w-full pl-12 pr-12 py-3 border-2 border-gray-200 rounded-xl focus:border-red-600 focus:ring-0 text-lg"

                                required>                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"                            <label class="block text-sm font-bold text-gray-700 mb-2">Numéro de téléphone administrateur</label>

                            <button type="button" @click="showPassword = !showPassword" 

                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>                            <div class="flex gap-2">

                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>                        </svg>                                <!-- Indicatif verrouillé Côte d'Ivoire -->

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>

                                </svg>                        <span class="font-medium">{{ session('error') }}</span>                                <div class="px-4 py-3 border-2 border-gray-300 bg-gray-100 rounded-xl text-sm font-bold text-gray-600 flex items-center">

                                <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>                    </div>                                    🇨🇮 +225

                                </svg>

                            </button>                </div>                                </div>

                        </div>

                    </div>                @endif                                <input type="tel" x-model="phone" placeholder="0X XX XX XX XX"



                    <!-- Avertissement -->                                    class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-red-600 focus:ring-0 text-lg"

                    <div class="mb-6 p-3 bg-red-50 border border-red-200 rounded-xl">

                        <p class="text-xs text-red-600 font-medium flex items-center gap-2">                <form action="{{ route('admin.auth.login') }}" method="POST">                                    required>

                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>                    @csrf                            </div>

                            </svg>

                            Accès réservé aux administrateurs autorisés uniquement                            <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">

                        </p>

                    </div>                    <!-- Username -->                                <svg class="w-4 h-4 text-green-500" viewBox="0 0 24 24" fill="currentColor">



                    <!-- Bouton -->                    <div class="mb-6">                                    <path

                    <button type="submit"

                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2">                        <label class="block text-sm font-bold text-gray-700 mb-2">Nom d'utilisateur</label>                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />

                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>                        <div class="relative">                                </svg>

                        </svg>

                        Se connecter                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">                                <span>Code d'accès via <strong>WhatsApp</strong></span>

                    </button>

                </form>                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">                            </p>



                <!-- Lien retour -->                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>                            <p class="text-xs text-red-600 mt-2 font-bold">

                <div class="mt-6 text-center">

                    <a href="/" class="text-gray-500 hover:text-gray-700 text-sm font-medium">                                </svg>                                ⚠️ Accès réservé au numéro administrateur autorisé uniquement

                        ← Retour au site

                    </a>                            </span>                            </p>

                </div>

            </div>                            <input type="text" name="username" value="{{ old('username') }}" placeholder="Entrez votre nom d'utilisateur"                        </div>



            <!-- Note de sécurité -->                                class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-red-600 focus:ring-0 text-lg"

            <p class="text-center text-gray-400 text-xs mt-6">

                🔒 Connexion sécurisée - Toutes les tentatives sont enregistrées                                required autofocus>                        <!-- Bouton -->

            </p>

        </div>                        </div>                        <button type="submit" :disabled="loading"

    </div>

                    </div>                            class="w-full bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white font-bold py-4 px-6 rounded-xl shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2">

    <style>

        [x-cloak] { display: none !important; }                            <span x-show="!loading">Recevoir le code admin</span>

    </style>

</x-layouts.app>                    <!-- Password -->                            <span x-show="loading" class="flex items-center gap-2">


                    <div class="mb-6">                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"

                        <label class="block text-sm font-bold text-gray-700 mb-2">Mot de passe</label>                                    viewBox="0 0 24 24">

                        <div class="relative" x-data="{ showPassword: false }">                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"

                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">                                        stroke-width="4"></circle>

                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">                                    <path class="opacity-75" fill="currentColor"

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">

                                </svg>                                    </path>

                            </span>                                </svg>

                            <input :type="showPassword ? 'text' : 'password'" name="password" placeholder="Entrez votre mot de passe"                                Envoi en cours...

                                class="w-full pl-12 pr-12 py-3 border-2 border-gray-200 rounded-xl focus:border-red-600 focus:ring-0 text-lg"                            </span>

                                required>                        </button>

                            <button type="button" @click="showPassword = !showPassword"                     </form>

                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">                </div>

                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>                <!-- Étape 2: Vérification du code -->

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>                <div x-show="step === 2" x-cloak>

                                </svg>                    <form @submit.prevent="verifyOtp">

                                <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">                        <div class="text-center mb-6">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>                            <div

                                </svg>                                class="w-16 h-16 bg-red-600 rounded-full mx-auto mb-4 flex items-center justify-center">

                            </button>                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        </div>                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"

                    </div>                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">

                                    </path>

                    <!-- Avertissement -->                                </svg>

                    <div class="mb-6 p-3 bg-red-50 border border-red-200 rounded-xl">                            </div>

                        <p class="text-xs text-red-600 font-medium flex items-center gap-2">                            <h2 class="text-xl font-bold text-gray-800">Code admin envoyé !</h2>

                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">                            <p class="text-gray-600 mt-1">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>                                Vérifiez vos messages WhatsApp au <span class="font-bold text-red-600"

                            </svg>                                    x-text="fullPhone"></span>

                            Accès réservé aux administrateurs autorisés uniquement                            </p>

                        </p>                        </div>

                    </div>

                        <!-- Champ code -->

                    <!-- Bouton -->                        <div class="mb-6">

                    <button type="submit"                            <label class="block text-sm font-bold text-gray-700 mb-2">Code de vérification</label>

                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2">                            <input type="text" x-model="code" placeholder="000000" maxlength="6"

                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">                                class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:border-red-600 focus:ring-0 text-2xl text-center tracking-[0.5em] font-bold"

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>                                required>

                        </svg>                        </div>

                        Se connecter

                    </button>                        <!-- Boutons -->

                </form>                        <button type="submit" :disabled="loading || code.length !== 6"

                            class="w-full bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white font-bold py-4 px-6 rounded-xl shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2 mb-4">

                <!-- Lien retour -->                            <span x-show="!loading">Vérifier et accéder</span>

                <div class="mt-6 text-center">                            <span x-show="loading" class="flex items-center gap-2">

                    <a href="/" class="text-gray-500 hover:text-gray-700 text-sm font-medium">                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"

                        ← Retour au site                                    viewBox="0 0 24 24">

                    </a>                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"

                </div>                                        stroke-width="4"></circle>

            </div>                                    <path class="opacity-75" fill="currentColor"

                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">

            <!-- Note de sécurité -->                                    </path>

            <p class="text-center text-gray-400 text-xs mt-6">                                </svg>

                🔒 Connexion sécurisée - Toutes les tentatives sont enregistrées                                Vérification...

            </p>                            </span>

        </div>                        </button>

    </div>

                        <!-- Renvoyer le code -->

    <style>                        <div class="text-center">

        [x-cloak] { display: none !important; }                            <p class="text-sm text-gray-500 mb-2">Vous n'avez pas reçu le code ?</p>

    </style>                            <button type="button" @click="resendOtp" :disabled="resendCooldown > 0"

</x-layouts.app>                                class="text-red-600 hover:underline font-bold disabled:text-gray-400">

                                <span x-show="resendCooldown === 0">Renvoyer le code</span>
                                <span x-show="resendCooldown > 0">Réessayer dans <span
                                        x-text="resendCooldown"></span>s</span>
                            </button>
                        </div>

                        <!-- Changer de numéro -->
                        <div class="text-center mt-4 pt-4 border-t">
                            <button type="button" @click="step = 1; code = ''; error = ''; resendCooldown = 0"
                                class="text-sm text-gray-500 hover:text-gray-700">
                                ← Modifier le numéro
                            </button>
                        </div>
                    </form>
                </div>

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
                step: 1,
                phone: '',
                countryCode: '+225', // Verrouillé sur Côte d'Ivoire
                code: '',
                loading: false,
                error: '',
                resendCooldown: 0,

                get fullPhone() {
                    return this.countryCode + this.formatPhoneNumber(this.phone);
                },

                formatPhoneNumber(phone) {
                    // Supprimer tout sauf les chiffres
                    let digits = phone.replace(/\D/g, '');

                    // Côte d'Ivoire: 10 chiffres avec le 0 initial
                    if (!digits.startsWith('0') && digits.length === 9) {
                        digits = '0' + digits;
                    }

                    return digits;
                },

                async sendOtp() {
                    if (!this.phone.trim()) {
                        this.error = 'Veuillez entrer votre numéro de téléphone.';
                        return;
                    }

                    this.loading = true;
                    this.error = '';

                    console.log('=== ENVOI OTP ADMIN ===');
                    console.log('Numéro formaté:', this.fullPhone);

                    try {
                        const response = await fetch('/admin/auth/send-otp', {
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

                        console.log('=== RÉPONSE SERVEUR ADMIN ===');
                        console.log('Status HTTP:', response.status);
                        console.log('Data:', data);

                        if (data.success) {
                            this.step = 2;
                            this.startResendCooldown();
                            console.log('Code admin envoyé avec succès !');
                        } else {
                            this.error = data.message || 'Erreur lors de l\'envoi du code.';
                            if (data.error) {
                                console.error('Erreur:', data.error);
                            }
                        }
                    } catch (err) {
                        console.error('Erreur réseau:', err);
                        this.error = 'Erreur de connexion. Veuillez réessayer.';
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
                        const response = await fetch('/admin/auth/verify-otp', {
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
                            // Redirection vers l'admin
                            window.location.href = data.redirect || '/admin';
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
                        const response = await fetch('/admin/auth/send-otp', {
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

                        if (data.success) {
                            this.startResendCooldown();
                        } else {
                            this.error = data.message || 'Erreur lors du renvoi.';
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
