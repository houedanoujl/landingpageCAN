<x-layouts.app title="Accueil">

    @if($siteSettings && $siteSettings->tournamentWinner)
    <!-- Confetti Canvas for celebration -->
    <canvas id="confetti-canvas" class="fixed inset-0 w-full h-full pointer-events-none z-[200]"></canvas>
    @endif

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
                    <img src="/images/logoSOBOA.png.webp" alt="SOBOA" class="w-full h-full object-contain">
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
                    <img src="/images/logoSOBOA.png.webp" alt="App Icon" class="w-12 h-12 object-contain">
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
                 targetDate: new Date('{{ ($worldCupStart ?? \Carbon\Carbon::parse(config('game.world_cup_start', '2026-06-11 19:00:00')))->format('Y-m-d\TH:i:s') }}').getTime(),
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
        <div class="relative z-[10] text-center px-6 md:px-8 py-12 md:py-16 max-w-5xl mx-auto">
            @if($siteSettings && $siteSettings->tournamentWinner)
            <!-- Tournament Winner Celebration -->
            <div class="animate-fade-in-down">
                <!-- Trophy Animation -->
                <div class="text-8xl md:text-9xl mb-6 animate-bounce-slow">
                    🏆
                </div>

                <!-- Winner Flag -->
                @if($siteSettings->tournamentWinner->flag_url_80)
                <div class="w-32 h-32 md:w-40 md:h-40 mx-auto mb-6 rounded-full overflow-hidden border-4 border-yellow-400 shadow-2xl animate-pulse-soft">
                    <img src="{{ $siteSettings->tournamentWinner->flag_url_80 }}"
                         alt="{{ $siteSettings->tournamentWinner->name }}"
                         class="w-full h-full object-cover">
                </div>
                @endif

                <!-- Congratulations Message -->
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-black text-white mb-4 leading-tight uppercase animate-fade-in-up" style="font-family: 'Montserrat', sans-serif; letter-spacing: -0.02em;">
                    <span class="inline-block">Bravo</span><br>
                    <span class="gradient-text inline-block text-5xl md:text-7xl lg:text-8xl" style="text-shadow: 0 0 30px rgba(255, 215, 0, 0.5);">{{ $siteSettings->tournamentWinner->name }} !</span>
                </h1>

                <p class="text-2xl md:text-3xl text-soboa-orange font-black mb-6 animate-pulse">
                    Champion du Tournoi !
                </p>

                <p class="text-xl md:text-2xl text-white/80 mb-10 max-w-2xl mx-auto font-medium leading-relaxed">
                    Merci a tous les participants pour cette aventure incroyable !<br>
                    Consultez le classement final et decouvrez les gagnants.
                </p>

                <!-- CTA for Leaderboard -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
                    <a href="/leaderboard"
                        class="inline-flex items-center justify-center gap-2 bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-4 px-8 rounded-full shadow-xl hover:shadow-2xl transition-all transform hover:scale-105 text-lg orange-glow">
                        Voir le classement final
                    </a>
                    <a href="/mes-pronostics"
                        class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white font-bold py-4 px-8 rounded-full border-2 border-white/30 transition-all">
                        Mes pronostics
                    </a>
                </div>

                <!-- Branding Badge -->
                <div class="inline-flex flex-col items-center bg-white/10 backdrop-blur-md rounded-2xl px-8 py-4 border border-white/20 shadow-2xl">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center p-2 mb-2 shadow-inner overflow-hidden">
                        <img src="/images/logoSOBOA.png.webp" alt="SOBOA FOOT TIME" class="w-full h-full object-cover rounded-full">
                    </div>
                    <span class="text-white font-black text-xl tracking-tighter uppercase leading-none">SOBOA FOOT TIME</span>
                    <span class="text-soboa-orange font-bold text-xs tracking-[0.2em] uppercase mt-1 opacity-90">Le gout de notre victoire</span>
                </div>
            </div>
            @elseif($siteSettings && $siteSettings->tournament_ended)
            <!-- Tournament Ended (no winner yet) -->
            <div class="animate-fade-in-down">
                <!-- Branding Badge -->
                <div class="inline-flex flex-col items-center bg-white/10 backdrop-blur-md rounded-2xl px-8 py-4 mb-8 border border-white/20 shadow-2xl">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center p-3 mb-3 shadow-inner overflow-hidden">
                        <img src="/images/logoSOBOA.png.webp" alt="SOBOA FOOT TIME" class="w-full h-full object-cover rounded-full">
                    </div>
                    <span class="text-white font-black text-3xl md:text-4xl tracking-tighter uppercase leading-none animate-glow hero-title">SOBOA FOOT TIME</span>
                    <span class="text-soboa-orange font-bold text-sm md:text-base tracking-[0.3em] uppercase mt-2 opacity-90 animate-pulse-soft">Le gout de notre victoire</span>
                </div>

                <!-- Tournament Ended Message -->
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-black text-white mb-6 leading-tight uppercase animate-fade-in-up" style="font-family: 'Montserrat', sans-serif; letter-spacing: -0.02em;">
                    <span class="inline-block">Tournoi</span><br>
                    <span class="gradient-text inline-block text-5xl md:text-7xl lg:text-8xl" style="text-shadow: 0 0 30px rgba(255, 215, 0, 0.5);">Termine !</span>
                </h1>

                <p class="text-xl md:text-2xl text-white/80 mb-10 max-w-2xl mx-auto font-medium leading-relaxed">
                    Merci a tous les participants !<br>
                    Consultez le classement final pour decouvrir les gagnants.
                </p>

                <!-- CTA for Leaderboard -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
                    <a href="/leaderboard"
                        class="inline-flex items-center justify-center gap-2 bg-soboa-orange hover:bg-soboa-orange-dark text-black font-bold py-4 px-8 rounded-full shadow-xl hover:shadow-2xl transition-all transform hover:scale-105 text-lg orange-glow">
                        Voir le classement final
                    </a>
                    @if(session('user_id'))
                    <a href="/mes-pronostics"
                        class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white font-bold py-4 px-8 rounded-full border-2 border-white/30 transition-all">
                        Mes pronostics
                    </a>
                    @endif
                </div>
            </div>
            @else
            <!-- Normal Hero Content -->
            <!-- Branding Badge with Animation -->
            <div
                class="inline-flex flex-col items-center bg-white/10 backdrop-blur-md rounded-2xl px-8 py-4 mb-8 border border-white/20 shadow-2xl animate-fade-in-down">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center p-3 mb-3 shadow-inner overflow-hidden">
                    <img src="/images/logoSOBOA.png.webp" alt="SOBOA FOOT TIME" class="w-full h-full object-cover rounded-full">
                </div>
                <span
                    class="text-white font-black text-3xl md:text-4xl tracking-tighter uppercase leading-none animate-glow hero-title">SOBOA FOOT TIME</span>
                <span
                    class="text-soboa-orange font-bold text-sm md:text-base tracking-[0.3em] uppercase mt-2 opacity-90 animate-pulse-soft">Le
                    goût de notre victoire</span>
            </div>

            <!-- Main Heading with Impact Typography -->
            <h1 class="text-5xl md:text-7xl lg:text-8xl font-black text-white mb-6 leading-tight uppercase animate-fade-in-up" style="font-family: 'Montserrat', sans-serif; letter-spacing: -0.02em;">
                <span class="inline-block animate-slide-right">Pronostiquez</span><br>
                <span class="gradient-text inline-block animate-slide-left text-6xl md:text-8xl lg:text-9xl" style="text-shadow: 0 0 30px rgba(255, 215, 0, 0.5);">& Gagnez!</span>
            </h1>

            <p class="text-xl md:text-2xl text-white/80 mb-10 max-w-2xl mx-auto font-medium leading-relaxed">
                Tentez de gagner <span class="text-soboa-orange font-bold underline underline-offset-4">un billet
                    d'avion pour la finale</span> et pleins d'autres lots !
            </p>

            <!-- Countdown Timer -->
            <div class="mb-10">
                @if($nextMatch && $nextMatch->homeTeam && $nextMatch->awayTeam)
                    @php
                        $timeUntilMatch = $nextMatch->match_date->diffInHours(now());
                        $isToday = $nextMatch->match_date->isToday();
                        $isTomorrow = $nextMatch->match_date->isTomorrow();
                    @endphp
                    
                    <p class="text-soboa-orange font-bold text-sm uppercase tracking-widest mb-4">
                        @if($isToday)
                            🔥 Match aujourd'hui - {{ $nextMatch->homeTeam->name }} vs {{ $nextMatch->awayTeam->name }} 🔥
                        @elseif($isTomorrow)
                            ⚡ Match demain - {{ $nextMatch->homeTeam->name }} vs {{ $nextMatch->awayTeam->name }} ⚡
                        @elseif($timeUntilMatch <= 72)
                            🚀 Prochain match - {{ $nextMatch->homeTeam->name }} vs {{ $nextMatch->awayTeam->name }} 🚀
                        @else
                            Prochain match - {{ $nextMatch->homeTeam->name }} vs {{ $nextMatch->awayTeam->name }}
                        @endif
                    </p>
                    
                    <!-- Teams flags display -->
                    <div class="flex items-center justify-center gap-6 mb-6">
                        <div class="text-center">
                            <div class="team-flag w-16 h-16 md:w-20 md:h-20 rounded-full overflow-hidden border-4 border-white/20 shadow-2xl mb-2 mx-auto">
                                <img src="{{ $nextMatch->homeTeam->flag_url_80 }}" 
                                     alt="{{ $nextMatch->homeTeam->name }}" 
                                     class="w-full h-full object-cover">
                            </div>
                            <span class="text-white font-semibold text-xs md:text-sm">{{ $nextMatch->homeTeam->name }}</span>
                        </div>
                        
                        <div class="vs-text text-white font-bold text-2xl md:text-3xl">VS</div>
                        
                        <div class="text-center">
                            <div class="team-flag w-16 h-16 md:w-20 md:h-20 rounded-full overflow-hidden border-4 border-white/20 shadow-2xl mb-2 mx-auto">
                                <img src="{{ $nextMatch->awayTeam->flag_url_80 }}" 
                                     alt="{{ $nextMatch->awayTeam->name }}" 
                                     class="w-full h-full object-cover">
                            </div>
                            <span class="text-white font-semibold text-xs md:text-sm">{{ $nextMatch->awayTeam->name }}</span>
                        </div>
                    </div>
                    
                    <div class="text-white/60 text-xs md:text-sm mb-4">
                        {{ $nextMatch->match_date->format('d M Y à H:i') }}
                        @if($nextMatch->phase === 'group_stage') 
                            • Groupe {{ $nextMatch->group_name }}
                        @else
                            • {{ ucfirst(str_replace('_', ' ', $nextMatch->phase)) }}
                        @endif
                    </div>
                @else
                    <p class="text-soboa-orange font-bold text-sm uppercase tracking-widest mb-4">
                        Prochain match - À définir
                    </p>
                @endif
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
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
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
            @endif
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-12 md:bottom-16 left-1/2 -translate-x-1/2 animate-bounce">
            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3">
                </path>
            </svg>
        </div>
    </section>

    @if(!$siteSettings || !$siteSettings->tournament_ended)
    <!-- Upcoming Matches Section (Hidden when tournament ended) -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
                <div>
                    <span class="text-soboa-orange font-bold text-sm uppercase tracking-widest">SOBOA FOOT TIME | LE GOÛT DE
                        NOTRE VICTOIRE</span>
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @forelse($upcomingMatches as $match)
                    <x-match-card :match="$match" />
                @empty
                    <div class="col-span-full text-center py-16 bg-white rounded-2xl shadow">
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
    @endif

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

    @if(!$siteSettings || !$siteSettings->tournament_ended)
    <!-- How It Works Section (Hidden when tournament ended) -->
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
                    <p class="text-gray-600">Prédisez les scores des matchs.</p>
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
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-center">
                    <div class="bg-white/10 rounded-xl p-4">
                        <span class="text-3xl font-black text-soboa-orange">+1</span>
                        <p class="text-sm text-white/80 mt-2">🔑 Connexion/jour</p>
                    </div>
                    <div class="bg-white/10 rounded-xl p-4">
                        <span class="text-3xl font-black text-soboa-orange">+1</span>
                        <p class="text-sm text-white/80 mt-2">⚽ Participation</p>
                    </div>
                    <div class="bg-white/10 rounded-xl p-4">
                        <span class="text-3xl font-black text-soboa-orange">+3</span>
                        <p class="text-sm text-white/80 mt-2">🎯 Bon vainqueur</p>
                    </div>
                    <div class="bg-white/10 rounded-xl p-4">
                        <span class="text-3xl font-black text-soboa-orange">+3</span>
                        <p class="text-sm text-white/80 mt-2">🏆 Score exact</p>
                    </div>
                    <div class="bg-white/10 rounded-xl p-4">
                        <span class="text-3xl font-black text-soboa-orange">+4</span>
                        <p class="text-sm text-white/80 mt-2">📍 Visite lieu</p>
                    </div>
                </div>
                <p class="text-center text-white/60 text-sm mt-4">Maximum 7 points par match + 4 points bonus par visite + 1 point par connexion quotidienne</p>
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
    @endif

    @if($siteSettings && $siteSettings->tournamentWinner)
    <!-- Confetti Script for Winner Celebration -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('confetti-canvas');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const confettiColors = ['#FF6600', '#003399', '#FFD700', '#00A651', '#FFFFFF', '#FF4444'];
        const confettiCount = 200;
        const confetti = [];

        // Initialize confetti particles
        for (let i = 0; i < confettiCount; i++) {
            confetti.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height - canvas.height,
                r: Math.random() * 6 + 4,
                d: Math.random() * confettiCount,
                color: confettiColors[Math.floor(Math.random() * confettiColors.length)],
                tilt: Math.floor(Math.random() * 10) - 10,
                tiltAngleIncremental: Math.random() * 0.07 + 0.05,
                tiltAngle: 0
            });
        }

        function drawConfetti() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            confetti.forEach((p, i) => {
                ctx.beginPath();
                ctx.lineWidth = p.r / 2;
                ctx.strokeStyle = p.color;
                ctx.moveTo(p.x + p.tilt + p.r / 4, p.y);
                ctx.lineTo(p.x + p.tilt, p.y + p.tilt + p.r / 4);
                ctx.stroke();
            });
            updateConfetti();
        }

        function updateConfetti() {
            confetti.forEach((p, i) => {
                p.tiltAngle += p.tiltAngleIncremental;
                p.y += (Math.cos(p.d) + 3 + p.r / 2) / 2;
                p.x += Math.sin(p.d);
                p.tilt = Math.sin(p.tiltAngle) * 15;

                if (p.y > canvas.height) {
                    confetti[i] = {
                        x: Math.random() * canvas.width,
                        y: -20,
                        r: p.r,
                        d: p.d,
                        color: p.color,
                        tilt: p.tilt,
                        tiltAngleIncremental: p.tiltAngleIncremental,
                        tiltAngle: p.tiltAngle
                    };
                }
            });
        }

        let animationRunning = true;
        function animate() {
            if (animationRunning) {
                drawConfetti();
                requestAnimationFrame(animate);
            }
        }
        animate();

        // Stop confetti after 10 seconds
        setTimeout(() => {
            animationRunning = false;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }, 10000);

        // Handle window resize
        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });
    });
    </script>
    @endif

</x-layouts.app>