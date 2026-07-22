<x-action-section>
    <x-slot name="title">
        {{ __('Account verwijderen') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Verwijder je account definitief.') }}
    </x-slot>

    <x-slot name="content">
        <div class="max-w-xl text-sm text-forge-steel/70">
            {{ __('Zodra je account is verwijderd, worden alle bijbehorende bronnen en gegevens definitief verwijderd. Download voordat je je account verwijdert eventuele gegevens of informatie die je wilt bewaren.') }}
        </div>

        <div class="mt-5">
            <x-danger-button wire:click="confirmUserDeletion" wire:loading.attr="disabled">
                {{ __('Account verwijderen') }}
            </x-danger-button>
        </div>

        <!-- Delete User Confirmation Modal -->
        <x-dialog-modal wire:model.live="confirmingUserDeletion">
            <x-slot name="title">
                {{ __('Account verwijderen') }}
            </x-slot>

            <x-slot name="content">
                {{ __('Weet je zeker dat je je account wilt verwijderen? Zodra je account is verwijderd, worden alle bijbehorende bronnen en gegevens definitief verwijderd. Voer je wachtwoord in om te bevestigen dat je je account definitief wilt verwijderen.') }}

                <div class="mt-4" x-data="{}" x-on:confirming-delete-user.window="setTimeout(() => $refs.password.focus(), 250)">
                    <x-input type="password" class="mt-1 block w-3/4"
                                autocomplete="current-password"
                                placeholder="{{ __('Wachtwoord') }}"
                                x-ref="password"
                                wire:model="password"
                                wire:keydown.enter="deleteUser" />

                    <x-input-error for="password" class="mt-2" />
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('confirmingUserDeletion')" wire:loading.attr="disabled">
                    {{ __('Annuleren') }}
                </x-secondary-button>

                <x-danger-button class="ms-3" wire:click="deleteUser" wire:loading.attr="disabled">
                    {{ __('Account verwijderen') }}
                </x-danger-button>
            </x-slot>
        </x-dialog-modal>
    </x-slot>
</x-action-section>
