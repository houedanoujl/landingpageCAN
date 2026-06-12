<x-layouts.app title="Pronostics">
<x-skeleton-screen type="matches" :cards="6" />
<div x-data="matchesPage()" x-init="init()" :class="{ 'searching': search.trim().length > 0 }" class="space-y-6 w-full max-w-7xl mx-auto px-3 fold:px-4 sm:px-6 lg:px-8">

    {{-- ========== HEADER ========== --}}
    <header class="relative py-section-sm px-6 rounded-2xl overflow-hidden shadow-elev-2">
        <div class="absolute inset-0 z-0">
            <img src="/images/sen.webp" alt="" loading="lazy" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-soboa-text-dark/80 via-soboa-blue/60 to-soboa-text-dark/80"></div>
        </div>
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl md:text-4xl font-black text-white drop-shadow-lg flex items-center gap-3">
                    <i data-lucide="target" class="w-8 h-8 text-soboa-orange-light"></i>
                    Pronostics
                </h1>
                <p class="text-white/80 font-semibold uppercase tracking-widest text-xs mt-1">
                    Pariez sur vos matchs favoris
                </p>
            </div>
            <div class="bg-white/10 backdrop-blur-md ring-1 ring-white/20 px-4 py-2 rounded-xl">
                <span class="text-[10px] text-white/70 font-bold uppercase tracking-wider block">Matchs disponibles</span>
                <span class="text-soboa-orange-light font-black text-2xl block">{{ $matchesByPhase->flatten()->count() }}</span>
            </div>
        </div>
    </header>

    {{-- ========== TOURNAMENT ENDED BANNER ========== --}}
    @if($tournamentEnded)
        <div class="bg-gradient-to-r from-soboa-orange to-soboa-orange-secondary rounded-2xl p-5 text-white shadow-elev-2">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                    <i data-lucide="trophy" class="w-8 h-8"></i>
                </div>
                <div class="flex-1">
                    <p class="font-black text-xl">Tournoi terminé</p>
                    <p class="text-white/90 text-sm mt-0.5">Les pronostics sont fermés. Merci à tous les participants !</p>
                </div>
                <a href="/leaderboard" class="btn btn-ghost-light btn-md">
                    Classement
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    @endif

    @if($matchesByPhase->count() > 0)
        {{-- ========== RECHERCHE ========== --}}
        <div class="relative">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            <input type="search"
                   x-model="search"
                   @input="applySearch()"
                   placeholder="Rechercher une équipe…"
                   class="w-full pl-10 pr-10 py-2.5 rounded-xl ring-1 ring-gray-200 focus:outline-none focus:ring-2 focus:ring-soboa-blue text-sm font-medium">
            <button type="button" x-show="search.trim().length" x-cloak @click="search=''; applySearch()"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" aria-label="Effacer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        {{-- Aucun résultat de recherche --}}
        <div x-show="search.trim().length && searchCount === 0" x-cloak
             class="bg-white rounded-2xl shadow-elev-1 p-8 text-center text-gray-500">
            <i data-lucide="search-x" class="w-8 h-8 mx-auto mb-2 text-gray-300"></i>
            Aucun match pour « <span class="font-bold" x-text="search"></span> ».
        </div>

        {{-- ========== PHASE TABS (sticky) ========== --}}
        <nav class="sticky top-[84px] md:top-[104px] z-sticky -mx-4 px-4 py-2 bg-white/85 backdrop-blur-md ring-1 ring-gray-200 rounded-2xl"
             aria-label="Phases du tournoi">
            <div class="flex gap-1.5 overflow-x-auto scrollbar-hide" role="tablist">
                @foreach($phaseOrder as $phaseKey => $phaseName)
                    @if(isset($matchesByPhase[$phaseKey]) && $matchesByPhase[$phaseKey]->count() > 0)
                        @php
                            $shortName = str_replace(
                                ['Phase de Poules', '1/16e de Finale', '1/8e de Finale', 'Quarts de Finale', 'Demi-Finales', 'Match pour la 3e Place', 'Finale'],
                                ['Poules', '1/16', '1/8', 'Quarts', 'Demis', '3e Place', 'Finale'],
                                $phaseName
                            );
                        @endphp
                        <button type="button"
                                role="tab"
                                :aria-selected="(activePhase === '{{ $phaseKey }}').toString()"
                                @click="activePhase = '{{ $phaseKey }}'"
                                :class="activePhase === '{{ $phaseKey }}'
                                    ? 'bg-soboa-blue text-white shadow-elev-1'
                                    : 'text-gray-700 hover:bg-gray-100'"
                                class="whitespace-nowrap inline-flex items-center gap-2 px-3.5 py-2 rounded-xl font-bold text-sm transition-all duration-base focus:outline-none focus:ring-2 focus:ring-soboa-blue active:scale-95">
                            <span class="hidden sm:inline">{{ $phaseName }}</span>
                            <span class="sm:hidden">{{ $shortName }}</span>
                            <span class="text-[11px] px-1.5 py-0.5 rounded-full"
                                  :class="activePhase === '{{ $phaseKey }}' ? 'bg-white/25' : 'bg-gray-200 text-gray-700'">
                                {{ $matchesByPhase[$phaseKey]->count() }}
                            </span>
                        </button>
                    @endif
                @endforeach
            </div>
        </nav>

        {{-- ========== PHASE PANELS ========== --}}
        @foreach($phaseOrder as $phaseKey => $phaseName)
            @if(isset($matchesByPhase[$phaseKey]) && $matchesByPhase[$phaseKey]->count() > 0)
                <section x-show="activePhase === '{{ $phaseKey }}'" x-cloak role="tabpanel" aria-label="{{ $phaseName }}">
                    @if($phaseKey === 'group_stage' && $groupStageByGroup->count() > 0)
                        {{-- Group sub-pills (épinglées sous les onglets de phase au scroll) --}}
                        <div class="group-pills sticky top-[140px] md:top-[160px] z-sticky -mx-4 px-4 py-2 bg-white/85 backdrop-blur-md ring-1 ring-gray-200 rounded-2xl flex flex-wrap gap-1.5 mb-4">
                            @foreach($groupStageByGroup as $groupName => $_)
                                <button type="button"
                                        @click="activeGroup = '{{ $groupName }}'"
                                        :class="activeGroup === '{{ $groupName }}'
                                            ? 'bg-soboa-orange text-white shadow-elev-1'
                                            : 'bg-white text-soboa-text-dark hover:bg-soboa-cream ring-1 ring-gray-200'"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold transition-all duration-base focus:outline-none focus:ring-2 focus:ring-soboa-orange active:scale-95">
                                    Groupe {{ $groupName }}
                                </button>
                            @endforeach
                        </div>

                        @foreach($groupStageByGroup as $groupName => $groupMatches)
                            <div x-show="activeGroup === '{{ $groupName }}'" x-cloak class="group-panel grid grid-cols-1 lg:grid-cols-2 gap-4">
                                @foreach($groupMatches as $match)
                                    <x-match-row :match="$match"
                                                 :userPrediction="$userPredictions[$match->id] ?? null"
                                                 :trend="$predictionTrends[$match->id] ?? null"
                                                 :favoriteTeamId="$favoriteTeamId"
                                                 :tournamentEnded="$tournamentEnded" />
                                @endforeach
                            </div>
                        @endforeach
                    @else
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            @foreach($matchesByPhase[$phaseKey] as $match)
                                <x-match-row :match="$match"
                                             :userPrediction="$userPredictions[$match->id] ?? null"
                                             :trend="$predictionTrends[$match->id] ?? null"
                                             :favoriteTeamId="$favoriteTeamId"
                                             :tournamentEnded="$tournamentEnded" />
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif
        @endforeach

    @else
        {{-- ========== EMPTY STATE ========== --}}
        <div class="bg-white rounded-2xl shadow-elev-1 p-12 text-center">
            <div class="w-20 h-20 mx-auto bg-soboa-orange/10 rounded-full flex items-center justify-center mb-4">
                <i data-lucide="calendar-x" class="w-10 h-10 text-soboa-orange"></i>
            </div>
            <h3 class="text-2xl font-black text-soboa-text-dark">Aucun match disponible</h3>
            <p class="text-gray-600 mt-2">Revenez bientôt pour de nouveaux matchs !</p>
        </div>
    @endif

    {{-- ========== SECTION INFOS (sous la liste des matchs) ========== --}}
    @unless($tournamentEnded)
        {{-- Geolocation banners --}}
        <template x-if="venueState === 'near' && nearbyVenue">
            <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-2xl p-4 text-white shadow-elev-1">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center animate-pulse">
                        <i data-lucide="map-pin-check" class="w-6 h-6"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-white/80">Vous êtes proche de</p>
                        <p class="font-black text-lg" x-text="nearbyVenue?.name"></p>
                        <p class="text-xs text-white/80 mt-0.5"><strong>+4 points bonus</strong> automatiques sur vos pronostics</p>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="venueState === 'far' && closestVenues.length">
            <div class="bg-gradient-to-r from-soboa-blue to-soboa-blue-light rounded-2xl p-4 text-white shadow-elev-1">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                        <i data-lucide="map" class="w-5 h-5"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-sm mb-2">PDV les plus proches</p>
                        <div class="flex gap-2 overflow-x-auto snap-x snap-mandatory scrollbar-hide pb-1 -mx-1 px-1">
                            <template x-for="v in closestVenues" :key="v.id">
                                <div class="snap-start shrink-0 w-36 bg-white/10 rounded-xl px-3 py-2.5 ring-1 ring-white/15">
                                    <p class="font-bold text-sm truncate" x-text="v.name"></p>
                                    <p class="text-lg font-black leading-tight mt-0.5" x-text="v.distance_m < 1000 ? v.distance_m + ' m' : v.distance_km.toFixed(1) + ' km'"></p>
                                    <p class="text-[10px] text-white/70 uppercase tracking-wide mt-0.5">+4 pts bonus</p>
                                </div>
                            </template>
                        </div>
                        <p class="text-xs text-white/80 mt-2">Rendez-vous dans un PDV pour gagner <strong>+4 pts bonus</strong> · glissez pour voir plus</p>
                    </div>
                </div>
            </div>
        </template>

        {{-- Info panel --}}
        <div class="bg-soboa-cream rounded-xl p-4 border-l-4 border-soboa-orange flex items-start gap-3">
            <div class="w-9 h-9 rounded-full bg-soboa-orange/15 text-soboa-orange flex items-center justify-center flex-shrink-0">
                <i data-lucide="info" class="w-5 h-5"></i>
            </div>
            <div class="text-sm text-soboa-text-dark">
                <p class="font-bold mb-0.5">Comment ça marche ?</p>
                <p class="text-soboa-text-dark/80">
                    Faites vos pronostics. Dans un PDV partenaire (&lt; 200m), recevez automatiquement
                    <strong class="text-soboa-orange">+4 points bonus</strong> (1× par jour).
                </p>
            </div>
        </div>
    @endunless

    {{-- 18+ mention --}}
    <div class="bg-red-50 ring-1 ring-red-200 rounded-lg p-3 flex items-center justify-center gap-3">
        <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
            <span class="text-white font-black text-[10px]">18+</span>
        </div>
        <p class="text-red-700 text-sm">
            Jeu réservé aux plus de 18 ans.
            <a href="{{ route('terms') }}" class="underline font-semibold focus:outline-none focus:ring-2 focus:ring-red-500 rounded">Conditions</a>
        </p>
    </div>

    {{-- ========== PREDICTION MODAL ========== --}}
    <div x-show="modal.open" x-cloak
         x-transition:enter="transition ease-out duration-base"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-fast"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="modal-backdrop-sheet"
         @keydown.escape.window="closePrediction()"
         @click.self="closePrediction()"
         role="dialog" aria-modal="true" aria-labelledby="prediction-modal-title">

        <div x-show="modal.open" x-cloak
             x-transition:enter="transition ease-out duration-base"
             x-transition:enter-start="translate-y-full sm:translate-y-4 sm:scale-95 opacity-0"
             x-transition:enter-end="translate-y-0 sm:scale-100 opacity-100"
             x-transition:leave="transition ease-in duration-fast"
             x-transition:leave-start="translate-y-0 sm:scale-100 opacity-100"
             x-transition:leave-end="translate-y-full sm:translate-y-4 sm:scale-95 opacity-0"
             class="modal-sheet-panel">

            <header class="modal-header">
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] uppercase tracking-widest text-white/70 font-bold">Pronostic</p>
                    <h2 id="prediction-modal-title" class="font-black text-lg leading-tight truncate" x-text="modal.match?.matchInfo"></h2>
                    <p class="text-xs text-white/80 mt-0.5" x-text="modal.match?.kickoff"></p>
                </div>
                <button type="button" @click="closePrediction()" class="modal-close" aria-label="Fermer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </header>

            <form @submit.prevent="submitPrediction()" class="p-5 space-y-5 overflow-y-auto">
                <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                    <div class="text-center">
                        <div class="w-12 h-12 mx-auto rounded-full bg-gray-50 ring-1 ring-gray-200 overflow-hidden flex items-center justify-center mb-2">
                            <template x-if="modal.match?.homeFlag">
                                <img :src="modal.match.homeFlag" :alt="modal.match.homeName" loading="lazy" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!modal.match?.homeFlag">
                                <span class="font-black text-soboa-blue" x-text="modal.match?.homeName?.slice(0,2)"></span>
                            </template>
                        </div>
                        <label :for="'score-a-' + modal.match?.id" class="block text-xs font-bold text-soboa-text-dark truncate" x-text="modal.match?.homeName"></label>
                        <input :id="'score-a-' + modal.match?.id"
                               type="number"
                               inputmode="numeric"
                               min="0" max="20"
                               required
                               x-model.number="modal.scoreA"
                               class="mt-2 w-full text-center text-3xl font-black border-2 border-gray-200 rounded-xl py-3 focus:border-soboa-orange focus:outline-none focus:ring-2 focus:ring-soboa-orange/20 transition-colors duration-base"
                               aria-label="Score équipe domicile">
                    </div>

                    <span class="text-2xl font-black text-gray-300 mt-12">-</span>

                    <div class="text-center">
                        <div class="w-12 h-12 mx-auto rounded-full bg-gray-50 ring-1 ring-gray-200 overflow-hidden flex items-center justify-center mb-2">
                            <template x-if="modal.match?.awayFlag">
                                <img :src="modal.match.awayFlag" :alt="modal.match.awayName" loading="lazy" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!modal.match?.awayFlag">
                                <span class="font-black text-soboa-blue" x-text="modal.match?.awayName?.slice(0,2)"></span>
                            </template>
                        </div>
                        <label :for="'score-b-' + modal.match?.id" class="block text-xs font-bold text-soboa-text-dark truncate" x-text="modal.match?.awayName"></label>
                        <input :id="'score-b-' + modal.match?.id"
                               type="number"
                               inputmode="numeric"
                               min="0" max="20"
                               required
                               x-model.number="modal.scoreB"
                               class="mt-2 w-full text-center text-3xl font-black border-2 border-gray-200 rounded-xl py-3 focus:border-soboa-orange focus:outline-none focus:ring-2 focus:ring-soboa-orange/20 transition-colors duration-base"
                               aria-label="Score équipe extérieure">
                    </div>
                </div>

                {{-- Penalty (knockout + draw) --}}
                <div x-show="modal.match?.isKnockout && isDraw()" x-cloak
                     x-transition.duration.300ms
                     class="bg-amber-50 ring-2 ring-amber-300 rounded-xl p-4 space-y-3">
                    <div class="flex items-start gap-2">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
                        <div class="text-sm">
                            <p class="font-bold text-amber-800">Égalité — phase à élimination</p>
                            <p class="text-amber-700 text-xs mt-0.5">Pas de match nul possible. Qui gagne aux tirs au but ?</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" value="home" x-model="modal.penaltyWinner" class="peer sr-only" :required="modal.match?.isKnockout && isDraw()">
                            <div class="ring-2 ring-gray-200 peer-checked:ring-soboa-blue peer-checked:bg-soboa-blue/10 rounded-lg p-3 text-center transition-colors duration-base">
                                <p class="font-bold text-soboa-text-dark text-sm truncate" x-text="modal.match?.homeName"></p>
                                <p class="text-[10px] text-gray-500 mt-0.5">Vainqueur TAB</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" value="away" x-model="modal.penaltyWinner" class="peer sr-only" :required="modal.match?.isKnockout && isDraw()">
                            <div class="ring-2 ring-gray-200 peer-checked:ring-soboa-blue peer-checked:bg-soboa-blue/10 rounded-lg p-3 text-center transition-colors duration-base">
                                <p class="font-bold text-soboa-text-dark text-sm truncate" x-text="modal.match?.awayName"></p>
                                <p class="text-[10px] text-gray-500 mt-0.5">Vainqueur TAB</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Points hint --}}
                <div class="bg-soboa-blue/5 rounded-lg p-3 text-xs text-soboa-text-dark/80 leading-relaxed">
                    <p><strong class="text-soboa-blue">+1</strong> participation · <strong class="text-soboa-blue">+3</strong> bon vainqueur · <strong class="text-soboa-blue">+3</strong> score exact
                        · <strong class="text-soboa-orange" x-text="venueState === 'near' ? '+4 PDV garanti ✓' : '+4 PDV si détecté'"></strong>
                    </p>
                </div>

                <div x-show="modal.error" x-cloak class="bg-red-50 ring-1 ring-red-200 rounded-lg p-3 text-sm text-red-700 flex items-start gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
                    <span x-text="modal.error"></span>
                </div>

                <button type="submit" :disabled="modal.submitting" class="btn btn-primary btn-lg btn-block">
                    <i data-lucide="check-circle-2" class="w-5 h-5" x-show="!modal.submitting"></i>
                    <i data-lucide="loader-2" class="w-5 h-5 animate-spin" x-show="modal.submitting" x-cloak></i>
                    <span x-text="modal.submitting ? 'Envoi…' : 'Valider mon pronostic'"></span>
                </button>
            </form>
        </div>
    </div>

    {{-- ========== SUCCESS RECAP MODAL ========== --}}
    <div x-show="recap.open" x-cloak
         x-transition:enter="transition ease-out duration-base"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="modal-backdrop"
         @click.self="recap.open = false"
         role="dialog" aria-modal="true" aria-labelledby="recap-modal-title">
        <div x-show="recap.open" x-cloak
             x-transition:enter="transition ease-out duration-base"
             x-transition:enter-start="scale-90 opacity-0"
             x-transition:enter-end="scale-100 opacity-100"
             class="modal-panel modal-panel-sm">
            <div class="bg-gradient-to-r from-soboa-orange to-soboa-orange-secondary p-5 text-center">
                <div class="w-16 h-16 mx-auto bg-white rounded-full flex items-center justify-center mb-2 animate-bounce-slow">
                    <i data-lucide="party-popper" class="w-9 h-9 text-soboa-orange"></i>
                </div>
                <h3 id="recap-modal-title" class="text-xl font-black text-white">Pronostic enregistré !</h3>
            </div>
            <div class="p-5 space-y-3">
                <div class="bg-soboa-cream rounded-xl p-3 text-center">
                    <p class="text-xs text-gray-600 font-medium">Votre pronostic</p>
                    <p class="text-base font-bold text-soboa-text-dark" x-text="recap.matchInfo"></p>
                    <p class="text-2xl font-black text-soboa-blue mt-1">
                        <span x-text="recap.scoreA"></span> - <span x-text="recap.scoreB"></span>
                    </p>
                </div>
                <div class="space-y-1.5 text-sm">
                    <div class="flex items-center justify-between bg-soboa-blue/5 rounded-lg p-2.5">
                        <span class="text-soboa-text-dark">Participation</span>
                        <span class="font-black text-soboa-blue">+1 pt</span>
                    </div>
                    <div x-show="recap.venueBonus > 0" x-cloak class="flex items-center justify-between bg-emerald-50 rounded-lg p-2.5">
                        <span class="text-soboa-text-dark">Bonus PDV <span class="text-xs text-emerald-600" x-text="recap.venueName ? '(' + recap.venueName + ')' : ''"></span></span>
                        <span class="font-black text-emerald-600">+4 pts</span>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-soboa-blue to-soboa-blue-light rounded-xl p-3 text-center text-white">
                    <p class="text-xs text-white/80">Vos points totaux</p>
                    <p class="text-3xl font-black" x-text="recap.totalPoints"></p>
                </div>
                <button type="button" @click="recap.open = false" class="btn btn-primary btn-md btn-block">
                    Continuer
                </button>
            </div>
        </div>
    </div>

    {{-- ========== BOTTOM SHEET : PDV LE PLUS PROCHE ========== --}}
    <div x-show="pdvSheet.open" x-cloak
         x-transition:enter="transition ease-out duration-base"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="modal-backdrop-sheet"
         @click.self="closePdvSheet()"
         role="dialog" aria-modal="true" aria-labelledby="pdv-sheet-title">
        <div x-show="pdvSheet.open" x-cloak
             x-transition:enter="transition ease-out duration-base"
             x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-90 opacity-0"
             x-transition:enter-end="translate-y-0 sm:scale-100 opacity-100"
             class="modal-sheet-panel">
            <div class="bg-gradient-to-r from-soboa-blue to-soboa-blue-light p-5 text-center relative">
                <button type="button" @click="closePdvSheet()" class="modal-close absolute top-3 right-3 text-white" aria-label="Fermer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
                <div class="w-16 h-16 mx-auto bg-white rounded-full flex items-center justify-center mb-2 p-1.5 shadow-elev-2 overflow-hidden">
                    <img src="{{ asset('images/logoSOBOA.png.webp') }}" alt="SOBOA" class="w-full h-full object-cover rounded-full">
                </div>
                <h3 id="pdv-sheet-title" class="text-lg font-black text-white">
                    <span x-show="pdvSheet.isNear">Vous êtes dans un PDV partenaire !</span>
                    <span x-show="!pdvSheet.isNear">PDV partenaire le plus proche</span>
                </h3>
            </div>
            <div class="p-5 space-y-3">
                <div class="bg-soboa-cream rounded-xl p-4 text-center">
                    <p class="text-xl font-black text-soboa-text-dark" x-text="pdvSheet.venue?.name"></p>
                    <p x-show="pdvSheet.venue?.zone" class="text-xs text-gray-500 uppercase tracking-wide mt-0.5" x-text="pdvSheet.venue?.zone"></p>
                    <p x-show="!pdvSheet.isNear && pdvSheet.venue?.distance_m != null" class="text-2xl font-black text-soboa-blue mt-2"
                       x-text="pdvSheet.venue?.distance_m < 1000 ? pdvSheet.venue.distance_m + ' m' : pdvSheet.venue?.distance_km?.toFixed(1) + ' km'"></p>
                </div>
                <div class="flex items-center justify-between rounded-lg p-3"
                     :class="pdvSheet.isNear ? 'bg-emerald-50' : 'bg-soboa-blue/5'">
                    <span class="text-sm text-soboa-text-dark" x-text="pdvSheet.isNear ? 'Bonus PDV automatique sur vos pronostics' : 'Rendez-vous sur place pour le bonus PDV'"></span>
                    <span class="font-black" :class="pdvSheet.isNear ? 'text-emerald-600' : 'text-soboa-blue'">+4 pts</span>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('map') }}" class="btn btn-ghost btn-md flex-1">
                        <i data-lucide="map" class="w-4 h-4"></i>
                        Voir la carte
                    </a>
                    <button type="button" @click="closePdvSheet()" class="btn btn-primary btn-md flex-1">
                        Compris !
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function matchesPage() {
        return {
            activePhase: @json($matchesByPhase->keys()->first()),
            activeGroup: @json($groupStageByGroup->keys()->first()),
            search: '',
            searchCount: 0,
            venueState: 'unknown',
            nearbyVenue: null,
            closestVenues: [],
            pdvSheet: { open: false, venue: null, isNear: false },
            userLat: null,
            userLng: null,
            modal: {
                open: false,
                match: null,
                scoreA: '',
                scoreB: '',
                penaltyWinner: '',
                submitting: false,
                error: null,
                isEdit: false,
            },
            recap: {
                open: false,
                matchInfo: '',
                scoreA: 0,
                scoreB: 0,
                venueName: null,
                venueBonus: 0,
                totalPoints: {{ session('user_points', 0) }},
            },

            init() {
                // Restaurer l'onglet phase + groupe actif après un rechargement
                // (sinon validation d'un pronostic renvoie toujours au Groupe A).
                const savedPhase = sessionStorage.getItem('matches_active_phase');
                const savedGroup = sessionStorage.getItem('matches_active_group');
                if (savedPhase) this.activePhase = savedPhase;
                if (savedGroup) this.activeGroup = savedGroup;
                this.$watch('activePhase', v => sessionStorage.setItem('matches_active_phase', v));
                this.$watch('activeGroup', v => sessionStorage.setItem('matches_active_group', v));
                this.$watch('search', () => this.applySearch());

                // Navigation directe vers un match via l'ancre (#match-12) :
                // activer la bonne phase + le bon groupe avant de scroller,
                // sinon la carte reste cachée derrière les onglets Alpine.
                this.goToHashMatch();
                window.addEventListener('hashchange', () => this.goToHashMatch());

                // Bouton "Modifier" injecté après un pronostic (délégation d'évènement)
                this.$el.addEventListener('click', (e) => {
                    const btn = e.target.closest('[data-reopen]');
                    if (!btn) return;
                    const payload = this._payloads?.[btn.dataset.reopen];
                    if (payload) this.openPrediction(payload);
                });

                setTimeout(() => this.detectGeolocation(), 1500);
            },

            goToHashMatch() {
                const hash = window.location.hash;
                if (!/^#match-\d+$/.test(hash)) return;
                const card = document.getElementById(hash.slice(1));
                if (!card) return;
                if (card.dataset.phase) this.activePhase = card.dataset.phase;
                if (card.dataset.group) this.activeGroup = card.dataset.group;
                this.$nextTick(() => {
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    card.classList.add('ring-2', 'ring-soboa-orange');
                    setTimeout(() => card.classList.remove('ring-2', 'ring-soboa-orange'), 3000);
                });
            },

            isDraw() {
                return this.modal.scoreA !== '' && this.modal.scoreB !== '' && Number(this.modal.scoreA) === Number(this.modal.scoreB);
            },

            // Recherche d'un match par nom d'équipe (toutes phases/groupes confondus)
            applySearch() {
                const norm = (s) => (s || '').normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase().trim();
                const q = norm(this.search);
                const cards = document.querySelectorAll('article[id^="match-"]');
                let shown = 0;
                cards.forEach((c) => {
                    // Recherche sur les noms d'équipe (data-attrs) + texte visible de la carte
                    const hay = norm((c.dataset.homeTeam || '') + ' ' + (c.dataset.awayTeam || '') + ' ' + c.textContent);
                    const ok = q === '' || hay.includes(q);
                    c.style.display = ok ? '' : 'none';
                    if (ok && q !== '') shown++;
                });
                this.searchCount = q === '' ? -1 : shown;
            },

            openPrediction(payload) {
                (this._payloads ||= {})[payload.id] = payload;
                this.modal.match = payload;
                this.modal.scoreA = payload.existing?.scoreA ?? '';
                this.modal.scoreB = payload.existing?.scoreB ?? '';
                this.modal.penaltyWinner = payload.existing?.penaltyWinner ?? '';
                this.modal.isEdit = !!payload.existing;
                this.modal.error = null;
                this.modal.submitting = false; // jamais bloqué par un envoi précédent
                this.modal.open = true;
                document.body.style.overflow = 'hidden';
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                    document.getElementById('score-a-' + payload.id)?.focus();
                });
            },

            closePrediction() {
                this.modal.open = false;
                document.body.style.overflow = '';
            },

            // Met à jour la carte du match après un pronostic (sans recharger la page).
            applyPredictionUpdate(d) {
                if (!d || !d.match_id) return;

                // 1. Barre de tendance
                if (d.trend) {
                    const wrap = document.getElementById('trend-wrap-' + d.match_id);
                    if (wrap) {
                        wrap.style.display = '';
                        const t = d.trend;
                        const setBar = (sel, pct) => {
                            const el = wrap.querySelector(sel);
                            if (el) { el.style.width = pct + '%'; el.textContent = pct > 0 ? pct + '%' : ''; }
                        };
                        setBar('[data-trend-home]', t.home);
                        setBar('[data-trend-draw]', t.draw);
                        setBar('[data-trend-away]', t.away);
                        const label = wrap.querySelector('[data-trend-label]');
                        if (label) label.textContent = t.total + ' ' + (t.total > 1 ? 'pronostics' : 'pronostic');
                    }
                }

                // 2. Pied de carte : afficher le pronostic enregistré + bouton Modifier
                const footer = document.getElementById('match-footer-' + d.match_id);
                const payload = this._payloads?.[d.match_id];
                if (payload) {
                    payload.existing = { ...(payload.existing || {}), scoreA: d.score_a, scoreB: d.score_b };
                }
                if (footer) {
                    footer.innerHTML =
                        '<div class="flex items-center justify-between gap-3">' +
                            '<div class="flex items-center gap-2">' +
                                '<div class="w-9 h-9 rounded-full bg-green-100 text-green-700 flex items-center justify-center"><i data-lucide="check-circle-2" class="w-5 h-5"></i></div>' +
                                '<div><p class="text-xs text-gray-500 leading-tight">Votre pronostic</p>' +
                                '<p class="text-base font-black text-soboa-text-dark leading-tight">' + d.score_a + ' - ' + d.score_b + '</p></div>' +
                            '</div>' +
                            '<button type="button" data-reopen="' + d.match_id + '" class="btn btn-ghost btn-sm"><i data-lucide="pencil" class="w-4 h-4"></i>Modifier</button>' +
                        '</div>';
                    this.$nextTick(() => window.lucide && window.lucide.createIcons());
                }
            },

            async submitPrediction() {
                if (this.modal.submitting) return;
                if (this.modal.match.isKnockout && this.isDraw() && !this.modal.penaltyWinner) {
                    this.modal.error = 'Choisissez le vainqueur aux tirs au but.';
                    return;
                }
                this.modal.submitting = true;
                this.modal.error = null;

                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const fd = new FormData();
                fd.append('_token', csrf);
                fd.append('match_id', this.modal.match.id);
                fd.append('match_info', this.modal.match.matchInfo);
                fd.append('score_a', this.modal.scoreA);
                fd.append('score_b', this.modal.scoreB);
                fd.append('predict_draw', this.isDraw() ? '1' : '0');
                if (this.modal.penaltyWinner) fd.append('penalty_winner', this.modal.penaltyWinner);
                if (this.nearbyVenue?.id) {
                    fd.append('venue_id', this.nearbyVenue.id);
                    // Coordonnées pour la vérification serveur de la proximité (anti-triche).
                    if (this.userLat != null && this.userLng != null) {
                        fd.append('latitude', this.userLat);
                        fd.append('longitude', this.userLng);
                    }
                }

                try {
                    const res = await fetch(@json(route('predictions.store')), {
                        method: 'POST',
                        body: fd,
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        this.modal.error = data.message || data.error || 'Erreur lors de l\'enregistrement.';
                        this.modal.submitting = false;
                        return;
                    }

                    const totalPoints = data.user_points_total ?? this.recap.totalPoints;
                    this.recap = {
                        open: true,
                        matchInfo: this.modal.match.matchInfo,
                        scoreA: this.modal.scoreA,
                        scoreB: this.modal.scoreB,
                        venueName: data.venue ?? null,
                        venueBonus: data.venue_bonus_points ?? 0,
                        totalPoints,
                    };

                    document.querySelectorAll('[data-user-points]').forEach(el => el.textContent = totalPoints);
                    sessionStorage.setItem('user_points', totalPoints);

                    // Mise à jour live de la carte (tendance + pronostic) — sans rechargement
                    this.applyPredictionUpdate(data);
                    this.closePrediction();
                    // Réarmer le formulaire pour les modifications suivantes
                    this.modal.submitting = false;
                } catch (err) {
                    console.error('[SOBOA FOOT TIME]', err);
                    this.modal.error = 'Erreur de connexion. Réessayez.';
                    this.modal.submitting = false;
                }
            },

            async detectGeolocation() {
                if (!navigator.geolocation) return;
                navigator.geolocation.getCurrentPosition(async (pos) => {
                    // Mémoriser la position pour la vérification serveur lors du pronostic.
                    this.userLat = pos.coords.latitude;
                    this.userLng = pos.coords.longitude;
                    try {
                        const res = await fetch('/api/geolocation/venues', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            },
                            body: JSON.stringify({ latitude: pos.coords.latitude, longitude: pos.coords.longitude }),
                        });
                        const data = await res.json();
                        if (!data.success) return;
                        const venues = data.venues || [];
                        const near = venues.find(v => v.is_nearby);
                        if (near) {
                            this.nearbyVenue = near;
                            this.venueState = 'near';
                            localStorage.setItem('detected_venue_id', near.id);
                            this.showPdvSheet(near, true);
                        } else {
                            this.closestVenues = venues.slice(0, 3);
                            this.venueState = 'far';
                            if (venues.length) this.showPdvSheet(venues[0], false);
                        }
                    } catch (e) {
                        console.warn('[SOBOA FOOT TIME] geo api', e);
                    }
                }, () => {}, { enableHighAccuracy: false, timeout: 15000, maximumAge: 60000 });
            },

            // Bottom sheet "PDV le plus proche" : affiché une fois par session,
            // sauf si un pronostic est en cours de saisie.
            showPdvSheet(venue, isNear) {
                if (sessionStorage.getItem('pdv_sheet_seen')) return;
                if (this.modal.open || this.recap.open) return;
                this.pdvSheet = { open: true, venue, isNear };
            },

            closePdvSheet() {
                this.pdvSheet.open = false;
                sessionStorage.setItem('pdv_sheet_seen', '1');
            },
        };
    }
</script>

<style>
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    .scrollbar-hide::-webkit-scrollbar { display: none; }

    /* Pendant une recherche : afficher toutes les phases/groupes, masquer onglets + pills */
    .searching [role="tabpanel"] { display: block !important; }
    .searching .group-panel { display: grid !important; }
    .searching nav[aria-label="Phases du tournoi"],
    .searching .group-pills { display: none !important; }
</style>
</x-layouts.app>
