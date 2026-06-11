<x-layouts.app title="Classement">
    <x-skeleton-screen type="list" :cards="8" />
    <div class="space-y-6">
        <!-- Header -->
        <div class="relative py-8 px-6 rounded-2xl overflow-hidden mb-6 shadow-2xl text-center">
            <div class="absolute inset-0 z-0">
                <img src="/images/sen.webp" class="w-full h-full object-cover" alt="Background">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>
            </div>
            <div class="relative z-10">
                <h1 class="text-3xl md:text-4xl font-black text-white drop-shadow-2xl">Classement</h1>
                <p class="text-white/90 font-bold mt-2 uppercase tracking-widest text-xs">
                    {{ $period_label }}
                </p>
            </div>
        </div>

        {{-- Classement unique sur toute la compétition : pas de sélecteur de
             période, pas de remise à zéro. Le cumul court du premier au
             dernier match. --}}
        <div class="bg-gradient-to-r from-soboa-blue to-soboa-blue-dark rounded-xl shadow-lg p-4 text-white">
            <div class="flex items-center gap-3">
                <i data-lucide="trophy" class="w-7 h-7 text-soboa-orange flex-shrink-0"></i>
                <div>
                    <p class="font-bold">Classement National — Top 50</p>
                    <p class="text-sm text-white/80">Les 50 meilleurs pronostiqueurs sur toute la compétition</p>
                </div>
            </div>
        </div>

        @php
            $isWeekly = str_starts_with($selected_period, 'week_');
            $topData = $isWeekly ? $top15 : ($top50 ?? $top20 ?? []);
            $topLimit = $isWeekly ? 15 : 50;
            $topLabel = $isWeekly ? 'TOP 15 Hebdomadaire' : 'TOP 50 National';
        @endphp

        <!-- Classement principal -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
            <div class="bg-gradient-to-r from-soboa-blue to-soboa-blue/80 p-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <span></span> {{ $topLabel }}
                </h2>
                <p class="text-white/80 text-sm mt-1">
                    @if($isWeekly)
                        Points gagnés cette semaine
                    @else
                        Classement général depuis le 21 décembre 2025
                    @endif
                </p>
            </div>

            @if(count($topData) > 0)
                @php
                    $podiumConfig = [
                        0 => ['order' => 'sm:order-2', 'avatar' => 'w-20 h-20 sm:w-24 sm:h-24 text-2xl', 'ring' => 'ring-yellow-400', 'bar' => 'h-28 sm:h-32 from-yellow-300 to-yellow-500', 'barText' => 'text-yellow-900', 'icon' => 'crown', 'iconColor' => 'text-yellow-400', 'rankLabel' => '1', 'medal' => 'bg-yellow-400'],
                        1 => ['order' => 'sm:order-1', 'avatar' => 'w-16 h-16 sm:w-20 sm:h-20 text-xl', 'ring' => 'ring-gray-300', 'bar' => 'h-20 sm:h-24 from-gray-300 to-gray-400', 'barText' => 'text-gray-800', 'icon' => 'medal', 'iconColor' => 'text-gray-300', 'rankLabel' => '2', 'medal' => 'bg-gray-300'],
                        2 => ['order' => 'sm:order-3', 'avatar' => 'w-16 h-16 sm:w-20 sm:h-20 text-xl', 'ring' => 'ring-amber-600', 'bar' => 'h-16 sm:h-20 from-amber-400 to-amber-600', 'barText' => 'text-amber-900', 'icon' => 'medal', 'iconColor' => 'text-amber-500', 'rankLabel' => '3', 'medal' => 'bg-amber-500'],
                    ];
                @endphp

                <!-- Podium Top 3 (mobile = stacked list, desktop = podium) -->
                <div class="bg-gradient-to-br from-soboa-orange to-soboa-orange-secondary p-5 sm:p-6 sm:pb-8">
                    {{-- Mobile : liste claire --}}
                    <ul class="sm:hidden space-y-2">
                        @foreach([0, 1, 2] as $idx)
                            @if(isset($topData[$idx]))
                                @php $cfg = $podiumConfig[$idx]; @endphp
                                <li class="bg-white/95 rounded-xl p-3 flex items-center gap-3 shadow-elev-1">
                                    <div class="relative flex-shrink-0">
                                        <div class="w-12 h-12 rounded-full bg-soboa-blue text-white flex items-center justify-center font-black text-lg ring-2 {{ $cfg['ring'] }}">
                                            {{ substr($topData[$idx]['name'], 0, 1) }}
                                        </div>
                                        <span class="absolute -bottom-1 -right-1 w-6 h-6 {{ $cfg['medal'] }} rounded-full flex items-center justify-center text-xs font-black text-white ring-2 ring-white">{{ $cfg['rankLabel'] }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-black text-soboa-text-dark text-sm truncate">{{ $topData[$idx]['name'] }}</p>
                                        <p class="text-xs text-gray-500">Rang #{{ $cfg['rankLabel'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-black text-soboa-orange text-lg tabular-nums">{{ $topData[$idx]['points'] }}</p>
                                        <p class="text-[10px] text-gray-500 uppercase tracking-wider">pts</p>
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    </ul>

                    {{-- Desktop : podium classique --}}
                    <div class="hidden sm:grid grid-cols-3 items-end gap-3">
                        @foreach([1, 0, 2] as $idx)
                            @if(isset($topData[$idx]))
                                @php $cfg = $podiumConfig[$idx]; @endphp
                                <div class="flex flex-col items-center {{ $cfg['order'] }}">
                                    <i data-lucide="{{ $cfg['icon'] }}" class="{{ $cfg['iconColor'] }} w-6 h-6 mb-1"></i>
                                    <div class="{{ $cfg['avatar'] }} rounded-full bg-soboa-blue text-white flex items-center justify-center font-black mb-2 ring-4 {{ $cfg['ring'] }} shadow-elev-2">
                                        {{ substr($topData[$idx]['name'], 0, 1) }}
                                    </div>
                                    <div class="text-center mb-2 max-w-full">
                                        <div class="font-black text-base text-soboa-text-dark truncate">{{ $topData[$idx]['name'] }}</div>
                                        <div class="text-soboa-text-dark/70 text-sm font-bold tabular-nums">{{ $topData[$idx]['points'] }} pts</div>
                                    </div>
                                    <div class="w-full {{ $cfg['bar'] }} bg-gradient-to-b rounded-t-lg flex items-center justify-center text-3xl font-black {{ $cfg['barText'] }} shadow-elev-2">
                                        {{ $cfg['rankLabel'] }}
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Positions 4 à N (selon période) -->
                @if(count($topData) > 3)
                    <div class="divide-y divide-gray-100">
                        @foreach(array_slice($topData, 3) as $entry)
                            @if($entry)
                                <div class="p-4 flex items-center justify-between hover:bg-soboa-cream transition-colors duration-base {{ ($isWeekly && $entry['rank'] <= 15) ? 'bg-emerald-50/50' : '' }}">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="flex-shrink-0 w-9 h-9 rounded-full {{ $entry['rank'] <= 10 ? 'bg-soboa-blue text-white' : 'bg-soboa-blue/10 text-soboa-blue' }} flex items-center justify-center font-black text-sm tabular-nums">
                                            {{ $entry['rank'] }}
                                        </span>
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-soboa-blue to-soboa-blue-light text-white flex items-center justify-center font-black">
                                            {{ substr($entry['name'], 0, 1) }}
                                        </div>
                                        <span class="font-bold text-soboa-text-dark truncate">{{ $entry['name'] }}</span>
                                        @if($isWeekly && $entry['rank'] <= 15)
                                            <span class="flex-shrink-0 inline-flex items-center gap-1 text-[10px] bg-emerald-500 text-white px-2 py-0.5 rounded-full font-bold uppercase">
                                                <i data-lucide="check" class="w-2.5 h-2.5"></i>Gagnant
                                            </span>
                                        @endif
                                    </div>
                                    <span class="flex-shrink-0 font-black text-soboa-orange tabular-nums">{{ $entry['points'] }} <span class="text-xs text-gray-500">pts</span></span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            @else
                <div class="p-section-md text-center">
                    <div class="w-20 h-20 mx-auto bg-soboa-orange/10 rounded-full flex items-center justify-center mb-4">
                        <i data-lucide="trophy" class="w-10 h-10 text-soboa-orange"></i>
                    </div>
                    <h3 class="text-xl font-black text-soboa-text-dark">Classement vide</h3>
                    <p class="text-gray-600 mt-2 text-sm max-w-xs mx-auto">Soyez le premier à pronostiquer pour apparaître ici.</p>
                    @guest
                    <a href="{{ route('login') }}" class="btn btn-primary btn-md btn-pill mt-5">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        Se connecter
                    </a>
                    @else
                    <a href="{{ route('matches') }}" class="btn btn-primary btn-md btn-pill mt-5">
                        <i data-lucide="target" class="w-4 h-4"></i>
                        Faire un pronostic
                    </a>
                    @endguest
                </div>
            @endif
        </div>

        {{-- Position personnelle --}}
        @if($user_position)
            @php
                $topThreshold = $isWeekly ? 15 : 50;
                $userInTopDisplay = $user_position['rank'] <= $topThreshold;
                $topLabel2 = $isWeekly ? 'TOP 15' : 'TOP 50';
                $pointsToNext = $userInTopDisplay ? null : null;
            @endphp

            @if(!$userInTopDisplay)
                {{-- Bloc "hors top" valorisant --}}
                <div class="relative overflow-hidden rounded-2xl shadow-lg border-2 border-soboa-orange/40">
                    <div class="absolute inset-0 bg-gradient-to-br from-soboa-blue via-soboa-blue-dark to-soboa-text-dark opacity-95"></div>
                    <div class="relative z-10 p-6">
                        <div class="flex items-start justify-between gap-4 flex-wrap">
                            <div class="flex items-center gap-4">
                                <div class="relative">
                                    <div class="w-16 h-16 rounded-full bg-soboa-orange flex items-center justify-center text-2xl font-black text-white shadow-lg">
                                        {{ $user_position['rank'] }}
                                    </div>
                                    <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-soboa-cream rounded-full flex items-center justify-center text-xs"></div>
                                </div>
                                <div>
                                    <p class="text-soboa-cream/70 text-xs font-bold uppercase tracking-widest mb-0.5">Votre position</p>
                                    <p class="text-white font-black text-xl leading-none">{{ $user_position['rank'] }}<span class="text-soboa-cream/50 text-sm">e</span> place</p>
                                    <p class="text-soboa-orange font-black text-2xl mt-1">{{ $user_position['points'] }} pts</p>
                                    <p class="text-white/50 text-xs mt-0.5">sur {{ $user_position['total_users'] ?? '—' }} participants</p>
                                </div>
                            </div>
                            <div class="bg-soboa-orange/20 border border-soboa-orange/40 rounded-xl p-4 text-center min-w-[140px]">
                                <p class="text-soboa-orange-light text-xs font-bold uppercase tracking-wider mb-1">Pour atteindre le Top 50</p>
                                <p class="text-white/70 text-xs">Continuez à pronostiquer chaque jour</p>
                                <a href="{{ route('matches') }}" class="mt-3 inline-block bg-soboa-orange hover:bg-soboa-orange-secondary text-white text-xs font-black px-4 py-2 rounded-lg transition">
                                    Pronostiquer →
                                </a>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-white/10 grid grid-cols-3 gap-3 text-center">
                            <div class="bg-white/5 rounded-xl p-3">
                                <p class="text-soboa-orange font-black text-lg">+1</p>
                                <p class="text-white/50 text-[10px] uppercase tracking-wide">par connexion/jour</p>
                            </div>
                            <div class="bg-white/5 rounded-xl p-3">
                                <p class="text-soboa-orange font-black text-lg">+4</p>
                                <p class="text-white/50 text-[10px] uppercase tracking-wide">en point partenaire</p>
                            </div>
                            <div class="bg-white/5 rounded-xl p-3">
                                <p class="text-soboa-orange font-black text-lg">+6</p>
                                <p class="text-white/50 text-[10px] uppercase tracking-wide">score exact</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- Dans le top --}}
                <div class="bg-gradient-to-r from-soboa-blue to-soboa-blue-dark rounded-2xl shadow-lg p-6 text-white">
                    <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <span></span> Votre position
                    </h3>
                    <div class="bg-white/10 rounded-xl p-4 backdrop-blur">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-soboa-orange flex items-center justify-center text-2xl font-black text-white shadow-xl">
                                #{{ $user_position['rank'] }}
                            </div>
                            <div>
                                <div class="text-xl font-black">{{ $user_position['points'] }} points</div>
                                <div class="text-soboa-orange font-bold text-sm mt-0.5">
                                    Vous êtes dans le {{ $topLabel2 }} !
                                    @if($isWeekly)<span class="text-green-300 ml-1">— Gagnant</span>@endif
                                </div>
                                <div class="text-white/50 text-xs mt-0.5">sur {{ $user_position['total_users'] ?? '—' }} participants</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="bg-gray-100 rounded-xl p-6 text-center">
                <span class="text-4xl mb-2 block"></span>
                <p class="text-gray-600 mb-4">Connectez-vous pour voir votre position dans le classement</p>
                <a href="{{ route('login') }}" class="inline-block bg-soboa-orange text-white font-bold px-6 py-3 rounded-full hover:bg-soboa-orange-secondary transition">
                    Se connecter
                </a>
            </div>
        @endif

        <!-- Légende des points -->
        <div class="bg-gradient-to-r from-soboa-blue/5 to-soboa-orange/5 rounded-xl p-4 border border-gray-200">
            <h3 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                <span></span> Comment gagner des points ?
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs">
                <span class="bg-white px-3 py-2 rounded-lg border border-gray-200 flex items-center gap-2">
                    <span></span> +1 pt/connexion/jour
                </span>
                <span class="bg-white px-3 py-2 rounded-lg border border-gray-200 flex items-center gap-2">
                    <span></span> +1 pt/pronostic
                </span>
                <span class="bg-white px-3 py-2 rounded-lg border border-gray-200 flex items-center gap-2">
                    <span></span> +3 pts/bon vainqueur
                </span>
                <span class="bg-white px-3 py-2 rounded-lg border border-gray-200 flex items-center gap-2">
                    <span></span> +3 pts/score exact
                </span>
                <span class="bg-white px-3 py-2 rounded-lg border border-gray-200 flex items-center gap-2">
                    <span></span> +4 pts/visite lieu
                </span>
            </div>
        </div>
    </div>
</x-layouts.app>