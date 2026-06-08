<x-layouts.app title="Mes Pronostics">
    <style>
        @keyframes pulse-live {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .live-indicator { animation: pulse-live 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        .live-card {
            background: linear-gradient(90deg, #ffffff 0%, #FEF7F1 50%, #ffffff 100%);
            background-size: 2000px 100%;
            animation: shimmer 3s infinite linear;
        }
        @keyframes like-pop {
            0% { transform: scale(1); }
            50% { transform: scale(1.35); }
            100% { transform: scale(1); }
        }
        .like-pop { animation: like-pop 0.25s ease; }
    </style>

    <div class="space-y-6">
        {{-- Hero --}}
        <div class="relative py-12 px-8 rounded-2xl overflow-hidden mb-2 shadow-2xl">
            <div class="absolute inset-0 z-0">
                <img src="/images/sen.webp" class="w-full h-full object-cover" alt="">
                <div class="absolute inset-0 bg-gradient-to-br from-soboa-blue-dark/80 via-soboa-blue/60 to-soboa-orange/20"></div>
            </div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-4xl font-black text-white drop-shadow-2xl">Mes Pronostics</h1>
                    <p class="text-white/70 font-bold uppercase tracking-widest text-xs mt-1">Suivez vos performances en direct</p>
                </div>
                @if(isset($user))
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl shadow-2xl border border-white/20 p-2 flex items-center gap-4 pr-8 self-start md:self-auto">
                        <div class="bg-gradient-to-br from-soboa-orange to-soboa-orange-light w-14 h-14 rounded-2xl flex items-center justify-center text-white font-black text-2xl shadow-xl">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="text-[10px] text-white/70 uppercase font-black tracking-widest">Joueur</p>
                            <p class="font-black text-white text-lg">{{ $user->name }}</p>
                        </div>
                        <div class="ml-2 pl-6 border-l border-white/20 text-center">
                            <span class="block font-black text-soboa-orange text-3xl leading-none">{{ $user->points_total }}</span>
                            <span class="text-[10px] text-white/70 font-black uppercase tracking-wider">pts</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(isset($user) && $user->plain_password)
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5" x-data="{ show: false }">
                <div class="flex items-start gap-3">
                    <div class="bg-soboa-blue/10 w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-soboa-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-black text-gray-800">Votre mot de passe</p>
                        <p class="text-xs text-gray-500 mb-3">Conservez-le pour vos prochaines connexions.</p>
                        <div class="flex items-center gap-2">
                            <code class="px-4 py-2 bg-gray-100 rounded-lg font-mono text-lg tracking-wider text-gray-800 select-all"
                                x-text="show ? @js($user->plain_password) : '••••••••'"></code>
                            <button type="button" @click="show = !show"
                                class="px-3 py-2 text-sm font-bold text-soboa-blue hover:bg-soboa-blue/10 rounded-lg transition"
                                x-text="show ? 'Masquer' : 'Afficher'"></button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($totalPredictions == 0)
            <div class="bg-white rounded-2xl shadow-elev-1 p-section-md text-center">
                <div class="w-20 h-20 bg-soboa-blue/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="clipboard-list" class="w-10 h-10 text-soboa-blue"></i>
                </div>
                <h2 class="text-xl font-black text-soboa-text-dark mb-1">Aucun pronostic</h2>
                <p class="text-gray-500 mb-5 max-w-xs mx-auto text-sm">Démarrez votre course aux points en pronostiquant un match à venir.</p>
                <a href="/matches" class="btn btn-primary btn-md btn-pill">
                    <i data-lucide="target" class="w-4 h-4"></i>
                    Voir les matchs
                </a>
            </div>
        @else

            @php $currentUserId = session('user_id'); @endphp

            {{-- LIVE --}}
            @if($livePredictions->count() > 0)
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-bold text-red-600">En cours</h2>
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                        </span>
                        <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">{{ $livePredictions->count() }}</span>
                    </div>
                    <div class="grid gap-4">
                        @foreach($livePredictions as $prediction)
                            @include('partials.prediction-card', ['prediction' => $prediction, 'borderClass' => 'border-red-500', 'cardClass' => 'live-card', 'badge' => '<span class="live-indicator px-3 py-1 bg-red-600 text-white text-sm font-bold rounded-full flex items-center gap-1"><span class="w-2 h-2 bg-white rounded-full"></span>LIVE</span>', 'showLive' => true])
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- À venir --}}
            @if($scheduledPredictions->count() > 0)
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-bold text-soboa-blue">À venir</h2>
                        <span class="px-2 py-1 bg-soboa-blue/10 text-soboa-blue text-xs font-bold rounded-full">{{ $scheduledPredictions->count() }}</span>
                    </div>
                    <div class="grid gap-4">
                        @foreach($scheduledPredictions as $prediction)
                            @include('partials.prediction-card', ['prediction' => $prediction, 'borderClass' => 'border-soboa-blue', 'cardClass' => '', 'badge' => '<span class="px-3 py-1 bg-soboa-blue/10 text-soboa-blue text-sm font-bold rounded-full">En attente</span>', 'showLive' => false])
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Terminés --}}
            @if($finishedPredictions->count() > 0)
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-bold text-gray-700">Terminés</h2>
                        <span class="px-2 py-1 bg-gray-200 text-gray-700 text-xs font-bold rounded-full">{{ $finishedPredictions->count() }}</span>
                    </div>
                    <div class="grid gap-4">
                        @foreach($finishedPredictions as $prediction)
                            @php
                                $pts = $prediction->points_earned ?? 0;
                                $borderClass = $pts > 0 ? 'border-green-500' : 'border-gray-300';
                                $badge = $pts > 0
                                    ? '<span class="px-3 py-1 bg-green-100 text-green-700 text-sm font-bold rounded-full">+' . $pts . ' pts</span>'
                                    : '<span class="px-3 py-1 bg-gray-200 text-gray-600 text-sm font-bold rounded-full">0 pts</span>';
                            @endphp
                            @include('partials.prediction-card', ['prediction' => $prediction, 'borderClass' => $borderClass, 'cardClass' => '', 'badge' => $badge, 'showLive' => false])
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Statistiques --}}
            <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
                <h2 class="text-lg font-bold text-soboa-blue mb-4">Statistiques</h2>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div class="p-3 bg-soboa-blue/5 rounded-xl">
                        <div class="text-2xl font-black text-soboa-blue">{{ $totalPredictions }}</div>
                        <div class="text-xs text-gray-500 font-medium mt-1">Pronostics</div>
                    </div>
                    <div class="p-3 bg-green-50 rounded-xl">
                        <div class="text-2xl font-black text-green-600">{{ $successfulPredictions }}</div>
                        <div class="text-xs text-gray-500 font-medium mt-1">Réussis</div>
                    </div>
                    <div class="p-3 bg-soboa-orange/10 rounded-xl">
                        <div class="text-2xl font-black text-soboa-orange">{{ $totalPointsEarned }}</div>
                        <div class="text-xs text-gray-500 font-medium mt-1">Points gagnés</div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
