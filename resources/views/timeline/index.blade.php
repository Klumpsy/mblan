<x-app-layout>
    <div class="relative">
        <div class="pointer-events-none absolute inset-0 bg-grid opacity-30"></div>
        <div class="relative mx-auto max-w-2xl px-6 py-12">
            <x-forge.heading eyebrow="MBLAN26">Foto tijdlijn</x-forge.heading>

            <p class="mb-10 max-w-xl text-sm text-forge-steel/60">
                Deel je mooiste momenten van de LAN. Iedereen kan foto's plaatsen, nieuwste bovenaan.
            </p>

            <livewire:timeline.feed />
        </div>
    </div>
</x-app-layout>
