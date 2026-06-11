@php
    $userId       = session('user_id');
    $likeCount    = $prediction->likes->count();
    $commentCount = $prediction->comments->count();
    $isLiked      = $userId ? $prediction->likes->where('user_id', $userId)->isNotEmpty() : false;
    $likeRoute    = route('predictions.like', $prediction->id);
    $commentRoute = route('predictions.comments.store', $prediction->id);
@endphp

<div
    x-data="{
        liked: {{ $isLiked ? 'true' : 'false' }},
        likeCount: {{ $likeCount }},
        commentCount: {{ $commentCount }},
        showComments: false,
        commentBody: '',
        comments: {{ $prediction->comments->map(fn($c) => ['id' => $c->id, 'user_name' => $c->user->name, 'body' => $c->body, 'created_at' => $c->created_at->diffForHumans(), 'is_mine' => $c->user_id == $userId])->toJson() }},
        submitting: false,
        async toggleLike() {
            @if(!$userId)
                window.location = '{{ route('login') }}';
                return;
            @endif
            const prev = this.liked;
            this.liked = !this.liked;
            this.likeCount += this.liked ? 1 : -1;
            try {
                const res = await fetch('{{ $likeRoute }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.liked = data.liked;
                this.likeCount = data.count;
            } catch(e) {
                this.liked = prev;
                this.likeCount += this.liked ? 1 : -1;
            }
        },
        async postComment() {
            if (!this.commentBody.trim() || this.submitting) return;
            @if(!$userId)
                window.location = '{{ route('login') }}';
                return;
            @endif
            this.submitting = true;
            try {
                const res = await fetch('{{ $commentRoute }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ body: this.commentBody })
                });
                const data = await res.json();
                if (res.status === 201) {
                    this.comments.push({ id: data.id, user_name: data.user_name, body: data.body, created_at: data.created_at, is_mine: true });
                    this.commentCount = data.count;
                    this.commentBody = '';
                }
            } finally { this.submitting = false; }
        },
        async deleteComment(commentId, idx) {
            if (!confirm('Supprimer ce commentaire ?')) return;
            await fetch(`/predictions/{{ $prediction->id }}/comments/${commentId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
            });
            this.comments.splice(idx, 1);
            this.commentCount = Math.max(0, this.commentCount - 1);
        }
    }"
    class="{{ $cardClass }} bg-white rounded-xl shadow-sm p-5 border-l-4 {{ $borderClass }} hover:shadow-md transition-shadow"
>
    {{-- Header: date + badge --}}
    <div class="flex justify-between items-start mb-3">
        <div class="flex flex-col gap-0.5">
            <span class="text-xs text-gray-500">{{ $prediction->match->match_date->translatedFormat('l d F Y - H:i') }}</span>
            @if($prediction->match->match_name ?? false)
                <span class="text-xs font-semibold text-soboa-blue">{{ $prediction->match->match_name }}</span>
            @endif
        </div>
        <div>{!! $badge !!}</div>
    </div>

    {{-- Score --}}
    <div class="flex items-center justify-between mb-3">
        <div class="flex-1 text-center">
            <span class="font-black text-gray-800 text-sm">{{ \App\Models\Team::fr($prediction->match->team_a) }}</span>
        </div>
        <div class="px-4 text-center min-w-[120px]">
            <div class="text-xl font-black text-soboa-orange">
                {{ $prediction->score_a }} – {{ $prediction->score_b }}
            </div>
            <div class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">Votre pronostic</div>
            @if($showLive && $prediction->match->score_a !== null)
                <div class="mt-1.5 pt-1.5 border-t border-red-200">
                    <div class="text-lg font-black text-red-600">{{ $prediction->match->score_a }} – {{ $prediction->match->score_b }}</div>
                    <div class="text-[10px] text-red-400 font-bold uppercase tracking-wide">Score actuel</div>
                </div>
            @elseif(!$showLive && $prediction->match->status === 'finished' && $prediction->match->score_a !== null)
                <div class="mt-1.5 pt-1.5 border-t border-gray-200">
                    <div class="text-lg font-black text-gray-700">{{ $prediction->match->score_a }} – {{ $prediction->match->score_b }}</div>
                    <div class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">Score final</div>
                </div>
            @endif
        </div>
        <div class="flex-1 text-center">
            <span class="font-black text-gray-800 text-sm">{{ \App\Models\Team::fr($prediction->match->team_b) }}</span>
        </div>
    </div>

    {{-- Social bar --}}
    <div class="flex items-center gap-3 pt-3 border-t border-gray-100">
        {{-- Like button --}}
        <button
            @click="toggleLike()"
            :class="liked ? 'text-soboa-orange' : 'text-gray-400 hover:text-soboa-orange'"
            class="flex items-center gap-1.5 text-sm font-bold transition-colors focus:outline-none"
        >
            <svg :class="liked ? 'like-pop' : ''" class="w-5 h-5" :fill="liked ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <span x-text="likeCount"></span>
        </button>

        {{-- Comment toggle --}}
        <button
            @click="showComments = !showComments"
            class="flex items-center gap-1.5 text-sm font-bold text-gray-400 hover:text-soboa-blue transition-colors focus:outline-none"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span x-text="commentCount + ' commentaire' + (commentCount > 1 ? 's' : '')"></span>
        </button>

        {{-- User badge --}}
        <div class="ml-auto flex items-center gap-1.5">
            <div class="w-6 h-6 rounded-full bg-soboa-blue flex items-center justify-center text-white text-xs font-black">
                {{ substr($prediction->user->name ?? '?', 0, 1) }}
            </div>
            <span class="text-xs text-gray-500 font-medium">{{ $prediction->user->name ?? 'Utilisateur' }}</span>
        </div>
    </div>

    {{-- Comments panel --}}
    <div x-show="showComments" x-collapse class="mt-3 pt-3 border-t border-gray-100 space-y-3">

        {{-- Comment list --}}
        <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
            <template x-for="(comment, idx) in comments" :key="comment.id">
                <div class="flex gap-2 group">
                    <div class="w-7 h-7 rounded-full bg-soboa-orange/20 flex items-center justify-center text-soboa-orange font-black text-xs flex-shrink-0 mt-0.5">
                        <span x-text="comment.user_name ? comment.user_name[0].toUpperCase() : '?'"></span>
                    </div>
                    <div class="flex-1 bg-gray-50 rounded-xl px-3 py-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-black text-soboa-blue" x-text="comment.user_name"></span>
                            <span class="text-[10px] text-gray-400" x-text="comment.created_at"></span>
                        </div>
                        <p class="text-sm text-gray-700 mt-0.5" x-text="comment.body"></p>
                    </div>
                    <button
                        x-show="comment.is_mine"
                        @click="deleteComment(comment.id, idx)"
                        class="opacity-0 group-hover:opacity-100 text-gray-300 hover:text-red-500 transition self-start mt-1 focus:outline-none"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
            <p x-show="comments.length === 0" class="text-xs text-gray-400 text-center py-2">Soyez le premier à commenter !</p>
        </div>

        {{-- Comment form --}}
        @if($userId)
            <form @submit.prevent="postComment()" class="flex gap-2">
                <input
                    x-model="commentBody"
                    type="text"
                    maxlength="500"
                    placeholder="Votre commentaire…"
                    class="flex-1 text-sm border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-soboa-orange/40 focus:border-soboa-orange transition"
                />
                <button
                    type="submit"
                    :disabled="!commentBody.trim() || submitting"
                    class="px-4 py-2 bg-soboa-orange text-white text-sm font-bold rounded-xl disabled:opacity-40 hover:bg-soboa-orange-secondary transition"
                >
                    <span x-show="!submitting">Envoyer</span>
                    <span x-show="submitting">…</span>
                </button>
            </form>
        @else
            <p class="text-xs text-center text-gray-400">
                <a href="{{ route('login') }}" class="text-soboa-orange font-bold hover:underline">Connectez-vous</a> pour commenter.
            </p>
        @endif
    </div>
</div>
