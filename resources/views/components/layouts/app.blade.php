<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOBOA CAN 2025 | {{ $title ?? 'Accueil' }}</title>
    <meta name="description"
        content="Pronostiquez les matchs de la CAN 2025, gagnez des points et devenez le meilleur pronostiqueur!">

    <!-- PWA Meta Tags -->
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#003399">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Google Fonts - Montserrat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'montserrat': ['Montserrat', 'sans-serif'],
                    },
                    colors: {
                        soboa: {
                            blue: '#003399',
                            'blue-dark': '#002266',
                            'blue-light': '#0044cc',
                            orange: '#FF6600',
                            'orange-light': '#FF8533',
                            'orange-dark': '#CC5200',
                        },
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'bounce-slow': 'bounce 2s infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-20px)' },
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        * {
            font-family: 'Montserrat', sans-serif;
        }

        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23003399' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .glass-dark {
            background: rgba(0, 51, 153, 0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .gradient-text {
            background: linear-gradient(135deg, #FF6600 0%, #FF8533 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-gradient {
            background: linear-gradient(135deg, #003399 0%, #002266 50%, #003399 100%);
        }

        .orange-glow {
            box-shadow: 0 0 40px rgba(255, 102, 0, 0.3);
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-50 bg-pattern min-h-screen flex flex-col" x-data="{ mobileMenuOpen: false }">

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 glass-dark shadow-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 md:h-20">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-3 group">
                    <div
                        class="w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform overflow-hidden bg-white">
                        <img src="/images/soboa.png" alt="SOBOA" class="w-full h-full object-contain p-1">
                    </div>
                    <div class="text-white">
                        <span class="font-black text-lg md:text-xl tracking-tight">SOBOA</span>
                        <span class="text-soboa-orange font-bold text-xs md:text-sm block -mt-1">CAN 2025</span>
                    </div>
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center gap-1">
                    <a href="/"
                        class="px-4 py-2 text-white/90 hover:text-white hover:bg-white/10 rounded-lg font-semibold text-sm transition-all">Accueil</a>
                    <a href="/matches"
                        class="px-4 py-2 text-white/90 hover:text-white hover:bg-white/10 rounded-lg font-semibold text-sm transition-all">Pronostics</a>
                    <a href="/leaderboard"
                        class="px-4 py-2 text-white/90 hover:text-white hover:bg-white/10 rounded-lg font-semibold text-sm transition-all">Classement</a>
                    <a href="/map"
                        class="px-4 py-2 text-white/90 hover:text-white hover:bg-white/10 rounded-lg font-semibold text-sm transition-all">Lieux</a>
                </div>

                <!-- User Actions -->
                <div class="flex items-center gap-3">
                    @if(session('user_id'))
                        <div class="hidden md:flex items-center gap-3">
                            <a href="/dashboard" class="text-right hover:opacity-80 transition-opacity">
                                <span
                                    class="text-soboa-orange font-bold text-sm block">{{ session('predictor_name') }}</span>
                                <span class="text-white/60 text-xs">{{ session('user_points', 0) }} pts</span>
                            </a>
                            <a href="/logout" class="text-white/70 hover:text-white text-xs font-medium">Déconnexion</a>
                        </div>
                    @else
                        <a href="/login"
                            class="hidden md:inline-flex bg-soboa-orange hover:bg-soboa-orange-dark text-white font-bold py-2.5 px-6 rounded-full shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                            Jouer maintenant
                        </a>
                    @endif

                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="md:hidden p-2 text-white hover:bg-white/10 rounded-lg transition-colors">
                        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-4" x-cloak
            class="md:hidden glass-dark border-t border-white/10">
            <div class="px-4 py-4 space-y-2">
                <a href="/"
                    class="block px-4 py-3 text-white hover:bg-white/10 rounded-lg font-semibold transition-colors">🏠
                    Accueil</a>
                <a href="/matches"
                    class="block px-4 py-3 text-white hover:bg-white/10 rounded-lg font-semibold transition-colors">⚽
                    Pronostics</a>
                <a href="/leaderboard"
                    class="block px-4 py-3 text-white hover:bg-white/10 rounded-lg font-semibold transition-colors">🏆
                    Classement</a>
                <a href="/map"
                    class="block px-4 py-3 text-white hover:bg-white/10 rounded-lg font-semibold transition-colors">📍
                    Lieux partenaires</a>

                @if(session('user_id'))
                    <div class="pt-4 border-t border-white/10">
                        <a href="/dashboard"
                            class="px-4 py-2 flex items-center justify-between hover:bg-white/10 rounded-lg transition-colors">
                            <span class="text-soboa-orange font-bold">{{ session('predictor_name') }}</span>
                            <span class="text-white font-bold">{{ session('user_points', 0) }} pts</span>
                        </a>
                        <a href="/logout"
                            class="block px-4 py-3 text-red-400 hover:bg-white/10 rounded-lg font-semibold transition-colors">Déconnexion</a>
                    </div>
                @else
                    <div class="pt-4">
                        <a href="/login"
                            class="block w-full bg-soboa-orange hover:bg-soboa-orange-dark text-white font-bold py-3 px-4 rounded-lg text-center shadow-lg transition-colors">
                            🎮 Jouer maintenant
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow pt-16 md:pt-20">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-soboa-blue text-white py-8 mt-auto">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center overflow-hidden bg-white">
                            <img src="/images/soboa.png" alt="SOBOA" class="w-full h-full object-contain p-1">
                        </div>
                        <div>
                            <span class="font-black text-xl">SOBOA</span>
                            <span class="text-soboa-orange block text-sm font-bold">CAN 2025 - Maroc</span>
                        </div>
                    </div>
                    <p class="text-white/60 text-sm">Pronostiquez, jouez et gagnez avec SOBOA pendant la Coupe d'Afrique
                        des Nations!</p>
                </div>
                <div>
                    <h4 class="font-bold text-soboa-orange mb-4">Liens Rapides</h4>
                    <ul class="space-y-2 text-white/70 text-sm">
                        <li><a href="/matches" class="hover:text-white transition-colors">Faire un pronostic</a></li>
                        <li><a href="/leaderboard" class="hover:text-white transition-colors">Voir le classement</a>
                        </li>
                        <li><a href="/map" class="hover:text-white transition-colors">Lieux partenaires</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-soboa-orange mb-4">Système de Points</h4>
                    <ul class="space-y-2 text-white/70 text-sm">
                        <li>⚽ +1 pt / pronostic</li>
                        <li>🎯 +3 pts / bon vainqueur</li>
                        <li>🏆 +3 pts / score exact</li>
                        <li>📍 +4 pts / visite lieu partenaire</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10 pt-6 text-center">
                <p class="text-white/50 text-xs">© {{ date('Y') }} SOBOA. Tous droits réservés SOBOA Sénégal</p>
            </div>
        </div>
    </footer>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('SOBOA CAN 2025: Service Worker registered', registration.scope);
                    })
                    .catch((error) => {
                        console.error('SOBOA CAN 2025: Service Worker registration failed', error);
                    });
            });
        }
    </script>
</body>

</html>