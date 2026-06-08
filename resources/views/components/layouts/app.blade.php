@php
    // Récupérer les points réels de l'utilisateur depuis la base de données
    // pour éviter les problèmes de cache de session
    $userPoints = 0;
    if (session('user_id')) {
        $currentUser = \App\Models\User::find(session('user_id'));
        if ($currentUser) {
            $userPoints = $currentUser->points_total;
            // Mettre à jour la session avec les vrais points
            session(['user_points' => $userPoints]);
        }
    }

    $navItems = [
        ['href' => route('home'), 'label' => 'Accueil', 'active' => request()->routeIs('home'), 'featured' => false],
        ['href' => route('matches'), 'label' => 'Pronostics', 'active' => request()->routeIs('matches'), 'featured' => false],
        ['href' => route('calendar'), 'label' => 'Calendrier', 'active' => request()->routeIs('calendar'), 'featured' => false],
        ['href' => route('leaderboard'), 'label' => 'Classement', 'active' => request()->routeIs('leaderboard'), 'featured' => false],
        ['href' => route('soboa-foot'), 'label' => 'SOBOA FOOT', 'active' => request()->routeIs('soboa-foot'), 'featured' => true],
    ];

    $desktopNavBase = 'px-4 py-2 rounded-lg font-semibold text-sm transition-all focus:outline-none focus:ring-2 focus:ring-white/70';
    $desktopNavActive = 'text-white bg-white/15 ring-1 ring-white/20 shadow-inner';
    $desktopNavInactive = 'text-white/80 hover:text-white hover:bg-white/10';
    $desktopNavFeatured = 'px-4 py-2 rounded-full font-black text-sm transition-all shadow-md shadow-black/10 focus:outline-none focus:ring-2 focus:ring-white/70';
    $desktopNavFeaturedActive = 'bg-white text-soboa-blue';
    $desktopNavFeaturedInactive = 'bg-soboa-orange text-white hover:bg-soboa-orange-secondary';

    $mobileNavBase = 'block px-4 py-3 rounded-lg font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-white/70';
    $mobileNavActive = 'text-white bg-white/15 ring-1 ring-white/20';
    $mobileNavInactive = 'text-white hover:bg-white/10';
    $mobileNavFeaturedActive = 'bg-white text-soboa-blue';
    $mobileNavFeaturedInactive = 'bg-soboa-orange text-white hover:bg-soboa-orange-secondary font-black';
@endphp
<!DOCTYPE html>
<!--
    Developed by Big Five Abidjan
    https://bigfive.solutions
    Support: jeanluc(at)bigfiveabidjan.com
-->
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Cache Control optimisé pour bfcache et performance -->
    <meta http-equiv="Cache-Control" content="public, max-age=600, stale-while-revalidate=300">
    
    <!-- Prefetch DNS pour ressources externes -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//www.googletagmanager.com">
    
    <!-- Preconnect pour ressources critiques -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Prefetch des pages principales pour navigation rapide -->
    @if(request()->route()->getName() !== 'home')
        <link rel="prefetch" href="{{ route('home') }}" as="document">
    @endif
    @if(request()->route()->getName() !== 'matches')
        <link rel="prefetch" href="{{ route('matches') }}" as="document">
    @endif
    @if(request()->route()->getName() !== 'leaderboard')
        <link rel="prefetch" href="{{ route('leaderboard') }}" as="document">
    @endif
    
    <title>SOBOA FOOT TIME | {{ $title ?? 'Accueil' }}</title>
    <meta name="description"
        content="Le jeu commence ici ! Pronostiquez les matchs, gagnez des points et devenez le meilleur pronostiqueur !">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="SOBOA FOOT TIME | {{ $title ?? 'Accueil' }}">
    <meta property="og:description"
        content="Le jeu commence ici ! Pronostiquez les matchs, visitez nos lieux partenaires et gagnez des récompenses exclusives.">
    <meta property="og:image" content="{{ asset('images/sen.webp') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="SOBOA FOOT TIME | {{ $title ?? 'Accueil' }}">
    <meta property="twitter:description"
        content="Le jeu commence ici ! Pronostiquez les matchs et gagnez des récompenses.">
    <meta property="twitter:image" content="{{ asset('images/sen.webp') }}">

    <!-- Google tag (gtag.js) - Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-PZ3EWMZ408"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-PZ3EWMZ408');
        gtag('config', 'GT-P36Z7M8B');
    </script>

    <link rel="icon" type="image/jpeg" href="/images/logoSOBOA.png.webp">
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#0058A3">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Google Fonts - Montserrat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&display=swap"
        rel="stylesheet">

    <!-- Vite & Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script>
        function renderLucideIcons() {
            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                window.lucide.createIcons();
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            renderLucideIcons();
            const debouncedRender = (() => {
                let timer = null;
                return () => {
                    clearTimeout(timer);
                    timer = setTimeout(renderLucideIcons, 50);
                };
            })();
            const observer = new MutationObserver((mutations) => {
                for (const m of mutations) {
                    for (const node of m.addedNodes) {
                        if (node.nodeType !== 1) continue;
                        if (node.matches?.('[data-lucide]') || node.querySelector?.('[data-lucide]')) {
                            debouncedRender();
                            return;
                        }
                    }
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        });
        document.addEventListener('alpine:initialized', renderLucideIcons);
        document.addEventListener('livewire:navigated', renderLucideIcons);
    </script>

    <style>
        * {
            font-family: 'Montserrat', sans-serif;
        }

        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23121212' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        /* Force hide/show navigation elements at correct breakpoints */
        @media (min-width: 1024px) {
            /* Desktop: HIDE hamburger button and mobile menu */
            nav button.lg\:\!hidden {
                display: none !important;
            }
            nav .lg\:hidden {
                display: none !important;
            }
            /* Desktop: SHOW desktop menu */
            nav .lg\:flex {
                display: flex !important;
            }
            /* Desktop: SHOW "Jouer maintenant" button */
            nav a.lg\:inline-flex {
                display: inline-flex !important;
            }
        }
        
        @media (max-width: 1023px) {
            /* Mobile: HIDE desktop menu */
            nav .lg\:flex {
                display: none !important;
            }
            /* Mobile: HIDE "Jouer maintenant" button */
            nav a.lg\:inline-flex {
                display: none !important;
            }
        }

        /* Landscape mode optimizations for navigation */
        @media (max-height: 600px) and (orientation: landscape) {
            nav {
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
            }
            
            nav .w-12, nav .h-12 {
                width: 2.5rem !important;
                height: 2.5rem !important;
            }
            
            nav .w-16, nav .h-16 {
                width: 3rem !important;
                height: 3rem !important;
            }
            
            nav .text-xl {
                font-size: 1rem !important;
            }
            
            nav .text-2xl {
                font-size: 1.25rem !important;
            }
            
            main {
                padding-top: 70px !important;
            }
        }

        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .glass-dark {
            background: rgba(18, 18, 18, 0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .gradient-text {
            background: linear-gradient(135deg, #F4A05B 0%, #F1862D 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-gradient {
            background: linear-gradient(135deg, #0058A3 0%, #0054A1 50%, #0B1F33 100%);
        }

        .orange-glow {
            box-shadow: 0 0 40px rgba(241, 134, 45, 0.35);
        }

        [x-cloak] {
            display: none !important;
        }

        /* Swiper custom styling */
        .upcomingMatchesSwiper {
            padding: 20px 0 40px 0;
            overflow: hidden;
        }

        .upcomingMatchesSwiper .swiper-wrapper {
            display: flex;
        }

        .upcomingMatchesSwiper .swiper-slide {
            width: 100% !important;
            flex-shrink: 0;
            max-width: 500px;
            margin: 0 auto;
        }

        .upcomingMatchesSwiper .swiper-button-next,
        .upcomingMatchesSwiper .swiper-button-prev {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .upcomingMatchesSwiper .swiper-button-next:after,
        .upcomingMatchesSwiper .swiper-button-prev:after {
            font-size: 20px;
        }

        .upcomingMatchesSwiper .swiper-pagination-bullet {
            background: white;
            opacity: 0.5;
        }

        .upcomingMatchesSwiper .swiper-pagination-bullet-active {
            opacity: 1;
        }
    </style>

    <!-- Fonction d'initialisation Swiper pour Alpine.js -->
    <script>
        function initUpcomingMatchesSwiper() {
            // Utiliser setTimeout avec 0ms pour s'assurer que le DOM est complètement rendu
            setTimeout(() => {
                // Vérifier si le container existe
                const swiperContainer = document.querySelector('.upcomingMatchesSwiper');
                if (!swiperContainer) {
                    console.warn('Swiper container not found');
                    return;
                }

                // Initialiser Swiper - toujours 1 seul item visible
                const swiper = new Swiper('.upcomingMatchesSwiper', {
                    slidesPerView: 1,
                    spaceBetween: 20,
                    loop: true,
                    centeredSlides: true,
                    autoplay: {
                        delay: 5000,
                        disableOnInteraction: false,
                        pauseOnMouseEnter: true,
                    },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                    // Pas de breakpoints - toujours 1 seul item
                });
            }, 0);
        }

        // Fonction pour scroller vers un match spécifique
        function scrollToMatch(matchId) {
            const matchElement = document.querySelector(`[data-match-id="${matchId}"]`);
            if (matchElement) {
                matchElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Effet highlight
                matchElement.classList.add('ring-4', 'ring-soboa-orange', 'ring-opacity-50');
                setTimeout(() => {
                    matchElement.classList.remove('ring-4', 'ring-soboa-orange', 'ring-opacity-50');
                }, 2000);
            }
        }

        // Fonction pour mettre à jour les points du header
        function updateHeaderPoints(newPoints) {
            // Sélectionner tous les éléments affichant les points
            const pointsElements = document.querySelectorAll('[data-user-points]');
            pointsElements.forEach(el => {
                el.textContent = newPoints;
                // Ajouter une animation
                el.classList.add('animate-pulse');
                setTimeout(() => {
                    el.classList.remove('animate-pulse');
                }, 1000);
            });
        }

        // Écouter les événements de mise à jour des points
        window.addEventListener('update-points', (e) => {
            if (e.detail && e.detail.points) {
                updateHeaderPoints(e.detail.points);
            }
        });
    </script>
</head>

<body class="bg-gray-50 bg-pattern min-h-screen flex flex-col" x-data="{ mobileMenuOpen: false, toast: null }" x-init="
    @if(session('toast'))
        toast = {{ session('toast') }};
        setTimeout(() => toast = null, 4000);
    @endif

    // Écouter les événements custom pour afficher le toast
    window.addEventListener('show-toast', (e) => {
        toast = e.detail;
        setTimeout(() => toast = null, 4000);
    });
">

    <!-- Skip to content (keyboard a11y) -->
    <a href="#main-content" class="skip-link">Aller au contenu principal</a>

    <!-- Toast Notification -->
    <div x-show="toast" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2" x-cloak
        class="fixed bottom-24 left-1/2 -translate-x-1/2 z-[100] w-auto max-w-sm">
        <div class="bg-green-600 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                <i data-lucide="check" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="font-bold text-lg" x-text="toast?.message"></div>
                <div class="text-white/80 text-sm" x-text="toast?.description"></div>
            </div>
            <button @click="toast = null" class="ml-2 text-white/60 hover:text-white p-1 focus:outline-none focus:ring-2 focus:ring-white rounded-full">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
    </div>

    <!-- Navigation -->
    <nav
        class="fixed top-0 left-0 right-0 z-[1001] transition-all duration-300 bg-soboa-blue backdrop-blur-md shadow-xl border-b border-white/10">
        <div class="max-w-7xl mx-auto px-3 fold:px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between py-3 fold:py-4 gap-2 lg:gap-4">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-3 group">
                    <div
                        class="w-12 h-12 md:w-16 md:h-16 rounded-full flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform overflow-hidden bg-white border-2 border-white">
                        <img src="/images/logoSOBOA.png.webp" alt="SOBOA FOOT TIME" class="w-full h-full object-contain p-0.5">
                    </div>
                    <div class="text-white">
                        <span
                            class="font-black text-xl md:text-2xl tracking-tighter uppercase leading-none block">SOBOA FOOT TIME</span>
                    </div>
                </a>

                <!-- Desktop Navigation (visible >= 1024px) -->
                <div class="hidden lg:flex items-center gap-1 flex-grow justify-center">
                    @foreach($navItems as $item)
                        @php
                            $desktopClass = $item['featured']
                                ? $desktopNavFeatured . ' ' . ($item['active'] ? $desktopNavFeaturedActive : $desktopNavFeaturedInactive)
                                : $desktopNavBase . ' ' . ($item['active'] ? $desktopNavActive : $desktopNavInactive);
                        @endphp
                        <a href="{{ $item['href'] }}"
                           @if($item['active']) aria-current="page" @endif
                           class="{{ $desktopClass }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>

                <!-- User Actions -->
                <div class="flex items-center gap-3">
                    @if(session('user_id'))
                        <div class="hidden lg:flex items-center gap-2 flex-shrink-0">
                            <a href="/mes-pronostics"
                                class="btn btn-ghost-light btn-sm btn-pill">
                                Mes Pronostics
                            </a>
                            <a href="/dashboard"
                                class="group flex items-center gap-2.5 bg-white/5 hover:bg-white/10 rounded-full pl-3.5 pr-1.5 py-1 ring-1 ring-white/15 transition-all">
                                <div class="text-right hidden xl:block leading-tight">
                                    <span
                                        class="text-white group-hover:text-soboa-orange font-bold text-sm block transition-colors">{{ session('predictor_name') }}</span>
                                    <span class="text-[10px] text-white/55 uppercase tracking-wider font-semibold">Mon Compte</span>
                                </div>
                                <div
                                    class="bg-gradient-to-r from-soboa-orange to-soboa-orange-secondary pl-3 pr-2 py-1.5 rounded-full flex items-center gap-1.5 shadow-md ring-1 ring-white/20">
                                    <span class="text-white font-black text-sm" data-user-points>{{ $userPoints }}</span>
                                    <span class="text-white/90 text-[10px] font-bold uppercase">pts</span>
                                    <div class="bg-white/20 rounded-full w-5 h-5 flex items-center justify-center ml-0.5">
                                        <i data-lucide="trophy" class="w-3 h-3 text-white"></i>
                                    </div>
                                </div>
                            </a>
                            <a href="/logout" title="Déconnexion" aria-label="Déconnexion"
                                class="p-2 text-white/60 hover:text-white hover:bg-white/10 rounded-full transition-colors">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                            </a>
                        </div>
                    @else
                        <a href="/login" class="btn btn-primary btn-md btn-pill hidden lg:inline-flex">
                            Jouer maintenant
                        </a>
                        <a href="/login" class="btn btn-primary btn-sm btn-pill lg:hidden">
                            Jouer
                        </a>
                    @endif

                    <!-- Mobile Menu Button (VISIBLE < 1024px, HIDDEN >= 1024px) -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                        :aria-expanded="mobileMenuOpen.toString()"
                        aria-controls="mobile-menu"
                        aria-label="Menu"
                        class="lg:!hidden p-2.5 text-white hover:bg-white/10 rounded-lg transition-colors flex-shrink-0 focus:outline-none focus:ring-2 focus:ring-white">
                        <i x-show="!mobileMenuOpen" data-lucide="menu" class="w-6 h-6"></i>
                        <i x-show="mobileMenuOpen" x-cloak data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-4" x-cloak
            id="mobile-menu"
            class="lg:hidden bg-soboa-blue border-t border-white/10 relative z-[100]">
            <div class="px-4 py-4 space-y-2">
                @foreach($navItems as $item)
                    @php
                        $mobileClass = $item['featured']
                            ? $mobileNavBase . ' ' . ($item['active'] ? $mobileNavFeaturedActive : $mobileNavFeaturedInactive)
                            : $mobileNavBase . ' ' . ($item['active'] ? $mobileNavActive : $mobileNavInactive);
                    @endphp
                    <a href="{{ $item['href'] }}"
                       @click="mobileMenuOpen = false"
                       @if($item['active']) aria-current="page" @endif
                       class="{{ $mobileClass }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach

                @if(session('user_id'))
                    <div class="pt-4 border-t border-white/10">
                        <a href="/mes-pronostics"
                            class="block px-4 py-3 text-white hover:bg-white/10 rounded-lg font-semibold transition-colors">
                            Mes Pronostics
                        </a>
                        <a href="/dashboard"
                            class="px-4 py-3 flex items-center justify-between hover:bg-white/10 rounded-lg transition-colors group">
                            <span
                                class="text-white group-hover:text-soboa-orange font-bold transition-colors">{{ session('predictor_name') }}</span>
                            <div
                                class="bg-gradient-to-r from-soboa-orange to-soboa-orange-secondary px-3 py-1 rounded-full flex items-center gap-1 shadow-sm">
                                <span class="text-white font-black text-sm" data-user-points>{{ $userPoints }}</span>
                                <span class="text-white/80 text-xs font-bold uppercase">pts</span>
                            </div>
                        </a>
                        <a href="/logout"
                            class="block px-4 py-3 text-red-300 hover:bg-white/10 rounded-lg font-semibold transition-colors">Déconnexion</a>
                    </div>
                @else
                    <div class="pt-4">
                        <a href="/login"
                            class="block w-full bg-soboa-orange hover:bg-soboa-orange-secondary text-white font-bold py-3 px-4 rounded-lg text-center shadow-lg transition-colors">
                            Jouer maintenant
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main id="main-content" tabindex="-1" class="flex-grow pt-[100px]">
        {{ $slot }}
    </main>

    <!-- Geolocation Banner (Auto-detect) -->
    @if(session('user_id'))
        @php
            $activeVenues = \App\Models\Bar::where('is_active', true)
                ->select(['id', 'name', 'zone', 'latitude', 'longitude', 'type_pdv'])
                ->get();
        @endphp
        <x-geolocation-banner :venues="$activeVenues" />
    @endif

    <!-- Footer -->
    <footer class="bg-soboa-blue text-white py-8 mt-auto">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="text-center md:text-left">
                    <div class="flex items-center gap-3 mb-4 justify-center md:justify-start">
                        <div
                            class="w-12 h-12 rounded-full flex items-center justify-center overflow-hidden bg-white border border-white/20">
                            <img src="/images/logoSOBOA.png.webp" alt="SOBOA FOOT TIME" class="w-full h-full object-contain p-1">
                        </div>
                        <div>
                            <span class="font-black text-xl uppercase">SOBOA FOOT TIME</span>
                        </div>
                    </div>
                    <p class="text-white/60 text-sm">Pronostiquez, jouez et gagnez avec SOBOA FOOT TIME !</p>
                </div>
                <div class="text-center md:text-left">
                    <h4 class="font-bold text-soboa-orange mb-4">Liens rapides</h4>
                    <ul class="space-y-2 text-white/70 text-sm">
                        <li><a href="/matches" class="hover:text-white transition-colors">Faire un pronostic</a></li>
                        <li><a href="/calendar" class="hover:text-white transition-colors">Calendrier des matchs</a></li>
                        <li><a href="/leaderboard" class="hover:text-white transition-colors">Voir le classement</a>
                        </li>
                        <li><a href="/map" class="hover:text-white transition-colors">Lieux partenaires</a></li>
                    </ul>
                </div>
                <div class="text-center md:text-left">
                    <h4 class="font-bold text-soboa-orange mb-4">Système de points</h4>
                    <ul class="space-y-2 text-white/70 text-sm">
                        <li>+1 pt / connexion quotidienne</li>
                        <li>+1 pt / pronostic</li>
                        <li>+3 pts / bon vainqueur</li>
                        <li>+3 pts / score exact</li>
                        <li>+4 pts / visite lieu partenaire</li>
                    </ul>
                </div>
            </div>
            
            <!-- Mention légale 18+ -->
            <div class="bg-red-600/20 border border-red-500/30 rounded-xl p-4 mt-6">
                <div class="flex items-center justify-center gap-3">
                    <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-black text-lg">18+</span>
                    </div>
                    <div class="text-center">
                        <p class="text-red-400 font-bold text-sm">Ce jeu est réservé aux plus de 18 ans</p>
                        <p class="text-white/60 text-xs mt-1">
                            L'abus d'alcool est dangereux pour la santé. À consommer avec modération.
                        </p>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('terms') }}" class="text-soboa-orange hover:text-white text-xs underline transition-colors">
                        Consulter les conditions de participation
                    </a>
                </div>
            </div>
            
            <div class="border-t border-white/10 pt-6 text-center">
                <p class="text-white/50 text-xs">© {{ date('Y') }} SOBOA FOOT TIME. Tous droits réservés SOBOA Sénégal</p>
            </div>
        </div>
    </footer>


    <!-- Top Progress Bar -->
    <div id="page-loader" class="fixed top-0 left-0 right-0 h-1 z-[9999] pointer-events-none">
        <div id="page-loader-bar" class="h-full bg-soboa-orange shadow-[0_0_8px_rgba(255,102,0,0.6)] origin-left scale-x-0 opacity-0 transition-transform duration-700 ease-out"></div>
    </div>

    <script>
        // Top progress bar driver with bfcache support
        (function() {
            const loader = document.getElementById('page-loader');
            const bar = document.getElementById('page-loader-bar');
            const initialLoadTime = performance.now();
            let trickleTimer = null;

            const setScale = (s) => {
                if (!bar) return;
                bar.style.transform = `scaleX(${s})`;
            };

            const hideLoader = () => {
                if (!bar) return;
                bar.style.opacity = '1';
                setScale(1);
                clearTimeout(trickleTimer);
                setTimeout(() => {
                    bar.style.opacity = '0';
                    setTimeout(() => { setScale(0); }, 200);
                }, 200);
            };

            const showLoader = () => {
                if (!bar) return;
                clearTimeout(trickleTimer);
                bar.style.opacity = '1';
                setScale(0.15);
                const trickle = (v) => {
                    if (v >= 0.9) return;
                    trickleTimer = setTimeout(() => {
                        const next = v + (0.9 - v) * 0.15;
                        setScale(next);
                        trickle(next);
                    }, 250);
                };
                trickle(0.15);
            };

            // Hide loader on initial load
            if (document.readyState === 'loading') {
                window.addEventListener('DOMContentLoaded', hideLoader);
            } else {
                hideLoader();
            }

            // Handle browser back/forward cache (bfcache) - CRITICAL for back button
            window.addEventListener('pageshow', (event) => {
                if (event.persisted) {
                    // Page was restored from bfcache (User hit "Back" or "Forward")
                    console.log('[SOBOA FOOT TIME] Page restored from bfcache - Instant load!');
                    hideLoader();
                    
                    // Restore dynamic content from sessionStorage
                    try {
                        const cachedPoints = sessionStorage.getItem('user_points');
                        if (cachedPoints) {
                            document.querySelectorAll('[data-user-points]').forEach(el => {
                                el.textContent = cachedPoints;
                            });
                        }
                        
                        // Restaurer l'état de la bulle géo si elle existe
                        const geoState = sessionStorage.getItem('geo_state');
                        if (geoState && typeof window.showGeoState === 'function') {
                            window.showGeoState(geoState);
                        }
                    } catch (e) {
                        console.warn('[SOBOA FOOT TIME] Erreur restauration cache:', e);
                    }
                    
                    // Force scroll restoration
                    if (history.scrollRestoration) {
                        history.scrollRestoration = 'auto';
                    }
                }
            });

            // Handle page hide (before unload) - Prepare for bfcache
            window.addEventListener('pagehide', () => {
                // Store current state in sessionStorage for bfcache restore
                try {
                    const pointsElement = document.querySelector('[data-user-points]');
                    if (pointsElement) {
                        sessionStorage.setItem('user_points', pointsElement.textContent);
                    }
                    
                    // Stocker l'état de la géolocalisation
                    if (window.currentGeoState) {
                        sessionStorage.setItem('geo_state', window.currentGeoState);
                    }
                } catch (e) {
                    console.warn('[SOBOA FOOT TIME] Erreur sauvegarde cache:', e);
                }
            });
            
            // Optimisation: Passive event listeners pour meilleure performance
            document.addEventListener('touchstart', () => {}, { passive: true });
            document.addEventListener('touchmove', () => {}, { passive: true });

            // Show loader on link click for better "perceived" speed
            document.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (!link) return;

                const href = link.getAttribute('href');

                // Conditions to show loader:
                // 1. Internal link (starts with / or current domain)
                // 2. Not an anchor (#)
                // 3. Not a JS action (javascript:)
                // 4. Not opening in a new tab (_blank)
                // 5. Not a download link
                // 6. Not a form submit button
                if (href && href.startsWith('/') &&
                    !href.startsWith('#') &&
                    !href.includes(':') &&
                    !link.hasAttribute('target') &&
                    !link.hasAttribute('download') &&
                    !link.closest('form')) {

                    // Check if it's the same URL (no need to show loader)
                    if (href === window.location.pathname) return;

                    // Only show loader if initial load completed (avoid flash on fast loads)
                    if (performance.now() - initialLoadTime > 500) {
                        showLoader();
                    }
                }
            }, true); // Use capture phase for better performance
        })();
    </script>

    <style>
        /* Fade In Up Animation for Main Content */
        main {
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((registration) => {
                        console.log('SW registered:', registration.scope);
                    })
                    .catch((err) => {
                        console.log('SW registration failed:', err);
                    });
            });
        }
    </script>

    <!-- Daily Reward System - Heartbeat & Visibility Detection -->
    @auth
    <script>
        (function() {
            'use strict';
            
            const DailyReward = {
                lastCheckDate: null,
                checkInterval: null,
                isChecking: false,
                
                init() {
                    // Get today's date string
                    this.lastCheckDate = localStorage.getItem('dailyReward_lastCheck');
                    const today = this.getTodayString();
                    
                    // Check on page load if not checked today
                    if (this.lastCheckDate !== today) {
                        this.checkAndAward();
                    }
                    
                    // Listen for visibility changes (user returns to tab after leaving overnight)
                    document.addEventListener('visibilitychange', () => {
                        if (document.visibilityState === 'visible') {
                            this.onVisibilityChange();
                        }
                    });
                    
                    // Listen for focus events (backup for visibility)
                    window.addEventListener('focus', () => {
                        this.onVisibilityChange();
                    });
                    
                    // Periodic check every 5 minutes for long sessions
                    this.checkInterval = setInterval(() => {
                        this.periodicCheck();
                    }, 5 * 60 * 1000); // 5 minutes
                    
                    // Also check when user wakes from sleep (pageshow event)
                    window.addEventListener('pageshow', (event) => {
                        if (event.persisted) {
                            // Page was restored from bfcache
                            this.onVisibilityChange();
                        }
                    });
                },
                
                getTodayString() {
                    const now = new Date();
                    return now.toISOString().split('T')[0]; // YYYY-MM-DD
                },
                
                onVisibilityChange() {
                    const today = this.getTodayString();
                    if (this.lastCheckDate !== today) {
                        this.checkAndAward();
                    }
                },
                
                periodicCheck() {
                    const today = this.getTodayString();
                    if (this.lastCheckDate !== today) {
                        this.checkAndAward();
                    }
                },
                
                async checkAndAward() {
                    if (this.isChecking) return;
                    this.isChecking = true;
                    
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        
                        const response = await fetch('/api/daily-reward/heartbeat', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            credentials: 'same-origin'
                        });
                        
                        if (!response.ok) {
                            console.log('Daily reward check failed:', response.status);
                            return;
                        }
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            // Update last check date
                            this.lastCheckDate = this.getTodayString();
                            localStorage.setItem('dailyReward_lastCheck', this.lastCheckDate);
                            
                            // If points were awarded, show notification and update UI
                            if (data.awarded && data.message) {
                                this.showRewardNotification(data.message, data.total_points);
                                this.updatePointsDisplay(data.total_points);
                            }
                        }
                    } catch (error) {
                        console.log('Daily reward check error:', error);
                    } finally {
                        this.isChecking = false;
                    }
                },
                
                showRewardNotification(message, totalPoints) {
                    // Create toast notification
                    const toast = document.createElement('div');
                    toast.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 z-50 bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 animate-bounce-in';
                    toast.innerHTML = `
                        <div>
                            <div class="font-bold text-lg">${message}</div>
                            <div class="text-sm text-white/80">Total: ${totalPoints} points</div>
                        </div>
                    `;
                    
                    // Add animation styles if not present
                    if (!document.getElementById('daily-reward-styles')) {
                        const style = document.createElement('style');
                        style.id = 'daily-reward-styles';
                        style.textContent = `
                            @keyframes bounceIn {
                                0% { opacity: 0; transform: translate(-50%, -20px) scale(0.9); }
                                50% { transform: translate(-50%, 5px) scale(1.02); }
                                100% { opacity: 1; transform: translate(-50%, 0) scale(1); }
                            }
                            .animate-bounce-in { animation: bounceIn 0.5s ease-out forwards; }
                            @keyframes fadeOut {
                                to { opacity: 0; transform: translate(-50%, -20px); }
                            }
                            .animate-fade-out { animation: fadeOut 0.3s ease-in forwards; }
                        `;
                        document.head.appendChild(style);
                    }
                    
                    document.body.appendChild(toast);
                    
                    // Remove after 5 seconds
                    setTimeout(() => {
                        toast.classList.remove('animate-bounce-in');
                        toast.classList.add('animate-fade-out');
                        setTimeout(() => toast.remove(), 300);
                    }, 5000);
                },
                
                updatePointsDisplay(points) {
                    // Update all points displays on the page
                    const pointsElements = document.querySelectorAll('[data-user-points]');
                    pointsElements.forEach(el => {
                        el.textContent = points;
                    });
                    
                    // Dispatch event for Alpine.js components
                    window.dispatchEvent(new CustomEvent('update-points', {
                        detail: { points: points }
                    }));
                }
            };
            
            // Initialize when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => DailyReward.init());
            } else {
                DailyReward.init();
            }
        })();
    </script>
    @endauth

    {{-- Modal GLOBAL UNIQUE : pronostics d'un match + likes.
         Ouvert via $dispatch('open-match-predictions', {matchId}) depuis x-match-row. --}}
    <div x-data="matchPredictionsModal()"
         @open-match-predictions.window="open($event.detail.matchId)"
         x-show="isOpen" x-cloak style="display:none;"
         @keydown.escape.window="close()"
         class="modal-backdrop">
        <div class="modal-panel p-0 overflow-hidden" @click.outside="close()">
            <header class="bg-gradient-to-r from-soboa-blue to-soboa-blue-light text-white px-5 py-4 flex items-center justify-between">
                <div class="min-w-0">
                    <h3 class="font-black text-lg leading-tight">Pronostics</h3>
                    <p class="text-xs text-white/80 truncate" x-text="title"></p>
                </div>
                <button @click="close()" class="modal-close" aria-label="Fermer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </header>
            <div class="max-h-[60vh] overflow-y-auto p-4 space-y-2">
                <template x-if="loading">
                    <p class="text-center text-gray-500 py-6">Chargement…</p>
                </template>
                <template x-if="!loading && items.length === 0">
                    <p class="text-center text-gray-500 py-6">Aucun pronostic pour ce match.</p>
                </template>
                <template x-for="p in items" :key="p.id">
                    <div class="flex items-center justify-between gap-3 bg-gray-50 rounded-xl px-3 py-2.5">
                        <div class="min-w-0">
                            <p class="font-bold text-sm text-soboa-text-dark truncate" x-text="p.user_name + (p.is_mine ? ' (vous)' : '')"></p>
                            <p class="text-xs text-gray-500">
                                Pronostic : <span class="font-black text-soboa-blue" x-text="p.score_a + ' - ' + p.score_b"></span>
                                <span x-show="p.points_earned > 0" class="text-soboa-orange font-bold" x-text="'· +' + p.points_earned + ' pts'"></span>
                            </p>
                        </div>
                        <button type="button" @click="toggleLike(p)" :disabled="!auth || p.is_mine"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-bold transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="p.liked ? 'bg-red-100 text-red-600' : 'bg-white text-gray-500 ring-1 ring-gray-200 hover:bg-gray-100'">
                            <i data-lucide="heart" class="w-4 h-4" :class="p.liked ? 'fill-current' : ''"></i>
                            <span x-text="p.likes_count"></span>
                        </button>
                    </div>
                </template>
                <p x-show="!auth && !loading" class="text-center text-xs text-gray-400 pt-2">
                    <a href="/login" class="text-soboa-blue font-bold">Connectez-vous</a> pour liker.
                </p>
            </div>
        </div>
    </div>
    <script>
        function matchPredictionsModal() {
            return {
                isOpen: false,
                loading: false,
                items: [],
                title: '',
                auth: false,
                matchId: null,
                open(matchId) {
                    this.matchId = matchId;
                    this.isOpen = true;
                    document.body.style.overflow = 'hidden';
                    this.load();
                },
                close() {
                    this.isOpen = false;
                    document.body.style.overflow = '';
                },
                async load() {
                    this.loading = true;
                    this.items = [];
                    try {
                        const r = await fetch(`/matches/${this.matchId}/predictions`, { headers: { 'Accept': 'application/json' } });
                        const d = await r.json();
                        this.items = d.predictions || [];
                        this.title = d.match || '';
                        this.auth = !!d.auth;
                    } catch (e) {
                        this.items = [];
                    }
                    this.loading = false;
                    this.$nextTick(() => window.lucide && window.lucide.createIcons());
                },
                async toggleLike(p) {
                    if (!this.auth || p.is_mine) return;
                    try {
                        const r = await fetch(`/predictions/${p.id}/like`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });
                        if (r.status === 401) { window.location.href = '/login'; return; }
                        const d = await r.json();
                        p.liked = d.liked;
                        p.likes_count = d.count;
                        this.$nextTick(() => window.lucide && window.lucide.createIcons());
                    } catch (e) {}
                }
            };
        }
    </script>
</body>

</html>
