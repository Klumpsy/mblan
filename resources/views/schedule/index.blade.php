<x-app-layout>
    <div class="relative">
        <div class="pointer-events-none absolute inset-0 bg-grid opacity-30"></div>
        <div class="relative mx-auto max-w-7xl px-6 py-12">
            {{-- heading with a farmer standing on top + grazing critters --}}
            <div class="relative">
                <img src="{{ asset('images/farm/tile_0109.png') }}" alt="" aria-hidden="true"
                    class="pixel pointer-events-none absolute -top-9 left-1 z-10 w-11"
                    style="animation: sprite-bob .5s steps(2,end) infinite;" />
                <img src="{{ asset('images/farm/tile_0121.png') }}" alt="" aria-hidden="true"
                    class="pixel pointer-events-none absolute -top-7 right-2 z-10 w-12"
                    style="animation: float 6s ease-in-out infinite;" />
                <img src="{{ asset('images/farm/tile_0122.png') }}" alt="" aria-hidden="true"
                    class="pixel pointer-events-none absolute -top-6 right-20 z-10 w-8"
                    style="animation: float 5s ease-in-out infinite;" />

                <x-forge.heading eyebrow="{{ $edition->name }}">Speelschema</x-forge.heading>
            </div>

            <livewire:edition.schedule :edition="$edition" />
        </div>
    </div>
</x-app-layout>
