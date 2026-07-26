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
            <div class="col-span-6 sm:col-span-4" x-data="{
                photoName: null,
                photoPreview: null,
                processing: false,

                async handleSelect(event) {
                    const file = event.target.files[0];
                    if (! file) return;

                    this.processing = true;
                    let upload = file;
                    try {
                        upload = await this.compress(file);
                    } catch (e) {
                        upload = file; // undecodable format -> let the server rules decide
                    }

                    this.photoName = upload.name;
                    this.photoPreview = URL.createObjectURL(upload);

                    this.$wire.upload('photo', upload,
                        () => { this.processing = false; },
                        () => { this.processing = false; },
                    );
                },

                async compress(file) {
                    if (! file.type.startsWith('image/')) return file;

                    const dataUrl = await new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = () => resolve(reader.result);
                        reader.onerror = reject;
                        reader.readAsDataURL(file);
                    });

                    const image = await new Promise((resolve, reject) => {
                        const img = new Image();
                        img.onload = () => resolve(img);
                        img.onerror = reject;
                        img.src = dataUrl;
                    });

                    const maxDim = 1024;
                    let width = image.width;
                    let height = image.height;
                    if (width > maxDim || height > maxDim) {
                        const scale = Math.min(maxDim / width, maxDim / height);
                        width = Math.round(width * scale);
                        height = Math.round(height * scale);
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    canvas.getContext('2d').drawImage(image, 0, 0, width, height);

                    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.8));
                    if (! blob) return file; // toBlob unsupported -> fall back to original

                    const base = file.name.replace(/\.[^.]+$/, '') || 'foto';
                    return new File([blob], base + '.jpg', { type: 'image/jpeg', lastModified: Date.now() });
                },
            }">
                <!-- Profile Photo File Input -->
                <input type="file" id="photo" class="hidden" x-ref="photo" accept="image/*"
                    x-on:change="handleSelect($event)" />

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

                <p class="mt-2 text-sm text-forge-steel/80" x-show="processing" style="display: none;">
                    {{ __('Foto wordt verwerkt...') }}
                </p>

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
