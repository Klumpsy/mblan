<div class="p-6 lg:p-8">
    <x-forge.heading eyebrow="Snel naar">Ontdek</x-forge.heading>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div x-data x-reveal>
            <x-forge.card class="block h-full">
                <a href="{{ route('schedule') }}" class="block">
                    <h3 class="font-display text-xl font-bold uppercase tracking-wide text-white transition group-hover:text-primary-300">Schema</h3>
                    <p class="mt-2 text-sm text-forge-steel/70 leading-relaxed">Bekijk het speelschema: welke games wanneer draaien tijdens het weekend.</p>
                </a>
            </x-forge.card>
        </div>

        <div x-data x-reveal.100>
            <x-forge.card class="block h-full">
                <a href="{{ route('tournaments') }}" class="block">
                    <h3 class="font-display text-xl font-bold uppercase tracking-wide text-white transition group-hover:text-primary-300">Toernooien</h3>
                    <p class="mt-2 text-sm text-forge-steel/70 leading-relaxed">Volg de schema's, deelnemers en live scores van elk toernooi.</p>
                </a>
            </x-forge.card>
        </div>

        <div x-data x-reveal.200>
            <x-forge.card class="block h-full">
                <a href="{{ route('blogs') }}" class="block">
                    <h3 class="font-display text-xl font-bold uppercase tracking-wide text-white transition group-hover:text-primary-300">Nieuws</h3>
                    <p class="mt-2 text-sm text-forge-steel/70 leading-relaxed">Lees de laatste updates, aankondigingen en verhalen uit de schuur.</p>
                </a>
            </x-forge.card>
        </div>
    </div>
</div>
