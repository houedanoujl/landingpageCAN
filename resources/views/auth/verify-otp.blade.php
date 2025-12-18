<x-layouts.app title="Vérification OTP">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-soboa-blue/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-soboa-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-soboa-blue">Vérification</h1>
                <p class="text-gray-600 mt-2">Entrez le code reçu par SMS</p>
            </div>

            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            @endif

            @if(session('info'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-lg mb-6" role="alert">
                <span class="font-medium">{{ session('info') }}</span>
            </div>
            @endif

            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6" role="alert">
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            @endif

            <form action="{{ route('auth.verify-otp') }}" method="POST" class="space-y-6">
                @csrf
                
                <div>
                    <label for="otp_code" class="block text-sm font-medium text-gray-700 mb-2 text-center">
                        Code de vérification (6 chiffres)
                    </label>
                    <input type="text" 
                           id="otp_code" 
                           name="otp_code" 
                           placeholder="000000"
                           maxlength="6"
                           pattern="[0-9]{6}"
                           inputmode="numeric"
                           autocomplete="one-time-code"
                           class="w-full px-4 py-4 text-center text-3xl font-bold tracking-[0.5em] border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-soboa-orange focus:border-soboa-orange"
                           required
                           autofocus>
                </div>
                
                <button type="submit" 
                        class="w-full bg-soboa-orange hover:bg-orange-600 text-black font-bold py-3 px-4 rounded-lg shadow transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Vérifier
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-200 text-center space-y-4">
                <p class="text-sm text-gray-600">
                    Vous n'avez pas reçu le code ?
                </p>
                <a href="/login" class="text-soboa-blue hover:underline font-medium">
                    ← Renvoyer le code
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
