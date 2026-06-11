<x-layouts.app title="Admin - Conditions générales">
    <div class="bg-gray-100 min-h-screen py-8">
        <div class="max-w-4xl mx-auto px-4">

            <!-- Header -->
            <div class="mb-8">
                <a href="{{ route('admin.dashboard') }}" class="text-soboa-orange hover:underline font-bold mb-2 inline-block">
                    ← Retour au dashboard
                </a>
                <h1 class="text-3xl font-black text-soboa-blue flex items-center gap-3">
                    <span class="text-4xl">📜</span> Conditions générales
                </h1>
                <p class="text-gray-600 mt-2">
                    Éditez le contenu de la page publique
                    <a href="{{ route('terms') }}" target="_blank" rel="noopener" class="text-soboa-orange hover:underline font-bold">/conditions</a>.
                    Laissez vide pour revenir à la version statique d'origine.
                </p>
            </div>

            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                ✅ {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('admin.terms.update') }}" method="POST" class="bg-white rounded-xl shadow-lg p-6"
                  x-data="{ preview: false, content: @js(old('terms_content', $settings->terms_content ?? '')) }">
                @csrf
                @method('PUT')

                <div class="flex items-center justify-between mb-4">
                    <div>
                        <label for="terms_content" class="block text-gray-700 font-bold">Contenu (HTML)</label>
                        @if($settings->terms_updated_at)
                            <p class="text-xs text-gray-500 mt-1">Dernière mise à jour : {{ $settings->terms_updated_at->format('d/m/Y à H:i') }}</p>
                        @else
                            <p class="text-xs text-gray-500 mt-1">Version statique d'origine actuellement affichée.</p>
                        @endif
                    </div>
                    <div class="flex rounded-lg overflow-hidden ring-1 ring-gray-300 text-sm font-bold">
                        <button type="button" @click="preview = false"
                                :class="!preview ? 'bg-soboa-blue text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                                class="px-4 py-2 transition-colors">Éditer</button>
                        <button type="button" @click="preview = true"
                                :class="preview ? 'bg-soboa-blue text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                                class="px-4 py-2 transition-colors">Aperçu</button>
                    </div>
                </div>

                <textarea id="terms_content" name="terms_content" rows="24" x-model="content" x-show="!preview"
                          placeholder="<section>&#10;    <h2>Article 1 - Objet</h2>&#10;    <p>…</p>&#10;</section>"
                          class="w-full border border-gray-300 rounded-lg p-4 font-mono text-sm leading-relaxed focus:ring-2 focus:ring-soboa-orange focus:outline-none"></textarea>

                <div x-show="preview" x-cloak
                     class="w-full border border-gray-200 rounded-lg p-6 bg-gray-50 prose-headings:text-soboa-blue space-y-4 min-h-[200px] overflow-auto"
                     x-html="content || '<p class=\'text-gray-400\'>Aucun contenu — la version statique sera affichée.</p>'"></div>

                <div class="flex items-center justify-between mt-6">
                    <p class="text-xs text-gray-500">
                        Balises HTML acceptées (h2, p, ul, strong…). Le contenu est affiché tel quel sur la page publique.
                    </p>
                    <button type="submit" class="bg-soboa-orange hover:bg-soboa-orange/90 text-white font-bold py-3 px-8 rounded-lg transition-colors">
                        💾 Enregistrer
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-layouts.app>
