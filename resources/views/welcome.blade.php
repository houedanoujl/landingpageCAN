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
        <div x-show="showAgeModal" x-cloak
            x-transition:enter="transition ease-out duration-base"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            class="modal-backdrop"
            role="dialog" aria-modal="true" aria-labelledby="age-modal-title">

            <div x-show="showAgeModal"
                x-transition:enter="transition ease-out duration-base"
                x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                class="modal-panel p-8 text-center relative">

                <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-soboa-orange via-soboa-blue to-soboa-orange"></div>

                <div class="w-24 h-24 mx-auto mb-6 flex items-center justify-center bg-white rounded-full shadow-elev-2 p-2">
                    <img src="/images/logoSOBOA.png.webp" alt="SOBOA" class="w-full h-full object-contain">
                </div>

                <h2 id="age-modal-title" class="text-3xl font-black text-soboa-blue mb-2">Êtes-vous majeur ?</h2>
                <p class="text-gray-500 mb-8">L'accès à ce site est réservé aux personnes de 18 ans et plus.</p>

                <div class="flex flex-col gap-3">
                    <button @click="confirmAge()" class="btn btn-primary btn-lg btn-block">
                        <span>Oui, j'ai plus de 18 ans</span>
                        <i data-lucide="check" class="w-5 h-5"></i>
                    </button>

                    <button @click="denyAge()" class="btn btn-ghost btn-lg btn-block">
                        Non, je suis mineur
                    </button>
                </div>

                <p class="text-xs text-gray-400 mt-6 pt-6 border-t border-gray-100">
                    L'abus d'alcool est dangereux pour la santé. À consommer avec modération.
                </p>
            </div>
        </div>

        <!-- PWA INSTALL MODAL -->
        <div x-show="showPwaModal" x-cloak
            x-transition:enter="transition ease-out duration-base transform"
            x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
            class="fixed bottom-0 left-0 right-0 z-modal-backdrop p-4 flex justify-center pointer-events-none"
            role="dialog" aria-modal="false" aria-labelledby="pwa-modal-title">

            <div class="bg-white rounded-2xl shadow-elev-modal max-w-md w-full p-5 pointer-events-auto border border-gray-100 flex flex-col sm:flex-row items-center gap-4 relative">
                <button @click="dismissPwa()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 p-2 rounded-full" aria-label="Fermer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>

                <div class="flex-shrink-0 bg-soboa-blue/10 p-3 rounded-xl">
                    <img src="/images/logoSOBOA.png.webp" alt="" class="w-12 h-12 object-contain">
                </div>

                <div class="flex-1 text-center sm:text-left">
                    <h3 id="pwa-modal-title" class="font-bold text-soboa-blue text-lg">Installez l'application</h3>
                    <p class="text-sm text-gray-500">Accédez plus rapidement aux pronostics et résultats.</p>
                </div>

                <button @click="installPwa()" class="btn btn-blue btn-md whitespace-nowrap">
                    Installer
                </button>
            </div>
        </div>

    </div>

    <!-- Hero Section - Grande Fête du Foot Africain Celebration -->
    <section class="relative min-h-[calc(100dvh-150px)] flex items-center justify-center overflow-hidden" x-data="{
                 countdown: { days: 0, hours: 0, minutes: 0, seconds: 0 },
                 targetDate: new Date('{{ ($nextMatch && $nextMatch->match_date ? $nextMatch->match_date : ($worldCupStart ?? \Carbon\Carbon::parse(config('game.world_cup_start', '2026-06-11 19:00:00'))))->format('Y-m-d\TH:i:s') }}Z').getTime(),
                 scrollY: 0,
                 parallaxOffset: 0,
                 init() {
                     this.updateCountdown();
                     setInterval(() => this.updateCountdown(), 1000);
                     const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                     if (!reduceMotion) {
                         window.addEventListener('scroll', () => {
                             this.scrollY = window.scrollY;
                             this.parallaxOffset = this.scrollY * 0.5;
                         }, { passive: true });
                     }
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
            <div class="absolute inset-0 bg-gradient-to-b from-soboa-text-dark/75 via-soboa-blue/55 to-soboa-text-dark/85"></div>
        </div>

        <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-soboa-text-dark/80 to-transparent z-[5]" aria-hidden="true"></div>

        <!-- Content -->
        <div class="relative z-[10] text-center px-5 md:px-8 py-8 md:py-14 max-w-5xl mx-auto">
            @if($siteSettings && $siteSettings->tournamentWinner)
            <!-- Tournament Winner Celebration -->
            <div class="animate-fade-in-down">
                <!-- Trophy Animation -->
                <div class="flex justify-center mb-6 animate-bounce-slow">
                    <i data-lucide="trophy" class="w-28 h-28 md:w-36 md:h-36 text-soboa-orange" stroke-width="1.5"></i>
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
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-black text-white mb-4 leading-tight uppercase animate-fade-in-up [text-wrap:balance]" style="font-family: 'Montserrat', sans-serif;">
                    <span class="inline-block">Bravo</span><br>
                    <span class="gradient-text inline-block text-5xl md:text-7xl lg:text-8xl" style="text-shadow: 0 0 30px rgba(241, 134, 45, 0.55);">{{ $siteSettings->tournamentWinner->name }} !</span>
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
                        class="btn btn-primary btn-lg btn-pill orange-glow">
                        Voir le classement final
                    </a>
                    <a href="/mes-pronostics"
                        class="btn btn-ghost-light btn-lg btn-pill">
                        Mes pronostics
                    </a>
                </div>

                <!-- Branding Badge -->
                <div class="inline-flex flex-col items-center bg-white/10 backdrop-blur-md rounded-2xl px-8 py-4 border border-white/20 shadow-2xl">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center p-2 mb-2 shadow-inner overflow-hidden">
                        <img src="/images/logoSOBOA.png.webp" alt="SOBOA FOOT TIME" class="w-full h-full object-cover rounded-full">
                    </div>
                    <span class="text-white font-black text-xl tracking-tighter uppercase leading-none">SOBOA FOOT TIME</span>
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
                </div>

                <!-- Tournament Ended Message -->
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-black text-white mb-6 leading-tight uppercase animate-fade-in-up [text-wrap:balance]" style="font-family: 'Montserrat', sans-serif;">
                    <span class="inline-block">Tournoi</span><br>
                    <span class="gradient-text inline-block text-5xl md:text-7xl lg:text-8xl" style="text-shadow: 0 0 30px rgba(241, 134, 45, 0.55);">Termine !</span>
                </h1>

                <p class="text-xl md:text-2xl text-white/80 mb-10 max-w-2xl mx-auto font-medium leading-relaxed">
                    Merci a tous les participants !<br>
                    Consultez le classement final pour decouvrir les gagnants.
                </p>

                <!-- CTA for Leaderboard -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
                    <a href="/leaderboard"
                        class="btn btn-primary btn-lg btn-pill orange-glow">
                        Voir le classement final
                    </a>
                    @if(session('user_id'))
                    <a href="/mes-pronostics"
                        class="btn btn-ghost-light btn-lg btn-pill">
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
                    class="text-white font-black text-3xl md:text-4xl tracking-tighter uppercase leading-none">SOBOA FOOT TIME</span>
            </div>

            <!-- Main Heading with Impact Typography -->
            <h1 class="text-4xl xs:text-5xl md:text-6xl lg:text-7xl font-black text-white mb-6 leading-tight uppercase animate-fade-in-up [text-wrap:balance]" style="font-family: 'Montserrat', sans-serif;">
                <span class="inline-block">Pronostiquez</span><br>
                <span class="gradient-text inline-block text-5xl xs:text-6xl md:text-7xl lg:text-8xl" style="text-shadow: 0 0 30px rgba(241, 134, 45, 0.55);">et gagnez</span>
            </h1>

            @if(!empty($siteSettings?->hero_promo_text))
            <p class="text-xl md:text-2xl text-white/80 mb-10 max-w-2xl mx-auto font-medium leading-relaxed">
                {!! nl2br(e($siteSettings->hero_promo_text)) !!}
            </p>
            @endif

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
                            Match aujourd'hui - {{ $nextMatch->home_name_fr }} vs {{ $nextMatch->away_name_fr }}
                        @elseif($isTomorrow)
                            Match demain - {{ $nextMatch->home_name_fr }} vs {{ $nextMatch->away_name_fr }}
                        @elseif($timeUntilMatch <= 72)
                            Prochain match - {{ $nextMatch->home_name_fr }} vs {{ $nextMatch->away_name_fr }}
                        @else
                            Prochain match - {{ $nextMatch->home_name_fr }} vs {{ $nextMatch->away_name_fr }}
                        @endif
                    </p>
                    
                    <!-- Teams flags display -->
                    <div class="flex items-center justify-center gap-6 mb-6">
                        <div class="text-center">
                            <div class="team-flag w-16 h-16 md:w-20 md:h-20 rounded-full overflow-hidden border-4 border-white/20 shadow-2xl mb-2 mx-auto">
                                <img src="{{ $nextMatch->homeTeam->flag_url_80 }}" 
                                     alt="{{ $nextMatch->home_name_fr }}" 
                                     class="w-full h-full object-cover">
                            </div>
                            <span class="text-white font-semibold text-xs md:text-sm">{{ $nextMatch->home_name_fr }}</span>
                        </div>
                        
                        <div class="vs-text text-white font-bold text-2xl md:text-3xl">VS</div>
                        
                        <div class="text-center">
                            <div class="team-flag w-16 h-16 md:w-20 md:h-20 rounded-full overflow-hidden border-4 border-white/20 shadow-2xl mb-2 mx-auto">
                                <img src="{{ $nextMatch->awayTeam->flag_url_80 }}" 
                                     alt="{{ $nextMatch->away_name_fr }}" 
                                     class="w-full h-full object-cover">
                            </div>
                            <span class="text-white font-semibold text-xs md:text-sm">{{ $nextMatch->away_name_fr }}</span>
                        </div>
                    </div>
                    
                    <div class="text-white/60 text-xs md:text-sm mb-4">
                        {{ $nextMatch->match_date->format('d M Y à H:i') }} <abbr title="Heure GMT (Temps Universel)" class="no-underline opacity-80">GMT</abbr>
                        @if($nextMatch->phase === 'group_stage')
                            <span aria-hidden="true">•</span> Groupe {{ $nextMatch->group_name }}
                        @else
                            <span aria-hidden="true">•</span> {{ ucfirst(str_replace('_', ' ', $nextMatch->phase)) }}
                        @endif
                    </div>
                @else
                    <p class="text-soboa-orange font-bold text-sm uppercase tracking-widest mb-4">
                        Prochain match - À définir
                    </p>
                @endif
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 md:gap-4 max-w-2xl mx-auto">
                    <div
                        class="bg-white/10 backdrop-blur-sm rounded-xl p-4 md:p-5 border border-white/20">
                        <span class="text-3xl md:text-5xl font-black text-white block tabular-nums" x-text="countdown.days">00</span>
                        <span class="text-white/60 text-xs md:text-sm font-semibold uppercase">Jours</span>
                    </div>
                    <div
                        class="bg-white/10 backdrop-blur-sm rounded-xl p-4 md:p-5 border border-white/20">
                        <span class="text-3xl md:text-5xl font-black text-white block tabular-nums"
                            x-text="countdown.hours">00</span>
                        <span class="text-white/60 text-xs md:text-sm font-semibold uppercase">Heures</span>
                    </div>
                    <div
                        class="bg-white/10 backdrop-blur-sm rounded-xl p-4 md:p-5 border border-white/20">
                        <span class="text-3xl md:text-5xl font-black text-white block tabular-nums"
                            x-text="countdown.minutes">00</span>
                        <span class="text-white/60 text-xs md:text-sm font-semibold uppercase">Minutes</span>
                    </div>
                    <div
                        class="bg-white/10 backdrop-blur-sm rounded-xl p-4 md:p-5 border border-white/20">
                        <span class="text-3xl md:text-5xl font-black text-soboa-orange block tabular-nums"
                            x-text="countdown.seconds">00</span>
                        <span class="text-white/60 text-xs md:text-sm font-semibold uppercase">Secondes</span>
                    </div>
                </div>
            </div>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
                @if(session('user_id'))
                    <a href="/matches"
                        class="btn btn-primary btn-lg btn-pill orange-glow">
                        Faire un pronostic
                    </a>
                    <a href="/dashboard"
                        class="btn btn-ghost-light btn-lg btn-pill">
                        Mon tableau de bord
                    </a>
                @else
                    <a href="/login"
                        class="btn btn-primary btn-lg btn-pill orange-glow">
                        Jouer et gagner
                    </a>
                    <a href="/map"
                        class="btn btn-ghost-light btn-lg btn-pill">
                        Trouver un lieu
                    </a>
                @endif
            </div>
            @endif
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-12 md:bottom-16 left-1/2 -translate-x-1/2 animate-bounce" aria-hidden="true">
            <i data-lucide="chevron-down" class="w-8 h-8 text-white/60"></i>
        </div>
    </section>

    @if(!$siteSettings || !$siteSettings->tournament_ended)
    <!-- À gagner : lots à pronostiquer (Hidden when tournament ended) -->
    <section class="relative py-16 md:py-20 overflow-hidden bg-soboa-text-dark">
        <div class="absolute inset-0 z-0" aria-hidden="true">
            <img src="{{ asset('images/sen.webp') }}" alt="" class="w-full h-full object-cover opacity-25" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-b from-soboa-text-dark/90 via-soboa-blue/70 to-soboa-text-dark/95"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4">
            <!-- Titre -->
            <div class="text-center mb-12">
                <h2 class="flex items-center justify-center gap-3 text-4xl md:text-5xl font-black text-soboa-orange uppercase tracking-tight">
                    <i data-lucide="gift" class="w-9 h-9 md:w-11 md:h-11" stroke-width="2"></i>
                    À gagner
                </h2>
                <p class="text-white/90 text-lg mt-3">Des prix incroyables pour célébrer le match !</p>
                <div class="w-24 h-1 bg-soboa-orange rounded-full mx-auto mt-4"></div>
            </div>

            <!-- Lots -->
            <div class="bg-white/10 backdrop-blur-md rounded-3xl border border-white/15 p-6 md:p-10 mb-10">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    <div class="flex justify-center">
                        <img src="{{ asset('images/lots/lots.webp') }}"
                             alt="Lots à gagner : Samsung Galaxy S25, PlayStation 5 et bons d'achat SOBOA"
                             class="w-full max-w-md lg:max-w-lg object-contain drop-shadow-2xl"
                             loading="lazy">
                    </div>
                    <div class="space-y-4">
                        @php
                            $lots = [
                                ['rank' => '1er', 'badge' => 'bg-blue-600', 'name' => 'Samsung Galaxy S25', 'cat' => 'Smartphone', 'catColor' => 'text-cyan-300'],
                                ['rank' => '2e', 'badge' => 'bg-purple-600', 'name' => 'PlayStation 5', 'cat' => 'Console', 'catColor' => 'text-purple-300'],
                                ['rank' => '3e', 'badge' => 'bg-green-600', 'name' => "Bons d'achat", 'cat' => 'À dépenser', 'catColor' => 'text-green-300'],
                            ];
                        @endphp
                        @foreach($lots as $lot)
                            <div class="flex items-center gap-4 bg-white/5 rounded-2xl border border-white/10 px-5 py-4 transition-transform duration-base hover:translate-x-1">
                                <span class="{{ $lot['badge'] }} text-white text-xs font-black uppercase leading-tight rounded-xl px-3 py-2 text-center shadow-elev-2 shrink-0">
                                    {{ $lot['rank'] }}<br>Prix
                                </span>
                                <div>
                                    <h3 class="text-white font-black text-xl md:text-2xl">{{ $lot['name'] }}</h3>
                                    <p class="{{ $lot['catColor'] }} font-bold text-xs uppercase tracking-widest mt-0.5">{{ $lot['cat'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Prochain match -->
            @if(isset($nextMatch) && $nextMatch)
                @php
                    $nextHomeName = \App\Models\Team::fr($nextMatch->homeTeam?->name ?? $nextMatch->team_a);
                    $nextAwayName = \App\Models\Team::fr($nextMatch->awayTeam?->name ?? $nextMatch->team_b);
                    $nextDate = $nextMatch->match_date;
                    $dateLabel = $nextDate->isToday() ? 'Match aujourd\'hui' : ($nextDate->isTomorrow() ? 'Match demain' : 'Match le ' . $nextDate->translatedFormat('d M'));
                @endphp
                <a href="{{ route('matches') }}#match-{{ $nextMatch->id }}"
                   class="block rounded-2xl border border-soboa-orange/60 bg-white/5 backdrop-blur-md px-6 py-6 hover:bg-white/10 transition-colors focus:outline-none focus:ring-2 focus:ring-soboa-orange">
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-8">
                        <div class="flex items-center gap-3">
                            @if($nextMatch->homeTeam?->flag_url_80)
                                <img src="{{ $nextMatch->homeTeam->flag_url_80 }}" alt="" class="w-10 h-10 rounded-full object-cover ring-2 ring-white/30">
                            @endif
                            <span class="text-white font-black text-lg uppercase">{{ $nextHomeName }}</span>
                        </div>
                        <span class="text-soboa-orange font-black text-2xl italic">VS</span>
                        <div class="flex items-center gap-3">
                            @if($nextMatch->awayTeam?->flag_url_80)
                                <img src="{{ $nextMatch->awayTeam->flag_url_80 }}" alt="" class="w-10 h-10 rounded-full object-cover ring-2 ring-white/30">
                            @endif
                            <span class="text-white font-black text-lg uppercase">{{ $nextAwayName }}</span>
                        </div>
                    </div>
                    <p class="flex items-center justify-center gap-2 text-white/80 font-bold mt-4 text-sm">
                        <i data-lucide="calendar" class="w-4 h-4 text-soboa-orange"></i>
                        <span class="uppercase">{{ $dateLabel }}</span>
                        <span class="text-soboa-orange">•</span>
                        {{ $nextDate->format('H:i') }}
                    </p>
                </a>
            @endif
        </div>
    </section>

    <!-- Upcoming Matches Section (Hidden when tournament ended) -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
                <div>
                    <span class="text-soboa-orange font-bold text-sm uppercase tracking-widest">SOBOA FOOT TIME</span>
                    <div class="flex items-center gap-2 mt-2">
                        <h2 class="text-3xl md:text-4xl font-black text-soboa-blue">Prochains matchs</h2>
                    </div>
                </div>
                <a href="/matches"
                    class="text-soboa-orange font-bold hover:underline mt-4 md:mt-0 inline-flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-soboa-orange rounded-sm">
                    Voir tous les matchs
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </a>
            </div>

            @if($upcomingMatches->isEmpty())
                <div class="text-center py-section-md bg-white rounded-2xl shadow-elev-1">
                    <div class="w-20 h-20 bg-soboa-orange/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="calendar-x" class="w-9 h-9 text-soboa-orange"></i>
                    </div>
                    <p class="text-soboa-text-dark font-semibold">Aucun match programmé pour le moment.</p>
                    <p class="text-gray-500 text-sm mt-2">Revenez bientôt pour voir le calendrier complet.</p>
                </div>
            @else
                {{-- Carousel : 1 match par vue sur mobile, 2 sur desktop (scroll-snap natif) --}}
                <div x-data="matchesCarousel()" x-init="init()" class="relative">
                    <div x-ref="track"
                         @scroll.debounce.100ms="syncPage()"
                         class="flex gap-6 overflow-x-auto snap-x snap-mandatory scroll-smooth scrollbar-hide pb-2"
                         aria-label="Carousel des prochains matchs" tabindex="0"
                         @keydown.arrow-left.prevent="prev()" @keydown.arrow-right.prevent="next()">
                        @foreach($upcomingMatches as $match)
                            <div class="snap-start shrink-0 w-full lg:w-[calc(50%-12px)]">
                                <x-match-card :match="$match" :trend="$predictionTrends[$match->id] ?? null" />
                            </div>
                        @endforeach
                    </div>

                    {{-- Flèches --}}
                    <button type="button" @click="prev()" x-show="page > 0" x-cloak
                            class="hidden md:flex absolute -left-5 top-1/2 -translate-y-1/2 w-11 h-11 bg-white rounded-full shadow-elev-2 items-center justify-center text-soboa-blue hover:bg-soboa-blue hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-soboa-blue z-10"
                            aria-label="Matchs précédents">
                        <i data-lucide="chevron-left" class="w-6 h-6"></i>
                    </button>
                    <button type="button" @click="next()" x-show="page < pageCount - 1" x-cloak
                            class="hidden md:flex absolute -right-5 top-1/2 -translate-y-1/2 w-11 h-11 bg-white rounded-full shadow-elev-2 items-center justify-center text-soboa-blue hover:bg-soboa-blue hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-soboa-blue z-10"
                            aria-label="Matchs suivants">
                        <i data-lucide="chevron-right" class="w-6 h-6"></i>
                    </button>

                    {{-- Points de pagination --}}
                    <div class="flex justify-center gap-2 mt-5" x-show="pageCount > 1">
                        <template x-for="i in pageCount" :key="i">
                            <button type="button" @click="goTo(i - 1)"
                                    class="h-2.5 rounded-full transition-all duration-base focus:outline-none focus:ring-2 focus:ring-soboa-orange"
                                    :class="page === i - 1 ? 'w-7 bg-soboa-orange' : 'w-2.5 bg-gray-300 hover:bg-gray-400'"
                                    :aria-label="'Aller à la page ' + i"></button>
                        </template>
                    </div>
                </div>

                <script>
                    function matchesCarousel() {
                        return {
                            page: 0,
                            pageCount: 1,
                            init() {
                                this.computePages();
                                window.addEventListener('resize', () => this.computePages());
                            },
                            // 1 carte visible par page sur mobile, 2 sur desktop (lg = 1024px)
                            perPage() {
                                return window.matchMedia('(min-width: 1024px)').matches ? 2 : 1;
                            },
                            computePages() {
                                const total = this.$refs.track.children.length;
                                this.pageCount = Math.max(1, Math.ceil(total / this.perPage()));
                                this.page = Math.min(this.page, this.pageCount - 1);
                            },
                            pageWidth() {
                                return this.$refs.track.clientWidth + 24; /* gap-6 */
                            },
                            goTo(p) {
                                this.page = Math.max(0, Math.min(p, this.pageCount - 1));
                                this.$refs.track.scrollTo({ left: this.page * this.pageWidth(), behavior: 'smooth' });
                            },
                            prev() { this.goTo(this.page - 1); },
                            next() { this.goTo(this.page + 1); },
                            syncPage() {
                                this.page = Math.round(this.$refs.track.scrollLeft / this.pageWidth());
                            },
                        };
                    }
                </script>
            @endif
        </div>
    </section>
    @endif

    <!-- Leaderboard Section -->
    <section class="py-16 bg-soboa-text-dark text-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
                <div>
                    <span class="text-soboa-orange font-bold text-sm uppercase tracking-widest">Qui sera le meilleur ?</span>
                    <h2 class="text-3xl md:text-4xl font-black text-white mt-2">Classement</h2>
                </div>
                @if($competitionStarted ?? true)
                <a href="/leaderboard"
                    class="text-white font-bold hover:text-soboa-orange mt-4 md:mt-0 inline-flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-soboa-orange rounded-sm transition-colors">
                    Classement complet
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </a>
                @endif
            </div>

            @if(!($competitionStarted ?? true))
            {{-- Classement masqué tant que le premier match n'est pas terminé.
                 Les points s'accumulent déjà côté serveur. --}}
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-10 text-center border border-white/10">
                <div class="text-5xl mb-4">🔒</div>
                <h3 class="font-black text-white text-2xl mb-2">Classement dévoilé après le premier match</h3>
                <p class="text-white/70 max-w-xl mx-auto">
                    Pronostiquez dès maintenant : vos points s'accumulent déjà !
                    Le classement s'affichera dès la fin du premier match de la compétition.
                </p>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @forelse($topUsers as $index => $user)
                    <div
                        class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-center border border-white/10 transition-transform duration-base hover:-translate-y-1 {{ $index === 0 ? 'ring-2 ring-soboa-orange bg-white/15' : '' }}">
                        <div class="text-3xl mb-2 font-black text-soboa-orange tabular-nums">
                            {{ $index + 1 }}
                        </div>
                        <div
                            class="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl font-bold text-white ring-1 ring-white/20">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                        <h3 class="font-bold text-white text-lg truncate">{{ $user->name }}</h3>
                        <p class="text-white/70 font-black text-xl tabular-nums">{{ $user->points_total }} pts</p>
                    </div>
                @empty
                    <div class="col-span-5 text-center py-10">
                        <p class="text-white/60">Aucun joueur inscrit pour le moment.</p>
                        <a href="/login" class="text-soboa-orange font-bold hover:underline">Soyez le premier.</a>
                    </div>
                @endforelse
            </div>
            @endif
        </div>
    </section>

    @if(!$siteSettings || !$siteSettings->tournament_ended)
    <!-- How It Works Section (Hidden when tournament ended) -->
    <section class="py-16 md:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <span class="text-soboa-orange font-bold text-sm uppercase tracking-widest">Simple et clair</span>
                <h2 class="text-3xl md:text-5xl font-black text-soboa-blue mt-2">Comment ça marche ?</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                @php
                    $steps = [
                        ['icon' => 'user-plus',  'title' => 'Inscrivez-vous',    'desc' => 'Créez votre compte avec votre numéro. C\'est gratuit.'],
                        ['icon' => 'target',     'title' => 'Pronostiquez',      'desc' => 'Prédisez les scores des matchs.'],
                        ['icon' => 'map-pin',    'title' => 'Visitez les lieux', 'desc' => '+4 points bonus en visitant nos lieux partenaires.'],
                        ['icon' => 'trophy',     'title' => 'Gagnez',            'desc' => 'Accumulez des points et remportez des cadeaux.'],
                    ];
                @endphp
                @foreach($steps as $i => $step)
                <div class="text-center group">
                    <div class="w-20 h-20 bg-soboa-orange/10 group-hover:bg-soboa-orange rounded-2xl flex items-center justify-center mx-auto mb-6 transition-colors duration-base">
                        <i data-lucide="{{ $step['icon'] }}" class="w-9 h-9 text-soboa-orange group-hover:text-white transition-colors duration-base" stroke-width="1.75"></i>
                    </div>
                    <div class="bg-soboa-orange text-white font-bold w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-4">
                        {{ $i + 1 }}
                    </div>
                    <h3 class="font-bold text-soboa-blue text-xl mb-2">{{ $step['title'] }}</h3>
                    <p class="text-gray-600">{{ $step['desc'] }}</p>
                </div>
                @endforeach
            </div>

            <!-- Points Breakdown -->
            <div class="mt-16 bg-soboa-blue rounded-2xl p-8 text-white">
                <h3 class="text-center font-black text-2xl mb-8">Système de points</h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-center">
                    <div class="bg-white/10 rounded-xl p-4">
                        <span class="text-3xl font-black text-soboa-orange">+1</span>
                        <p class="text-sm text-white/80 mt-2">Connexion/jour</p>
                    </div>
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
                <p class="text-center text-white/60 text-sm mt-4">Maximum 7 points par match + 4 points bonus par visite + 1 point par connexion quotidienne</p>
            </div>

            <!-- CTA -->
            <div class="text-center mt-12">
                <a href="/login"
                    class="inline-flex items-center gap-2 bg-soboa-orange hover:bg-soboa-orange-secondary text-white font-bold py-4 px-10 rounded-full shadow-elev-2 hover:shadow-elev-3 focus:outline-none focus:ring-2 focus:ring-soboa-orange focus:ring-offset-2 transition-all duration-base transform hover:scale-105 text-lg">
                    Commencer maintenant
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </a>
            </div>
        </div>
    </section>
    @endif

    @if($siteSettings && $siteSettings->tournamentWinner)
    <!-- Confetti Script for Winner Celebration -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        const canvas = document.getElementById('confetti-canvas');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const confettiColors = ['#F1862D', '#0058A3', '#F4A05B', '#3478B5', '#FFFFFF', '#FEF7F1'];
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
