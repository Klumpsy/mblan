<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div x-data="{ recovery: false }">
            <div class="mb-4 text-sm text-forge-steel/80" x-show="! recovery">
                {{ __('Bevestig de toegang tot je account door de code in te voeren uit je authenticator-app.') }}
            </div>

            <div class="mb-4 text-sm text-forge-steel/80" x-cloak x-show="recovery">
                {{ __('Bevestig de toegang tot je account door een van je herstelcodes in te voeren.') }}
            </div>

            <x-validation-errors class="mb-4" />

            <form method="POST" action="{{ route('two-factor.login') }}">
                @csrf

                <div class="mt-4" x-show="! recovery">
                    <x-label for="code" value="{{ __('Code') }}" /> {{-- 'Code' is identical in Dutch --}}
                    <x-input id="code" class="block mt-1 w-full" type="text" inputmode="numeric" name="code" autofocus x-ref="code" autocomplete="one-time-code" />
                </div>

                <div class="mt-4" x-cloak x-show="recovery">
                    <x-label for="recovery_code" value="{{ __('Herstelcode') }}" />
                    <x-input id="recovery_code" class="block mt-1 w-full" type="text" name="recovery_code" x-ref="recovery_code" autocomplete="one-time-code" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <button type="button" class="text-sm uppercase tracking-wider text-forge-steel/70 transition hover:text-primary-300 cursor-pointer"
                                    x-show="! recovery"
                                    x-on:click="
                                        recovery = true;
                                        $nextTick(() => { $refs.recovery_code.focus() })
                                    ">
                        {{ __('Gebruik een herstelcode') }}
                    </button>

                    <button type="button" class="text-sm uppercase tracking-wider text-forge-steel/70 transition hover:text-primary-300 cursor-pointer"
                                    x-cloak
                                    x-show="recovery"
                                    x-on:click="
                                        recovery = false;
                                        $nextTick(() => { $refs.code.focus() })
                                    ">
                        {{ __('Gebruik een authenticatiecode') }}
                    </button>

                    <x-button class="ms-4">
                        {{ __('Inloggen') }}
                    </x-button>
                </div>
            </form>
        </div>
    </x-authentication-card>
</x-guest-layout>
