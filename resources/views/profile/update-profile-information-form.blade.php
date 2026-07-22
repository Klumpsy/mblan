<x-form-section submit="updateProfileInformation">
    <x-slot name="title">
        {{ __('Profielgegevens') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Werk de profielgegevens en het e-mailadres van je account bij.') }}
    </x-slot>

    <x-slot name="form">
        <!-- Profile Photo -->
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div x-data="{ photoName: null, photoPreview: null }" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                <input type="file" id="photo" class="hidden" wire:model.live="photo" x-ref="photo"
                    x-on:change="
                                    photoName = $refs.photo.files[0].name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        photoPreview = e.target.result;
                                    };
                                    reader.readAsDataURL($refs.photo.files[0]);
                            " />

                <x-label for="photo" value="{{ __('Foto') }}" />

                <!-- Current Profile Photo -->
                <div class="mt-2" x-show="! photoPreview">
                    <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="{{ $this->user->name }}"
                        class="clip-corner metal-edge h-20 w-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div class="mt-2" x-show="photoPreview" style="display: none;">
                    <span class="block clip-corner metal-edge w-20 h-20 bg-cover bg-no-repeat bg-center"
                        x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                    </span>
                </div>

                <x-secondary-button class="mt-2 me-2" type="button" x-on:click.prevent="$refs.photo.click()">
                    {{ __('Nieuwe foto kiezen') }}
                </x-secondary-button>

                @if ($this->user->profile_photo_path)
                    <x-secondary-button type="button" class="mt-2" wire:click="deleteProfilePhoto">
                        {{ __('Foto verwijderen') }}
                    </x-secondary-button>
                @endif

                <x-input-error for="photo" class="mt-2" />
            </div>
        @endif

        <!-- Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="name" value="{{ __('Naam') }}" />
            <x-input id="name" type="text" class="mt-1 block w-full" wire:model="state.name" required
                autocomplete="name" />
            <x-input-error for="name" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="email" value="{{ __('E-mail') }}" />
            <x-input id="email" type="email" class="mt-1 block w-full" wire:model="state.email" required
                autocomplete="username" />
            <x-input-error for="email" class="mt-2" />

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) &&
                    !$this->user->hasVerifiedEmail())
                <p class="text-sm mt-2 text-forge-steel/80">
                    {{ __('Je e-mailadres is niet geverifieerd.') }}

                    <button type="button"
                        class="underline text-sm text-primary-300 hover:text-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 focus:ring-offset-forge-black transition"
                        wire:click.prevent="sendEmailVerification">
                        {{ __('Klik hier om de verificatiemail opnieuw te versturen.') }}
                    </button>
                </p>

                @if ($this->verificationLinkSent)
                    <p class="mt-2 font-medium text-sm text-primary-300">
                        {{ __('Er is een nieuwe verificatielink naar je e-mailadres verstuurd.') }}
                    </p>
                @endif
            @endif
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="discord_id" value="{{ __('Discord ID') }}" />
            <x-input id="discord_id" type="text" class="mt-1 block w-full" wire:model="state.discord_id" />
            <x-input-error for="discord_id" class="mt-2" />
        </div>


    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            {{ __('Opgeslagen.') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled" wire:target="photo">
            {{ __('Opslaan') }}
        </x-button>
    </x-slot>
</x-form-section>
