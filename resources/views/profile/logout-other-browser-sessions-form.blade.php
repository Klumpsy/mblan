<x-action-section>
    <x-slot name="title">
        {{ __('Browsersessies') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Beheer je actieve sessies op andere browsers en apparaten en log ze uit.') }}
    </x-slot>

    <x-slot name="content">
        <div class="max-w-xl text-sm text-forge-steel/70">
            {{ __('Indien nodig kun je al je andere browsersessies op al je apparaten uitloggen. Enkele van je recente sessies staan hieronder, maar deze lijst is mogelijk niet volledig. Als je vermoedt dat je account is gecompromitteerd, wijzig dan ook je wachtwoord.') }}
        </div>

        @if (count($this->sessions) > 0)
            <div class="mt-5 space-y-6">
                <!-- Other Browser Sessions -->
                @foreach ($this->sessions as $session)
                    <div class="flex items-center">
                        <div>
                            @if ($session->agent->isDesktop())
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-primary-400/70">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-primary-400/70">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                </svg>
                            @endif
                        </div>

                        <div class="ms-3">
                            <div class="text-sm text-forge-steel">
                                {{ $session->agent->platform() ? $session->agent->platform() : __('Onbekend') }} - {{ $session->agent->browser() ? $session->agent->browser() : __('Onbekend') }}
                            </div>

                            <div>
                                <div class="text-xs text-forge-steel/60">
                                    {{ $session->ip_address }},

                                    @if ($session->is_current_device)
                                        <span class="text-primary-300 font-semibold">{{ __('Dit apparaat') }}</span>
                                    @else
                                        {{ __('Laatst actief') }} {{ $session->last_active }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="flex items-center mt-5">
            <x-button wire:click="confirmLogout" wire:loading.attr="disabled">
                {{ __('Andere browsersessies uitloggen') }}
            </x-button>

            <x-action-message class="ms-3" on="loggedOut">
                {{ __('Klaar.') }}
            </x-action-message>
        </div>

        <!-- Log Out Other Devices Confirmation Modal -->
        <x-dialog-modal wire:model.live="confirmingLogout">
            <x-slot name="title">
                {{ __('Andere browsersessies uitloggen') }}
            </x-slot>

            <x-slot name="content">
                {{ __('Voer je wachtwoord in om te bevestigen dat je je andere browsersessies op al je apparaten wilt uitloggen.') }}

                <div class="mt-4" x-data="{}" x-on:confirming-logout-other-browser-sessions.window="setTimeout(() => $refs.password.focus(), 250)">
                    <x-input type="password" class="mt-1 block w-3/4"
                                autocomplete="current-password"
                                placeholder="{{ __('Wachtwoord') }}"
                                x-ref="password"
                                wire:model="password"
                                wire:keydown.enter="logoutOtherBrowserSessions" />

                    <x-input-error for="password" class="mt-2" />
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('confirmingLogout')" wire:loading.attr="disabled">
                    {{ __('Annuleren') }}
                </x-secondary-button>

                <x-button class="ms-3"
                            wire:click="logoutOtherBrowserSessions"
                            wire:loading.attr="disabled">
                    {{ __('Andere browsersessies uitloggen') }}
                </x-button>
            </x-slot>
        </x-dialog-modal>
    </x-slot>
</x-action-section>
