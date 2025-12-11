<x-layouts.app title="Accueil">
    
    <!-- Hero Section - 95 Years Celebration -->
    <section class="relative min-h-[90vh] flex items-center justify-center overflow-hidden hero-gradient" 
             x-data="{ 
                 countdown: { days: 0, hours: 0, minutes: 0, seconds: 0 },
                 targetDate: new Date('2025-12-21T00:00:00').getTime(),
                 isLaunched: false,
                 init() {
                     this.updateCountdown();
                     setInterval(() => this.updateCountdown(), 1000);
                 },
                 updateCountdown() {
                     const now = new Date().getTime();
                     const distance = this.targetDate - now;
                     
                     if (distance < 0) {
                         this.isLaunched = true;
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
        
        <!-- Background Image with Overlay -->
        <div class="absolute inset-0">
            <img src="/images/hero_celebration.png" alt="Célébration SOBOA" class="w-full h-full object-cover opacity-30">
            <div class="absolute inset-0 bg-gradient-to-b from-soboa-blue/80 via-soboa-blue/60 to-soboa-blue/90"></div>
        </div>
        
        <!-- Animated Shapes -->
        <div class="absolute top-20 left-10 w-64 h-64 bg-soboa-orange/20 rounded-full blur-3xl animate-pulse-slow"></div>
        <div class="absolute bottom-20 right-10 w-80 h-80 bg-soboa-orange/10 rounded-full blur-3xl animate-float"></div>
        
        <!-- Content -->
        <div class="relative z-10 text-center px-4 max-w-5xl mx-auto">
            <!-- 95 Years Badge -->
            <div class="inline-flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-full px-6 py-3 mb-8 border border-white/20">
                <div class="w-14 h-14 bg-soboa-orange rounded-full flex items-center justify-center font-black text-white text-xl shadow-lg orange-glow">
                    95
                </div>
                <span class="text-white font-bold text-lg">ANS D'EXCELLENCE</span>
            </div>
            
            <!-- Main Heading -->
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-black text-white mb-6 leading-tight">
                Célébrez avec nous<br>
                <span class="gradient-text">la CAN 2025</span>
            </h1>
            
            <p class="text-xl md:text-2xl text-white/80 mb-10 max-w-2xl mx-auto font-medium">
                Pronostiquez les scores, visitez nos bars partenaires et gagnez des cadeaux exclusifs !
            </p>
            
            <!-- Countdown Timer -->
            <div x-show="!isLaunched" class="mb-10">
                <p class="text-soboa-orange font-bold text-sm uppercase tracking-widest mb-4">Lancement officiel dans</p>
                <div class="flex justify-center gap-3 md:gap-6">
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 md:p-6 min-w-[70px] md:min-w-[100px] border border-white/20">
                        <span class="text-3xl md:text-5xl font-black text-white block countdown-number" x-text="countdown.days">00</span>
                        <span class="text-white/60 text-xs md:text-sm font-semibold uppercase">Jours</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 md:p-6 min-w-[70px] md:min-w-[100px] border border-white/20">
                        <span class="text-3xl md:text-5xl font-black text-white block countdown-number" x-text="countdown.hours">00</span>
                        <span class="text-white/60 text-xs md:text-sm font-semibold uppercase">Heures</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 md:p-6 min-w-[70px] md:min-w-[100px] border border-white/20">
                        <span class="text-3xl md:text-5xl font-black text-white block countdown-number" x-text="countdown.minutes">00</span>
                        <span class="text-white/60 text-xs md:text-sm font-semibold uppercase">Minutes</span>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 md:p-6 min-w-[70px] md:min-w-[100px] border border-white/20">
                        <span class="text-3xl md:text-5xl font-black text-soboa-orange block countdown-number" x-text="countdown.seconds">00</span>
                        <span class="text-white/60 text-xs md:text-sm font-semibold uppercase">Secondes</span>
                    </div>
                </div>
            </div>
            
            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @if(session('user_id'))
                <a href="/matches" class="inline-flex items-center justify-center gap-2 bg-soboa-orange hover:bg-soboa-orange-dark text-white font-bold py-4 px-8 rounded-full shadow-xl hover:shadow-2xl transition-all transform hover:scale-105 text-lg orange-glow">
                    <span>⚽</span> Faire un pronostic
                </a>
                @else
                <a href="/login" class="inline-flex items-center justify-center gap-2 bg-soboa-orange hover:bg-soboa-orange-dark text-white font-bold py-4 px-8 rounded-full shadow-xl hover:shadow-2xl transition-all transform hover:scale-105 text-lg orange-glow">
                    <span>🎮</span> Jouer & Gagner
                </a>
                @endif
                <a href="/map" class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white font-bold py-4 px-8 rounded-full border-2 border-white/30 transition-all">
                    <span>📍</span> Trouver un bar
                </a>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </div>
    </section>

    <!-- Staggered Image Grid Section -->
    <section class="py-16 md:py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-12">
                <span class="text-soboa-orange font-bold text-sm uppercase tracking-widest">L'ambiance SOBOA</span>
                <h2 class="text-3xl md:text-5xl font-black text-soboa-blue mt-2">Vivez la CAN autrement</h2>
            </div>
            
            <!-- Staggered Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                <!-- Image 1 - Large -->
                <div class="col-span-2 row-span-2 relative rounded-2xl overflow-hidden shadow-xl group">
                    <img src="/images/celebration_grid_1.png" alt="Célébration" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-soboa-blue/80 to-transparent"></div>
                    <div class="absolute bottom-6 left-6 right-6 text-white">
                        <span class="text-soboa-orange font-bold text-sm">CÉLÉBREZ</span>
                        <h3 class="text-2xl font-black">Entre amis</h3>
                    </div>
                </div>
                
                <!-- Image 2 -->
                <div class="relative rounded-2xl overflow-hidden shadow-xl group aspect-square">
                    <img src="/images/celebration_grid_2.png" alt="Match viewing" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-soboa-blue/60 to-transparent"></div>
                </div>
                
                <!-- Image 3 -->
                <div class="relative rounded-2xl overflow-hidden shadow-xl group aspect-square">
                    <img src="/images/celebration_grid_3.png" alt="Supporter" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-soboa-blue/60 to-transparent"></div>
                </div>
                
                <!-- Stats Card -->
                <div class="col-span-2 bg-soboa-blue rounded-2xl p-6 md:p-8 text-white flex items-center justify-around shadow-xl">
                    <div class="text-center">
                        <span class="text-4xl md:text-5xl font-black text-soboa-orange">95</span>
                        <p class="text-white/70 text-sm font-semibold">Ans d'histoire</p>
                    </div>
                    <div class="w-px h-16 bg-white/20"></div>
                    <div class="text-center">
                        <span class="text-4xl md:text-5xl font-black text-soboa-orange">{{ $venueCount ?? 50 }}+</span>
                        <p class="text-white/70 text-sm font-semibold">Bars partenaires</p>
                    </div>
                    <div class="w-px h-16 bg-white/20"></div>
                    <div class="text-center">
                        <span class="text-4xl md:text-5xl font-black text-soboa-orange">52</span>
                        <p class="text-white/70 text-sm font-semibold">Matchs CAN</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Matches Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
                <div>
                    <span class="text-soboa-orange font-bold text-sm uppercase tracking-widest">CAN 2025</span>
                    <h2 class="text-3xl md:text-4xl font-black text-soboa-blue mt-2">Prochains Matchs</h2>
                </div>
                <a href="/matches" class="text-soboa-orange font-bold hover:underline mt-4 md:mt-0 flex items-center gap-2">
                    Voir tous les matchs
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($upcomingMatches as $match)
                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 hover:border-soboa-orange/30 hover:shadow-xl transition-all group">
                    <!-- Match Header -->
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-xs font-bold text-soboa-blue bg-soboa-blue/10 px-3 py-1 rounded-full">
                            Groupe {{ $match->group_name }}
                        </span>
                        <span class="text-xs text-gray-500 font-semibold">
                            {{ $match->match_date->translatedFormat('d M H:i') }}
                        </span>
                    </div>
                    
                    <!-- Teams -->
                    <div class="flex items-center justify-between py-4">
                        <div class="text-center flex-1">
                            <div class="w-14 h-14 bg-soboa-blue/10 rounded-full mx-auto mb-2 flex items-center justify-center text-lg font-bold text-soboa-blue group-hover:bg-soboa-blue group-hover:text-white transition-colors">
                                {{ mb_substr($match->team_a, 0, 2) }}
                            </div>
                            <span class="font-bold text-gray-800 text-sm block truncate">{{ $match->team_a }}</span>
                        </div>
                        <div class="px-4 text-center">
                            <span class="text-2xl font-black text-gray-300">VS</span>
                        </div>
                        <div class="text-center flex-1">
                            <div class="w-14 h-14 bg-soboa-blue/10 rounded-full mx-auto mb-2 flex items-center justify-center text-lg font-bold text-soboa-blue group-hover:bg-soboa-blue group-hover:text-white transition-colors">
                                {{ mb_substr($match->team_b, 0, 2) }}
                            </div>
                            <span class="font-bold text-gray-800 text-sm block truncate">{{ $match->team_b }}</span>
                        </div>
                    </div>
                    
                    <!-- Stadium -->
                    <div class="text-center text-xs text-gray-400 mb-4 flex items-center justify-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        {{ $match->stadium }}
                    </div>
                    
                    <!-- CTA -->
                    @if(session('user_id'))
                    <a href="/matches" class="block w-full bg-soboa-orange hover:bg-soboa-orange-dark text-white font-bold py-3 rounded-xl text-center transition-colors">
                        Pronostiquer
                    </a>
                    @else
                    <a href="/login" class="block w-full bg-soboa-blue hover:bg-soboa-blue-dark text-white font-bold py-3 rounded-xl text-center transition-colors">
                        Se connecter
                    </a>
                    @endif
                </div>
                @empty
                <div class="col-span-3 text-center py-16 bg-gray-50 rounded-2xl">
                    <div class="w-20 h-20 bg-soboa-orange/10 rounded-full flex items-center justify-center mx-auto mb-4">
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
    <section class="py-16 bg-soboa-blue">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10">
                <div>
                    <span class="text-soboa-orange font-bold text-sm uppercase tracking-widest">Compétition</span>
                    <h2 class="text-3xl md:text-4xl font-black text-white mt-2">Meilleurs Pronostiqueurs</h2>
                </div>
                <a href="/leaderboard" class="text-soboa-orange font-bold hover:underline mt-4 md:mt-0 flex items-center gap-2">
                    Classement complet
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @forelse($topUsers as $index => $user)
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 text-center border border-white/10 {{ $index === 0 ? 'md:col-span-1 md:row-span-1 ring-2 ring-soboa-orange' : '' }}">
                    <div class="text-3xl mb-2">
                        @if($index == 0) 🥇 @elseif($index == 1) 🥈 @elseif($index == 2) 🥉 @else {{ $index + 1 }} @endif
                    </div>
                    <div class="w-16 h-16 bg-soboa-orange/20 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl font-bold text-white">
                        {{ mb_substr($user->name, 0, 1) }}
                    </div>
                    <h3 class="font-bold text-white text-lg truncate">{{ $user->name }}</h3>
                    <p class="text-soboa-orange font-black text-xl">{{ $user->points_total }} pts</p>
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
    <section class="py-16 md:py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <span class="text-soboa-orange font-bold text-sm uppercase tracking-widest">Simple & Amusant</span>
                <h2 class="text-3xl md:text-5xl font-black text-soboa-blue mt-2">Comment ça marche ?</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Step 1 -->
                <div class="text-center group">
                    <div class="w-20 h-20 bg-soboa-orange/10 group-hover:bg-soboa-orange rounded-2xl flex items-center justify-center mx-auto mb-6 transition-colors">
                        <span class="text-4xl group-hover:scale-125 transition-transform">📱</span>
                    </div>
                    <div class="bg-soboa-orange text-white font-bold w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-4">1</div>
                    <h3 class="font-bold text-soboa-blue text-xl mb-2">Inscrivez-vous</h3>
                    <p class="text-gray-600">Créez votre compte avec votre numéro de téléphone. C'est gratuit!</p>
                </div>
                
                <!-- Step 2 -->
                <div class="text-center group">
                    <div class="w-20 h-20 bg-soboa-orange/10 group-hover:bg-soboa-orange rounded-2xl flex items-center justify-center mx-auto mb-6 transition-colors">
                        <span class="text-4xl group-hover:scale-125 transition-transform">⚽</span>
                    </div>
                    <div class="bg-soboa-orange text-white font-bold w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-4">2</div>
                    <h3 class="font-bold text-soboa-blue text-xl mb-2">Pronostiquez</h3>
                    <p class="text-gray-600">Prédisez les scores des matchs de la CAN 2025 au Maroc.</p>
                </div>
                
                <!-- Step 3 -->
                <div class="text-center group">
                    <div class="w-20 h-20 bg-soboa-orange/10 group-hover:bg-soboa-orange rounded-2xl flex items-center justify-center mx-auto mb-6 transition-colors">
                        <span class="text-4xl group-hover:scale-125 transition-transform">🍺</span>
                    </div>
                    <div class="bg-soboa-orange text-white font-bold w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-4">3</div>
                    <h3 class="font-bold text-soboa-blue text-xl mb-2">Visitez les bars</h3>
                    <p class="text-gray-600">Gagnez +4 points bonus par jour en visitant nos bars partenaires.</p>
                </div>
                
                <!-- Step 4 -->
                <div class="text-center group">
                    <div class="w-20 h-20 bg-soboa-orange/10 group-hover:bg-soboa-orange rounded-2xl flex items-center justify-center mx-auto mb-6 transition-colors">
                        <span class="text-4xl group-hover:scale-125 transition-transform">🏆</span>
                    </div>
                    <div class="bg-soboa-orange text-white font-bold w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-4">4</div>
                    <h3 class="font-bold text-soboa-blue text-xl mb-2">Gagnez</h3>
                    <p class="text-gray-600">Accumulez des points et remportez des cadeaux exclusifs SOBOA!</p>
                </div>
            </div>
            
            <!-- CTA -->
            <div class="text-center mt-12">
                <a href="/login" class="inline-flex items-center gap-2 bg-soboa-orange hover:bg-soboa-orange-dark text-white font-bold py-4 px-10 rounded-full shadow-xl hover:shadow-2xl transition-all transform hover:scale-105 text-lg">
                    Commencer maintenant
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </a>
            </div>
        </div>
    </section>

</x-layouts.app>
