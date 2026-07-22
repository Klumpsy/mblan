<x-app-layout>
    <div class="relative">
        <div class="pointer-events-none absolute inset-0 bg-grid opacity-30"></div>
        <div class="relative mx-auto max-w-7xl px-6 py-12">
            <x-forge.heading eyebrow="{{ $edition->name }}">Speelschema</x-forge.heading>

            <livewire:edition.schedule :edition="$edition" />
        </div>
    </div>
</x-app-layout>
