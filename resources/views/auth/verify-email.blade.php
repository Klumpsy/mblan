<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-4 text-sm text-forge-steel/80">
            {{ __('Voordat je verdergaat, kun je je e-mailadres bevestigen door op de link te klikken die we je zojuist hebben gestuurd? Heb je de e-mail niet ontvangen, dan sturen we je met plezier een nieuwe.') }}
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-primary-300">
                {{ __('Er is een nieuwe verificatielink gestuurd naar het e-mailadres uit je profielinstellingen.') }}
            </div>
        @endif

        <div class="mt-4 flex items-center justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf

                <div>
                    <x-button type="submit">
                        {{ __('Verificatiemail opnieuw sturen') }}
                    </x-button>
                </div>
            </form>

            <div>
                <a
                    href="{{ route('profile.show') }}"
                    class="text-sm uppercase tracking-wider text-forge-steel/70 transition hover:text-primary-300 focus:outline-none focus:text-primary-300"
                >
                    {{ __('Profiel bewerken') }}</a>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf

                    <button type="submit" class="text-sm uppercase tracking-wider text-forge-steel/70 transition hover:text-primary-300 focus:outline-none focus:text-primary-300 ms-2">
                        {{ __('Uitloggen') }}
                    </button>
                </form>
            </div>
        </div>
    </x-authentication-card>
</x-guest-layout>
