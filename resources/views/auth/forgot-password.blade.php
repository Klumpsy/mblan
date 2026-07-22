<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-4 text-sm text-forge-steel/80">
            {{ __('Wachtwoord vergeten? Geen probleem. Laat je e-mailadres achter en we sturen je een reset-link waarmee je een nieuw wachtwoord kunt kiezen.') }}
        </div>

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-primary-300">
                {{ session('status') }}
            </div>
        @endif

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="block">
                <x-label for="email" value="{{ __('E-mail') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-button>
                    {{ __('Stuur reset-link') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
