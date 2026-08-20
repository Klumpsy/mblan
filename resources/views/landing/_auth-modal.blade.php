@php([$brandBase, $brandAccent] = \App\Models\Edition::currentBrand())

<div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-6">
    <div class="absolute inset-0 bg-forge-black/40" @click="open = false"></div>
    <div x-show="open" x-transition class="frame-wood relative w-full max-w-md p-8">
        <button type="button" @click="open = false" class="absolute right-3 top-3 font-pixel text-xs text-forge-steel/60 hover:text-primary-300">X</button>
        <div class="mb-1 font-pixel text-[8px] uppercase tracking-[0.2em] text-primary-400">De schuur is open</div>
        <h2 class="mb-6 font-display text-2xl font-bold uppercase tracking-wide text-white">Welkom bij {{ $brandBase }}@if ($brandAccent !== '')<span class="text-primary-400">{{ $brandAccent }}</span>@endif</h2>

        @auth
            <a href="{{ route('schedule') }}" class="btn-wood clip-corner w-full text-xs">Betreed De Schuur</a>
        @else
            <div x-data="{ showEmail: {{ $errors->any() ? 'true' : 'false' }} }">
                <a href="{{ route('discord.redirect') }}" class="btn-wood clip-corner block w-full text-center text-xs">Login met Discord</a>

                <button type="button" @click="showEmail = !showEmail"
                    class="mt-4 w-full text-center font-pixel text-[8px] uppercase tracking-widest text-forge-steel/50 hover:text-primary-300">
                    <span x-show="!showEmail">Inloggen met e-mail</span>
                    <span x-show="showEmail" x-cloak>Verberg e-mail login</span>
                </button>

                <div x-show="showEmail" x-cloak x-transition class="mt-4 border-t border-primary-500/15 pt-4">
                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf
                        <x-validation-errors />
                        <div>
                            <x-label for="email" value="E-mail" />
                            <x-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required />
                        </div>
                        <div>
                            <x-label for="password" value="Wachtwoord" />
                            <x-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
                        </div>
                        <label class="flex items-center">
                            <x-checkbox name="remember" />
                            <span class="ms-2 text-sm text-forge-steel/70">Onthoud mij</span>
                        </label>
                        <button type="submit" class="btn-wood clip-corner w-full text-xs">Inloggen</button>
                    </form>
                    <div class="mt-6 flex items-center justify-between font-pixel text-[8px] uppercase tracking-widest">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-forge-steel/60 hover:text-primary-300">Wachtwoord?</a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-primary-300 hover:text-primary-200">Registreren</a>
                        @endif
                    </div>
                </div>
            </div>
        @endauth
    </div>
</div>
