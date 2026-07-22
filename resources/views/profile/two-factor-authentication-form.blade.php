<x-action-section>
    <x-slot name="title">
        {{ __('Tweestapsverificatie') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Voeg extra beveiliging toe aan je account met tweestapsverificatie.') }}
    </x-slot>

    <x-slot name="content">
        <h3 class="font-display text-lg font-bold uppercase tracking-wide text-white">
            @if ($this->enabled)
                @if ($showingConfirmation)
                    {{ __('Voltooi het inschakelen van tweestapsverificatie.') }}
                @else
                    {{ __('Je hebt tweestapsverificatie ingeschakeld.') }}
                @endif
            @else
                {{ __('Je hebt tweestapsverificatie niet ingeschakeld.') }}
            @endif
        </h3>

        <div class="mt-3 max-w-xl text-sm text-forge-steel/70">
            <p>
                {{ __('Wanneer tweestapsverificatie is ingeschakeld, word je bij het inloggen gevraagd om een beveiligde, willekeurige code. Deze code haal je op uit de Google Authenticator-app op je telefoon.') }}
            </p>
        </div>

        @if ($this->enabled)
            @if ($showingQrCode)
                <div class="mt-4 max-w-xl text-sm text-forge-steel/70">
                    <p class="font-semibold text-white">
                        @if ($showingConfirmation)
                            {{ __('Scan de onderstaande QR-code met de authenticator-app op je telefoon, of voer de installatiesleutel in en geef de gegenereerde OTP-code op om het inschakelen van tweestapsverificatie te voltooien.') }}
                        @else
                            {{ __('Tweestapsverificatie is nu ingeschakeld. Scan de onderstaande QR-code met de authenticator-app op je telefoon, of voer de installatiesleutel in.') }}
                        @endif
                    </p>
                </div>

                <div class="mt-4 p-2 inline-block clip-corner metal-edge bg-white">
                    {!! $this->user->twoFactorQrCodeSvg() !!}
                </div>

                <div class="mt-4 max-w-xl text-sm text-forge-steel/70">
                    <p class="font-semibold text-white">
                        {{ __('Installatiesleutel') }}: {{ decrypt($this->user->two_factor_secret) }}
                    </p>
                </div>

                @if ($showingConfirmation)
                    <div class="mt-4">
                        <x-label for="code" value="{{ __('Code') }}" />

                        <x-input id="code" type="text" name="code" class="block mt-1 w-1/2" inputmode="numeric" autofocus autocomplete="one-time-code"
                            wire:model="code"
                            wire:keydown.enter="confirmTwoFactorAuthentication" />

                        <x-input-error for="code" class="mt-2" />
                    </div>
                @endif
            @endif

            @if ($showingRecoveryCodes)
                <div class="mt-4 max-w-xl text-sm text-forge-steel/70">
                    <p class="font-semibold text-white">
                        {{ __('Bewaar deze herstelcodes in een veilige wachtwoordmanager. Je kunt ze gebruiken om weer toegang tot je account te krijgen als je je apparaat voor tweestapsverificatie kwijtraakt.') }}
                    </p>
                </div>

                <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm clip-corner metal-edge text-forge-steel">
                    @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
                        <div>{{ $code }}</div>
                    @endforeach
                </div>
            @endif
        @endif

        <div class="mt-5">
            @if (! $this->enabled)
                <x-confirms-password wire:then="enableTwoFactorAuthentication">
                    <x-button type="button" wire:loading.attr="disabled">
                        {{ __('Inschakelen') }}
                    </x-button>
                </x-confirms-password>
            @else
                @if ($showingRecoveryCodes)
                    <x-confirms-password wire:then="regenerateRecoveryCodes">
                        <x-secondary-button class="me-3">
                            {{ __('Herstelcodes opnieuw genereren') }}
                        </x-secondary-button>
                    </x-confirms-password>
                @elseif ($showingConfirmation)
                    <x-confirms-password wire:then="confirmTwoFactorAuthentication">
                        <x-button type="button" class="me-3" wire:loading.attr="disabled">
                            {{ __('Bevestigen') }}
                        </x-button>
                    </x-confirms-password>
                @else
                    <x-confirms-password wire:then="showRecoveryCodes">
                        <x-secondary-button class="me-3">
                            {{ __('Herstelcodes tonen') }}
                        </x-secondary-button>
                    </x-confirms-password>
                @endif

                @if ($showingConfirmation)
                    <x-confirms-password wire:then="disableTwoFactorAuthentication">
                        <x-secondary-button wire:loading.attr="disabled">
                            {{ __('Annuleren') }}
                        </x-secondary-button>
                    </x-confirms-password>
                @else
                    <x-confirms-password wire:then="disableTwoFactorAuthentication">
                        <x-danger-button wire:loading.attr="disabled">
                            {{ __('Uitschakelen') }}
                        </x-danger-button>
                    </x-confirms-password>
                @endif

            @endif
        </div>
    </x-slot>
</x-action-section>
