@php
    $animationsJson = $animations->map(function($anim) {
        return [
            'date' => \Carbon\Carbon::parse($anim->animation_date)->format('Y-m-d'),
            'match' => ($anim->match->homeTeam ? $anim->match->homeTeam->name : $anim->match->team_a) . ' vs ' . ($anim->match->awayTeam ? $anim->match->awayTeam->name : $anim->match->team_b),
            'matchId' => $anim->match->id,
            'time' => \Carbon\Carbon::parse($anim->match->match_date)->format('H:i'),
            'venue' => $anim->bar->name,
            'zone' => $anim->bar->zone ?? '',
            'lat' => $anim->bar->latitude,
            'lng' => $anim->bar->longitude,
        ];
    })->values();
@endphp

<x-layouts.app title="Calendrier des Animations">
    <div class="space-y-6" x-data="calendarApp()">
        <!-- Header -->
        <div class="relative py-10 md:py-14 px-6 md:px-10 rounded-2xl overflow-hidden mb-6 shadow-2xl">
            <div class="absolute inset-0 z-0">
                <img src="/images/sen.webp" class="w-full h-full object-cover scale-105" alt="Background">
                <div class="absolute inset-0 bg-gradient-to-br from-black/70 via-black/50 to-soboa-blue/40"></div>
            </div>
            <div class="relative z-10">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="text-center md:text-left">
                        <div class="inline-flex items-center gap-3 mb-3">
                            <span class="text-5xl md:text-6xl animate-bounce">📅</span>
                            <div class="h-12 w-1 bg-soboa-orange rounded-full hidden md:block"></div>
                        </div>
                        <h1 class="text-3xl md:text-4xl lg:text-5xl font-black text-white drop-shadow-2xl tracking-tight">
                            Calendrier des <span class="text-soboa-orange">Animations</span>
                        </h1>
                        <p class="text-white/80 font-medium text-sm md:text-base mt-2 max-w-md">
                            Retrouvez tous les matchs de la CAN 2025 diffusés dans nos points de vente partenaires
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="bg-white/10 backdrop-blur-md border border-white/20 px-5 py-3 rounded-xl shadow-xl text-center">
                            <span class="text-xs text-white/70 font-bold uppercase tracking-wider block">Animations</span>
                            <span class="text-soboa-orange font-black text-2xl drop-shadow-md">{{ $animations->count() }}</span>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md border border-white/20 px-5 py-3 rounded-xl shadow-xl text-center">
                            <span class="text-xs text-white/70 font-bold uppercase tracking-wider block">Points de vente</span>
                            <span class="text-white font-black text-2xl drop-shadow-md">{{ $venuesWithAnimations->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mention 18+ -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
            <div class="flex items-center justify-center gap-3">
                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-black text-xs">18+</span>
                </div>
                <p class="text-red-700 text-sm font-medium">
                    Ce jeu est réservé aux plus de 18 ans. 
                    <a href="{{ route('terms') }}" class="underline hover:text-red-900">Conditions de participation</a>
                </p>
            </div>
        </div>

        <!-- VUE CALENDRIER (seule vue disponible) -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100">
            <!-- Navigation du mois -->
            <div class="bg-gradient-to-r from-soboa-blue via-blue-600 to-soboa-blue px-4 md:px-8 py-5">
                <div class="flex items-center justify-between max-w-md mx-auto">
                    <button @click="prevMonth()" class="text-white hover:bg-white/20 p-3 rounded-full transition-all hover:scale-110 active:scale-95">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <div class="text-center">
                        <h2 class="text-2xl md:text-3xl font-black text-white tracking-tight" x-text="monthNames[currentMonth]"></h2>
                        <p class="text-white/70 text-sm font-medium" x-text="currentYear"></p>
                    </div>
                    <button @click="nextMonth()" class="text-white hover:bg-white/20 p-3 rounded-full transition-all hover:scale-110 active:scale-95">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Jours de la semaine -->
            <div class="grid grid-cols-7 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200" style="display: grid; grid-template-columns: repeat(7, 1fr);">
                <template x-for="(day, idx) in ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']" :key="day">
                    <div :class="idx >= 5 ? 'text-soboa-orange' : 'text-gray-600'" 
                         class="text-center py-3 md:py-4 font-bold text-xs md:text-sm uppercase tracking-wider border-r border-gray-200 last:border-r-0" 
                         x-text="day"></div>
                </template>
            </div>

            <!-- Grille du calendrier -->
            <div class="calendar-grid bg-white" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0;">
                <template x-for="(day, index) in calendarDays" :key="index">
                    <div 
                        :class="{
                            'bg-gray-50/80 text-gray-400': day.isOtherMonth,
                            'bg-gradient-to-br from-soboa-orange/20 to-orange-100 ring-2 ring-soboa-orange ring-inset shadow-inner': day.isToday && !day.isOtherMonth,
                            'cursor-pointer hover:bg-blue-50/80 hover:shadow-md': day.hasAnimations && !day.isOtherMonth,
                            'bg-white': !day.isOtherMonth && !day.isToday
                        }"
                        class="calendar-cell border border-gray-200 p-2 relative transition-all duration-200 group flex flex-col"
                        style="min-height: 100px; width: 100%;"
                        @click="day.hasAnimations && !day.isOtherMonth && showDayDetails(day.date)">
                        
                        <!-- Numéro du jour -->
                        <div class="flex items-start justify-between mb-1">
                            <span 
                                :class="{
                                    'text-gray-300': day.isOtherMonth,
                                    'text-white bg-soboa-orange shadow-lg': day.isToday && !day.isOtherMonth,
                                    'text-gray-700 group-hover:text-soboa-blue': !day.isOtherMonth && !day.isToday
                                }"
                                class="inline-flex items-center justify-center w-6 h-6 md:w-7 md:h-7 text-xs md:text-sm font-bold rounded-full transition-colors flex-shrink-0"
                                x-text="day.dayNumber">
                            </span>
                            
                            <!-- Badge animations (mobile et desktop) -->
                            <template x-if="day.hasAnimations && !day.isOtherMonth">
                                <span class="bg-soboa-blue text-white text-[10px] md:text-xs font-bold px-1.5 py-0.5 md:px-2 md:py-1 rounded-full flex items-center justify-center shadow text-center leading-none" x-text="day.animationCount"></span>
                            </template>
                        </div>

                        <!-- Contenu de la cellule -->
                        <div class="flex-1 flex flex-col justify-start">
                            <!-- Aperçu des matchs -->
                            <template x-if="day.hasAnimations && !day.isOtherMonth">
                                <div class="space-y-0.5">
                                    <template x-for="(anim, i) in day.animations.slice(0, 2)" :key="i">
                                        <div class="text-[9px] md:text-[10px] bg-gray-100 rounded px-1.5 py-0.5 truncate border border-gray-200 text-center">
                                            <span class="font-semibold text-gray-700" x-text="anim.time"></span>
                                        </div>
                                    </template>
                                    <template x-if="day.animations.length > 2">
                                        <div class="text-[8px] md:text-[9px] text-soboa-blue font-semibold text-center">
                                            +<span x-text="day.animations.length - 2"></span> autres
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- Hover effect indicator -->
                        <template x-if="day.hasAnimations && !day.isOtherMonth">
                            <div class="absolute bottom-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-3 h-3 md:w-4 md:h-4 text-soboa-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Légende -->
            <div class="bg-gradient-to-r from-gray-50 to-white px-4 md:px-6 py-4 border-t border-gray-100">
                <div class="flex flex-wrap gap-4 md:gap-6 justify-center items-center text-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 bg-gradient-to-br from-soboa-orange to-orange-400 rounded-full shadow-sm flex items-center justify-center">
                            <span class="text-white text-xs font-bold">{{ now()->format('d') }}</span>
                        </div>
                        <span class="text-gray-600 font-medium">Aujourd'hui</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 bg-gradient-to-r from-soboa-blue to-blue-600 rounded-full shadow-sm flex items-center justify-center">
                            <span class="text-white text-[10px]">⚽</span>
                        </div>
                        <span class="text-gray-600 font-medium">Matchs diffusés</span>
                    </div>
                    <div class="hidden md:flex items-center gap-2 text-gray-500 text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
                        </svg>
                        <span>Cliquez sur un jour pour voir les détails</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal détails du jour -->
        <div x-show="selectedDay" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4"
             @click.self="selectedDay = null"
             @keydown.escape.window="selectedDay = null"
             style="overflow: hidden;">
            <div x-show="selectedDay"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-2xl shadow-2xl w-full max-w-[95vw] h-[85vh] md:h-[90vh] flex flex-col"
                 style="max-width: min(1400px, 95vw); overflow: hidden; position: relative;">
                <!-- Header -->
                <div class="bg-gradient-to-r from-soboa-blue via-blue-600 to-soboa-blue px-6 md:px-8 py-4 relative flex-shrink-0" style="overflow: hidden;">
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute right-0 top-0 w-20 h-20 bg-white rounded-full transform translate-x-6 -translate-y-6"></div>
                        <div class="absolute left-0 bottom-0 w-16 h-16 bg-white rounded-full transform -translate-x-4 translate-y-4"></div>
                    </div>
                    <div class="relative flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <p class="text-white/70 text-xs font-medium uppercase tracking-wider mb-1">Animations du</p>
                                    <h3 class="text-xl md:text-2xl font-black text-white capitalize" x-text="selectedDayTitle"></h3>
                                </div>
                                <button @click="selectedDay = null" class="text-white/70 hover:text-white hover:bg-white/20 p-2 rounded-full transition-all">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <!-- Compteur -->
                            <div class="flex items-center gap-2">
                                <span class="bg-white/20 text-white text-sm font-bold px-3 py-1 rounded-full" x-text="selectedDayAnimations.length + ' match' + (selectedDayAnimations.length > 1 ? 's' : '') + ' diffusé' + (selectedDayAnimations.length > 1 ? 's' : '')"></span>
                                <template x-if="selectedDayAnimations.length > 0">
                                    <span class="text-white/60 text-xs">•</span>
                                </template>
                                <template x-if="selectedDayAnimations.length > 0">
                                    <span class="text-white/60 text-xs">Sélectionnez un bar pour plus d'infos</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des animations -->
                <div class="p-6 flex-1 overflow-y-auto">
                    <!-- Grid responsive pour les animations -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
                        <template x-for="(anim, index) in selectedDayAnimations" :key="index">
                            <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-all hover:scale-[1.02]">
                                <!-- Match header -->
                                <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-100">
                                    <div class="w-10 h-10 bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl flex items-center justify-center shadow">
                                        <span class="text-lg">⚽</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-gray-800 text-sm leading-tight" x-text="anim.match"></p>
                                        <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">
                                            <span>🕐</span>
                                            <span x-text="anim.time"></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Lieu -->
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="w-8 h-8 bg-soboa-orange/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span class="text-sm">📍</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-800 text-sm leading-tight" x-text="anim.venue"></p>
                                        <template x-if="anim.zone">
                                            <p class="text-xs text-gray-500 truncate" x-text="anim.zone"></p>
                                        </template>
                                    </div>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex gap-2">
                                    <template x-if="anim.lat && anim.lng">
                                        <a :href="'https://www.google.com/maps/dir/?api=1&destination=' + anim.lat + ',' + anim.lng"
                                           target="_blank"
                                           class="flex-1 bg-gradient-to-r from-soboa-blue to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-center font-bold py-2 px-3 rounded-lg text-xs transition-all shadow-sm hover:shadow flex items-center justify-center gap-1">
                                            <span class="text-sm">🗺️</span>
                                            <span>Itinéraire</span>
                                        </a>
                                    </template>
                                    <a :href="'/matches#match-' + anim.matchId"
                                       class="flex-1 bg-gradient-to-r from-soboa-orange to-orange-500 hover:from-orange-500 hover:to-orange-600 text-black text-center font-bold py-2 px-3 rounded-lg text-xs transition-all shadow-sm hover:shadow flex items-center justify-center gap-1">
                                        <span class="text-sm">🎯</span>
                                        <span>Pronostiquer</span>
                                    </a>
                                </div>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Empty state -->
                    <template x-if="selectedDayAnimations.length === 0">
                        <div class="col-span-full text-center py-12">
                            <span class="text-6xl">📅</span>
                            <p class="text-gray-500 mt-3 text-lg">Aucune animation ce jour</p>
                        </div>
                    </template>
                </div>
                
                <!-- Footer -->
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex-shrink-0">
                    <div class="flex gap-3 justify-end">
                        <button @click="selectedDay = null" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-lg transition-colors text-sm">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function calendarApp() {
            // Données des animations depuis PHP
            const animationsData = @json($animationsJson);

            const today = new Date();
            
            return {
                currentMonth: today.getMonth(),
                currentYear: today.getFullYear(),
                monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
                selectedDay: null,
                selectedDayTitle: '',
                selectedDayAnimations: [],

                get calendarDays() {
                    const days = [];
                    const firstDay = new Date(this.currentYear, this.currentMonth, 1);
                    const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0);
                    
                    // Ajuster pour commencer le lundi (0 = Lundi, 6 = Dimanche)
                    let startDay = firstDay.getDay() - 1;
                    if (startDay < 0) startDay = 6;

                    // Jours du mois précédent
                    const prevMonthLastDay = new Date(this.currentYear, this.currentMonth, 0).getDate();
                    for (let i = startDay - 1; i >= 0; i--) {
                        const d = prevMonthLastDay - i;
                        const date = new Date(this.currentYear, this.currentMonth - 1, d);
                        days.push(this.createDayObject(date, true));
                    }

                    // Jours du mois actuel
                    for (let d = 1; d <= lastDay.getDate(); d++) {
                        const date = new Date(this.currentYear, this.currentMonth, d);
                        days.push(this.createDayObject(date, false));
                    }

                    // Jours du mois suivant pour compléter la grille
                    const remaining = 42 - days.length; // 6 semaines max
                    for (let d = 1; d <= remaining; d++) {
                        const date = new Date(this.currentYear, this.currentMonth + 1, d);
                        days.push(this.createDayObject(date, true));
                    }

                    return days;
                },

                createDayObject(date, isOtherMonth) {
                    const dateStr = date.toISOString().split('T')[0];
                    const dayAnimations = animationsData.filter(a => a.date === dateStr);
                    const today = new Date();
                    const isToday = date.getDate() === today.getDate() && 
                                   date.getMonth() === today.getMonth() && 
                                   date.getFullYear() === today.getFullYear();

                    return {
                        date: dateStr,
                        dayNumber: date.getDate(),
                        isOtherMonth,
                        isToday,
                        hasAnimations: dayAnimations.length > 0,
                        animationCount: dayAnimations.length,
                        animations: dayAnimations
                    };
                },

                prevMonth() {
                    if (this.currentMonth === 0) {
                        this.currentMonth = 11;
                        this.currentYear--;
                    } else {
                        this.currentMonth--;
                    }
                },

                nextMonth() {
                    if (this.currentMonth === 11) {
                        this.currentMonth = 0;
                        this.currentYear++;
                    } else {
                        this.currentMonth++;
                    }
                },

                showDayDetails(dateStr) {
                    const date = new Date(dateStr);
                    const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
                    this.selectedDayTitle = date.toLocaleDateString('fr-FR', options);
                    this.selectedDayAnimations = animationsData.filter(a => a.date === dateStr);
                    this.selectedDay = dateStr;
                }
            };
        }
    </script>

    <style>
        .calendar-grid {
            display: grid !important;
            grid-template-columns: repeat(7, 1fr) !important;
            width: 100%;
        }
        
        .calendar-cell {
            border: 1px solid #e5e7eb;
            min-height: 100px;
            display: flex !important;
            flex-direction: column;
        }
        
        @media (min-width: 768px) {
            .calendar-cell {
                min-height: 120px;
            }
        }
        
        @media (min-width: 1024px) {
            .calendar-cell {
                min-height: 140px;
            }
        }
    </style>
</x-layouts.app>
