<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div>
                <x-label for="name" value="{{ __('Naam') }}" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            </div>

            <div class="mt-4">
                <x-label for="email" value="{{ __('E-mail') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Wachtwoord') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Bevestig wachtwoord') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <x-label for="terms">
                        <div class="flex items-center">
                            <x-checkbox name="terms" id="terms" required />

                            <div class="ms-2">
                                {!! __('Ik ga akkoord met de :terms_of_service en het :privacy_policy', [
                                        'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline text-sm text-primary-300 transition hover:text-primary-200 focus:outline-none">'.__('Servicevoorwaarden').'</a>',
                                        'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline text-sm text-primary-300 transition hover:text-primary-200 focus:outline-none">'.__('Privacybeleid').'</a>',
                                ]) !!}
                            </div>
                        </div>
                    </x-label>
                </div>
            @endif

            <div class="flex items-center justify-end mt-4">
                <a class="text-sm uppercase tracking-wider text-forge-steel/70 transition hover:text-primary-300 focus:outline-none focus:text-primary-300" href="{{ route('login') }}">
                    {{ __('Al geregistreerd?') }}
                </a>

                <x-button class="ms-4">
                    {{ __('Registreren') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
