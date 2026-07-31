<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-primary-300">
                {{ session('status') }}
            </div>
        @endif

        {{-- Primary: log in with Discord (same flow as the home page) --}}
        <a href="{{ route('discord.redirect') }}" class="btn-wood clip-corner block w-full text-center text-xs">
            Login met Discord
        </a>

        <div class="my-6 flex items-center gap-3">
            <div class="h-px flex-1 bg-primary-500/15"></div>
            <span class="font-pixel text-[8px] uppercase tracking-widest text-forge-steel/50">of met e-mail</span>
            <div class="h-px flex-1 bg-primary-500/15"></div>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <x-label for="email" value="{{ __('E-mail') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                    autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Wachtwoord') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required
                    autocomplete="current-password" />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-forge-steel/80">{{ __('Onthoud mij') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="text-sm uppercase tracking-wider text-forge-steel/70 transition hover:text-primary-300 focus:outline-none focus:text-primary-300"
                        href="{{ route('password.request') }}">
                        {{ __('Wachtwoord vergeten?') }}
                    </a>
                @endif

                <x-button class="ms-4">
                    {{ __('Inloggen') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
