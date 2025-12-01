<x-layouts.app title="Matchs">
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-800">Calendrier des Matchs</h1>

        <div class="bg-white rounded-xl shadow-sm p-4 sticky top-20 z-40 border border-gray-200">
            <div class="flex gap-2 overflow-x-auto pb-2">
                <button class="px-4 py-2 bg-brand-green text-white rounded-full text-sm font-bold whitespace-nowrap">Tous</button>
                <button class="px-4 py-2 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-full text-sm font-medium whitespace-nowrap">Aujourd'hui</button>
                <button class="px-4 py-2 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-full text-sm font-medium whitespace-nowrap">Demain</button>
                <button class="px-4 py-2 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-full text-sm font-medium whitespace-nowrap">Terminés</button>
            </div>
        </div>

        <div class="space-y-4">
            @forelse($matches as $match)
            <div class="bg-white rounded-lg shadow p-5 border-l-4 {{ $match->status === 'finished' ? 'border-gray-400' : 'border-brand-green' }}">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <span class="text-xs font-bold uppercase text-gray-400 tracking-wide">{{ $match->match_date->format('l d F Y') }}</span>
                        <div class="text-sm text-gray-500">{{ $match->stadium }}</div>
                    </div>
                    @if($match->status === 'finished')
                        <span class="px-2 py-1 bg-gray-200 text-gray-600 text-xs font-bold rounded">Terminé</span>
                    @else
                        <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded">À venir</span>
                    @endif
                </div>

                <div class="flex items-center justify-between">
                    <!-- Team A -->
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-2 shadow-inner">
                            <span class="text-xl font-bold text-gray-400">{{ substr($match->team_a, 0, 1) }}</span>
                        </div>
                        <span class="font-bold text-lg text-center leading-tight">{{ $match->team_a }}</span>
                    </div>

                    <!-- Score / Time -->
                    <div class="px-4 text-center">
                        @if($match->status === 'finished')
                            <div class="text-3xl font-black text-gray-800 tracking-widest">
                                {{ $match->score_a ?? 0 }} - {{ $match->score_b ?? 0 }}
                            </div>
                        @else
                            <div class="text-2xl font-black text-gray-300">VS</div>
                            <div class="text-sm font-bold text-brand-red mt-1">{{ $match->match_date->format('H:i') }}</div>
                        @endif
                    </div>

                    <!-- Team B -->
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-2 shadow-inner">
                            <span class="text-xl font-bold text-gray-400">{{ substr($match->team_b, 0, 1) }}</span>
                        </div>
                        <span class="font-bold text-lg text-center leading-tight">{{ $match->team_b }}</span>
                    </div>
                </div>

                @if($match->status !== 'finished')
                <div class="mt-6 border-t pt-4">
                    <button class="w-full bg-brand-yellow hover:bg-yellow-400 text-brand-dark font-bold py-3 px-4 rounded-lg shadow transition transform active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Faire un pronostic
                    </button>
                </div>
                @endif
            </div>
            @empty
            <div class="text-center py-10">
                <p class="text-gray-500">Aucun match trouvé.</p>
            </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
