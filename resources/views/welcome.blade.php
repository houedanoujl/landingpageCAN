<x-layouts.app title="Accueil">

    <!-- Modals Overlay Container -->
    <div x-data="{ 
        showAgeModal: localStorage.getItem('age_verified') !== 'true',
        showPwaModal: false,
        pwaPrompt: null,
        
        init() {
            // Age Modal Logic
            if (this.showAgeModal) {
                document.body.style.overflow = 'hidden';
            }

            // PWA Install Logic
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault(); // Prevent default browser banner
                this.pwaPrompt = e;
                this.checkPwaStatus();
            });

            // Si déjà vérifié, vérifier PWA tout de suite
            if (!this.showAgeModal) {
                this.checkPwaStatus();
            }
        },

        confirmAge() {
            localStorage.setItem('age_verified', 'true');
            this.showAgeModal = false;
            document.body.style.overflow = 'auto';
            
            // Wait a bit before showing PWA prompt
            setTimeout(() => this.checkPwaStatus(), 2000);
        },

        denyAge() {
            window.location.href = 'https://www.google.com';
        },
        
        checkPwaStatus() {
            // Afficher seulement si : Age OK, Prompt capturé, Pas refusé, Pas en standalone
            const isRefused = localStorage.getItem('pwa_refused') === 'true';
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches;

            if (!this.showAgeModal && this.pwaPrompt && !isRefused && !isStandalone) {
                this.showPwaModal = true;
            }
        },

        installPwa() {
            this.showPwaModal = false;
            if (this.pwaPrompt) {
                this.pwaPrompt.prompt();
                this.pwaPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the A2HS prompt');
                    } else {
                        console.log('User dismissed the A2HS prompt');
                    }
                    this.pwaPrompt = null;
                });
            }
        },

        dismissPwa() {
            this.showPwaModal = false;
            localStorage.setItem('pwa_refused', 'true');
        }
    }">

        <!-- AGE VERIFICATION MODAL -->
        <div x-show="showAgeModal" x-cloak x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-md flex items-center justify-center p-4">

            <div x-show="showAgeModal" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 text-center relative overflow-hidden">

                <!-- Background Pattern -->
                <div
                    class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-soboa-orange via-soboa-blue to-soboa-orange">
                </div>

                <div
                    class="w-24 h-24 mx-auto mb-6 flex items-center justify-center bg-white rounded-full shadow-lg p-2">
                    <img src="/images/logoGazelle.jpeg" alt="SOBOA" class="w-full h-full object-contain">
                </div>

                <h2 class="text-3xl font-black text-soboa-blue mb-2">Êtes-vous majeur ?</h2>
                <p class="text-gray-500 mb-8">L'accès à ce site est réservé aux personnes de 18 ans et plus.</p>

                <div class="flex flex-col gap-3">
                    <button @click="confirmAge()"
                        class="w-full bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-4 px-6 rounded-xl shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2">
                        <span>Oui, j'ai plus de 18 ans</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </button>

                    <button @click="denyAge()"
                        class="w-full bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-4 px-6 rounded-xl transition">
                        Non, je suis mineur
                    </button>
                </div>

                <p class="text-xs text-gray-400 mt-6 pt-6 border-t border-gray-100">
                    L'abus d'alcool est dangereux pour la santé. À consommer avec modération.
                </p>
            </div>
        </div>

        <!-- PWA INSTALL MODAL -->
        <div x-show="showPwaModal" x-cloak x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
            class="fixed bottom-0 left-0 right-0 z-[90] p-4 flex justify-center pointer-events-none">

            <div
                class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 pointer-events-auto border border-gray-100 flex flex-col sm:flex-row items-center gap-4 relative overflow-hidden">
                <!-- Close Button -->
                <button @click="dismissPwa()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>

                <!-- Icon -->
                <div class="flex-shrink-0 bg-soboa-blue/10 p-3 rounded-xl">
                    <img src="/images/logoGazelle.jpeg" alt="App Icon" class="w-12 h-12 object-contain">
                </div>

                <!-- Text -->
                <div class="flex-1 text-center sm:text-left">
                    <h3 class="font-bold text-soboa-blue text-lg">Installez l'application</h3>
                    <p class="text-sm text-gray-500">Accédez plus rapidement aux pronostics et résultats !</p>
                </div>

                <!-- Button -->
                <button @click="installPwa()"
                    class="bg-soboa-blue hover:bg-soboa-blue-dark text-white text-sm font-bold py-3 px-6 rounded-xl shadow-lg transition whitespace-nowrap">
                    Installer
                </button>
            </div>
        </div>

    </div>

    <!-- Hero Section - Grande Fête du Foot Africain Celebration -->
    <section class="relative min-h-[90vh] flex items-center justify-center overflow-hidden" x-data="{
                 countdown: { days: 0, hours: 0, minutes: 0, seconds: 0 },
                 targetDate: new Date('2025-12-21T20:00:00').getTime(),
                 scrollY: 0,
                 parallaxOffset: 0,
                 init() {
                     this.updateCountdown();
                     setInterval(() => this.updateCountdown(), 1000);
                     window.addEventListener('scroll', () => {
                         this.scrollY = window.scrollY;
                         this.parallaxOffset = this.scrollY * 0.5;
                     });
                 },
                 updateCountdown() {
                     const now = new Date().getTime();
                     const distance = this.targetDate - now;

                     if (distance < 0) {
                         this.countdown = { days: 0, hours: 0, minutes: 0, seconds: 0 };
                         return;
                     }

                     this.countdown = {
                         days: Math.floor(distance / (1000 * 60 * 60 * 24)),
                         hours: Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
                         minutes: Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)),
                         seconds: Math.floor((distance % (1000 * 60)) / 1000)
                     };
                 }
             }">

        <!-- Background -->
        <div class="absolute inset-0 overflow-hidden z-0">
            <img src="/images/sen.webp" alt="" class="absolute inset-0 w-full h-full object-cover"
                :style="`transform: translateY(${parallaxOffset}px)`">
            <div class="absolute inset-0 bg-black opacity-50"></div>
        </div>

        <!-- Animated Shapes -->
        <div
            class="absolute top-20 left-10 w-64 h-64 bg-soboa-orange/20 rounded-full blur-3xl animate-pulse-slow z-[5]">
        </div>
        <div class="absolute bottom-20 right-10 w-80 h-80 bg-soboa-orange/10 rounded-full blur-3xl animate-float z-[5]">
        </div>
        <div class="absolute top-1/2 left-1/3 w-40 h-40 bg-white/5 rounded-full blur-2xl animate-bounce-slow z-[5]">
        </div>

        <!-- Content -->
        <div class="relative z-[10] text-center px-4 max-w-5xl mx-auto">
            <!-- SOBOA FOOT TIME Badge -->
            <div
                class="inline-flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-full px-6 py-3 mb-8 border border-white/20">
                <span class="text-3xl">⚽</span>
                <span class="text-white font-bold text-lg">SOBOA FOOT TIME</span>
            </div>

            <!-- Main Heading -->
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-black text-white mb-6 leading-tight">
                Pronostiquez<br>
                <span class="gradient-text">& Gagnez!</span>
            </h1>

            <p class="text-xl md:text-2xl text-white/80 mb-10 max-w-2xl mx-auto font-medium">
                Le jeu commence ici ! Devinez les scores, visitez nos lieux partenaires et gagnez des cadeaux !
            </p>

            <!-- Countdown Timer -->
            <div class="mb-10">
                <p class="text-soboa-orange font-bold text-sm uppercase tracking-widest mb-4">Premier match - Maroc vs
                    Comores</p>
                <div class="flex justify-center gap-3 md:gap-6">
                    <div
                        class="bg-white/10 backdrop-blur-sm rounded-xl p-4 md:p-6 min-w-[70px] md:min-w-[100px] border border-white/20">
                        <span class="text-3xl md:text-5xl font-black text-white block" x-text="countdown.days">00</span>
                        <span class="text-white/60 text-xs md:text-sm font-semibold uppercase">Jours</span>
                    </div>
                    <div
                        class="bg-white/10 backdrop-blur-sm rounded-xl p-4 md:p-6 min-w-[70px] md:min-w-[100px] border border-white/20">
                        <span class="text-3xl md:text-5xl font-black text-white block"
                            x-text="countdown.hours">00</span>
                        <span class="text-white/60 text-xs md:text-sm font-semibold uppercase">Heures</span>
                    </div>
                    <div
                        class="bg-white/10 backdrop-blur-sm rounded-xl p-4 md:p-6 min-w-[70px] md:min-w-[100px] border border-white/20">
                        <span class="text-3xl md:text-5xl font-black text-white block"
                            x-text="countdown.minutes">00</span>
                        <span class="text-white/60 text-xs md:text-sm font-semibold uppercase">Minutes</span>
                    </div>
                    <div
                        class="bg-white/10 backdrop-blur-sm rounded-xl p-4 md:p-6 min-w-[70px] md:min-w-[100px] border border-white/20">
                        <span class="text-3xl md:text-5xl font-black text-soboa-orange block"
                            x-text="countdown.seconds">00</span>
                        <span class="text-white/60 text-xs md:text-sm font-semibold uppercase">Secondes</span>
                    </div>
                </div>
            </div>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @if(session('user_id'))
                    <a href="/matches"
                        class="inline-flex items-center justify-center gap-2 bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-4 px-8 rounded-full shadow-xl hover:shadow-2xl transition-all transform hover:scale-105 text-lg orange-glow">
                        Faire un pronostic
                    </a>
                    <a href="/dashboard"
                        class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white font-bold py-4 px-8 rounded-full border-2 border-white/30 transition-all">
                        Mon tableau de bord
                    </a>
                @else
                    <a href="/login"
                        class="inline-flex items-center justify-center gap-2 bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-4 px-8 rounded-full shadow-xl hover:shadow-2xl transition-all transform hover:scale-105 text-lg orange-glow">
                        Jouer & Gagner
                    </a>
                    <a href="/map"
                        class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white font-bold py-4 px-8 rounded-full border-2 border-white/30 transition-all">
                        Trouver un lieu
                    </a>
                @endif
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3">
                </path>
            </svg>
        </div>
    </section>

    <!-- Upcoming Matches Section -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
                <div>
                    <span class="text-soboa-orange font-bold text-sm uppercase tracking-widest">SOBOA FOOT TIME</span>
                    <div class="flex items-center gap-2 mt-2">
                        <h2 class="text-3xl md:text-4xl font-black text-soboa-blue">Prochains matchs</h2>
                        <span class="text-3xl md:text-4xl">⚽</span>
                    </div>
                </div>
                <a href="/matches"
                    class="text-soboa-orange font-bold hover:underline mt-4 md:mt-0 flex items-center gap-2">
                    Voir tous les matchs
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($upcomingMatches as $match)
                    <x-match-card :match="$match" />
                @empty
                    <div class="col-span-3 text-center py-16 bg-white rounded-2xl shadow">
                        <div
                            class="w-20 h-20 bg-soboa-orange/10 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-4xl">⚽</span>
                        </div>
                        <p class="text-gray-500 font-medium">Aucun match programmé pour le moment.</p>
                        <p class="text-gray-400 text-sm mt-2">Revenez bientôt pour voir le calendrier complet!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Leaderboard Section -->
    <section class="py-16 bg-soboa-orange">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
                <div>
                    <span class="text-black font-bold text-sm uppercase tracking-widest">Qui sera le
                        meilleur?</span>
                    <h2 class="text-3xl md:text-4xl font-black text-black mt-2">Classement</h2>
                </div>
                <a href="/leaderboard"
                    class="text-black font-bold hover:underline mt-4 md:mt-0 flex items-center gap-2">
                    Classement complet
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @forelse($topUsers as $index => $user)
                    <div
                        class="bg-black/5 backdrop-blur-sm rounded-2xl p-6 text-center border border-black/10 {{ $index === 0 ? 'ring-2 ring-black' : '' }}">
                        <div class="text-3xl mb-2">
                            @if($index == 0) 🥇 @elseif($index == 1) 🥈 @elseif($index == 2) 🥉 @else {{ $index + 1 }}
                            @endif
                        </div>
                        <div
                            class="w-16 h-16 bg-soboa-orange/20 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl font-bold text-black">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                        <h3 class="font-bold text-black text-lg truncate">{{ $user->name }}</h3>
                        <p class="text-black/70 font-black text-xl">{{ $user->points_total }} pts</p>
                    </div>
                @empty
                    <div class="col-span-5 text-center py-10">
                        <p class="text-white/60">Aucun joueur inscrit pour le moment.</p>
                        <a href="/login" class="text-soboa-orange font-bold hover:underline">Soyez le premier !</a>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-16 md:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <span class="text-soboa-orange font-bold text-sm uppercase tracking-widest">Simple & Fun</span>
                <h2 class="text-3xl md:text-5xl font-black text-soboa-blue mt-2">Comment ça marche?</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Step 1 -->
                <div class="text-center group">
                    <div
                        class="w-20 h-20 bg-soboa-orange/10 group-hover:bg-soboa-orange rounded-2xl flex items-center justify-center mx-auto mb-6 transition-colors">
                        <span class="text-4xl group-hover:scale-125 transition-transform">📱</span>
                    </div>
                    <div
                        class="bg-soboa-orange text-black font-bold w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-4">
                        1</div>
                    <h3 class="font-bold text-soboa-blue text-xl mb-2">Inscrivez-vous</h3>
                    <p class="text-gray-600">Créez votre compte avec votre numéro. C'est gratuit!</p>
                </div>

                <!-- Step 2 -->
                <div class="text-center group">
                    <div
                        class="w-20 h-20 bg-soboa-orange/10 group-hover:bg-soboa-orange rounded-2xl flex items-center justify-center mx-auto mb-6 transition-colors">
                        <span class="text-4xl group-hover:scale-125 transition-transform">⚽</span>
                    </div>
                    <div
                        class="bg-soboa-orange text-black font-bold w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-4">
                        2</div>
                    <h3 class="font-bold text-soboa-blue text-xl mb-2">Pronostiquez</h3>
                    <p class="text-gray-600">Prédisez les scores des matchs de la CAN.</p>
                </div>

                <!-- Step 3 -->
                <div class="text-center group">
                    <div
                        class="w-20 h-20 bg-soboa-orange/10 group-hover:bg-soboa-orange rounded-2xl flex items-center justify-center mx-auto mb-6 transition-colors">
                        <span class="text-4xl group-hover:scale-125 transition-transform">📍</span>
                    </div>
                    <div
                        class="bg-soboa-orange text-black font-bold w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-4">
                        3</div>
                    <h3 class="font-bold text-soboa-blue text-xl mb-2">Visitez les lieux</h3>
                    <p class="text-gray-600">+4 points bonus en visitant nos lieux partenaires.</p>
                </div>

                <!-- Step 4 -->
                <div class="text-center group">
                    <div
                        class="w-20 h-20 bg-soboa-orange/10 group-hover:bg-soboa-orange rounded-2xl flex items-center justify-center mx-auto mb-6 transition-colors">
                        <span class="text-4xl group-hover:scale-125 transition-transform">🏆</span>
                    </div>
                    <div
                        class="bg-soboa-orange text-black font-bold w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-4">
                        4</div>
                    <h3 class="font-bold text-soboa-blue text-xl mb-2">Gagnez</h3>
                    <p class="text-gray-600">Accumulez des points et remportez des cadeaux!</p>
                </div>
            </div>

            <!-- Points Breakdown -->
            <div class="mt-16 bg-soboa-blue rounded-2xl p-8 text-white">
                <h3 class="text-center font-black text-2xl mb-8">Système de points</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div class="bg-white/10 rounded-xl p-4">
                        <span class="text-3xl font-black text-soboa-orange">+1</span>
                        <p class="text-sm text-white/80 mt-2">Participation</p>
                    </div>
                    <div class="bg-white/10 rounded-xl p-4">
                        <span class="text-3xl font-black text-soboa-orange">+3</span>
                        <p class="text-sm text-white/80 mt-2">Bon vainqueur</p>
                    </div>
                    <div class="bg-white/10 rounded-xl p-4">
                        <span class="text-3xl font-black text-soboa-orange">+3</span>
                        <p class="text-sm text-white/80 mt-2">Score exact</p>
                    </div>
                    <div class="bg-white/10 rounded-xl p-4">
                        <span class="text-3xl font-black text-soboa-orange">+4</span>
                        <p class="text-sm text-white/80 mt-2">Visite lieu</p>
                    </div>
                </div>
                <p class="text-center text-white/60 text-sm mt-4">Maximum 7 points par match + 4 points bonus par visite
                </p>
            </div>

            <!-- CTA -->
            <div class="text-center mt-12">
                <a href="/login"
                    class="inline-flex items-center gap-2 bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-4 px-10 rounded-full shadow-xl hover:shadow-2xl transition-all transform hover:scale-105 text-lg">
                    Commencer maintenant
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </a>
            </div>
        </div>
    </section>

</x-layouts.app>