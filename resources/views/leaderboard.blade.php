<x-layouts.app title="Classement">
    <div class="space-y-6">
        <div class="text-center py-6">
            <h1 class="text-3xl font-bold text-gray-800">Classement Général</h1>
            <p class="text-gray-500 mt-2">Les meilleurs experts du football africain</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
            <!-- Top 3 Podium (Visual) -->
            <div class="bg-soboa-orange p-6 text-black pb-10">
                <div class="flex justify-center items-end gap-4">
                    <!-- 2nd Place -->
                    @if(isset($users[1]))
                        <div class="flex flex-col items-center">
                            <div
                                class="w-16 h-16 rounded-full border-4 border-gray-300 bg-gray-700 flex items-center justify-center text-xl font-bold mb-2">
                                {{ substr($users[1]->name, 0, 1) }}
                            </div>
                            <div class="text-center">
                                <div class="font-bold text-sm">{{ $users[1]->name }}</div>
                                <div class="text-black/60 text-xs">{{ $users[1]->points_total }} pts</div>
                            </div>
                            <div
                                class="h-20 w-16 bg-gradient-to-b from-gray-300 to-gray-400 mt-2 rounded-t-lg flex items-center justify-center text-2xl font-bold text-gray-800 shadow-lg">
                                2</div>
                        </div>
                    @endif

                    <!-- 1st Place -->
                    @if(isset($users[0]))
                        <div class="flex flex-col items-center z-10">
                            <div
                                class="w-20 h-20 rounded-full border-4 border-black bg-gray-700 flex items-center justify-center text-3xl font-bold mb-2 text-yellow-400">
                                {{ substr($users[0]->name, 0, 1) }}
                            </div>
                            <div class="text-center">
                                <div class="font-bold text-lg text-black">{{ $users[0]->name }}</div>
                                <div class="text-black/70 text-sm">{{ $users[0]->points_total }} pts</div>
                            </div>
                            <div
                                class="h-28 w-20 bg-gradient-to-b from-yellow-300 to-yellow-500 mt-2 rounded-t-lg flex items-center justify-center text-4xl font-bold text-yellow-900 shadow-lg">
                                1</div>
                        </div>
                    @endif

                    <!-- 3rd Place -->
                    @if(isset($users[2]))
                        <div class="flex flex-col items-center">
                            <div
                                class="w-16 h-16 rounded-full border-4 border-black/30 bg-gray-700 flex items-center justify-center text-xl font-bold mb-2">
                                {{ substr($users[2]->name, 0, 1) }}
                            </div>
                            <div class="text-center">
                                <div class="font-bold text-sm">{{ $users[2]->name }}</div>
                                <div class="text-black/60 text-xs">{{ $users[2]->points_total }} pts</div>
                            </div>
                            <div
                                class="h-16 w-16 bg-gradient-to-b from-orange-300 to-orange-500 mt-2 rounded-t-lg flex items-center justify-center text-2xl font-bold text-orange-900 shadow-lg">
                                3</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Rang</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Joueur</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">
                                Points</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($users as $index => $user)
                            @php
                                $isCurrentUser = $user->id == session('user_id');
                            @endphp
                            <tr
                                class="{{ $isCurrentUser ? 'bg-orange-50 border-l-4 border-soboa-orange' : 'hover:bg-gray-50 transition' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="text-gray-900 font-bold">#{{ $index + 1 + ($users->currentPage() - 1) * $users->perPage() }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="h-8 w-8 rounded-full {{ $isCurrentUser ? 'bg-soboa-orange' : 'bg-gray-200 text-gray-600' }} text-black flex items-center justify-center font-bold text-xs mr-3">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div
                                            class="text-sm font-medium {{ $isCurrentUser ? 'text-soboa-orange font-bold' : 'text-gray-900' }}">
                                            {{ $user->name }}
                                            @if($isCurrentUser)
                                                <span
                                                    class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-soboa-orange text-black">Vous</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold {{ $isCurrentUser ? 'text-soboa-orange' : 'text-gray-700' }}">
                                    {{ $user->points_total }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>