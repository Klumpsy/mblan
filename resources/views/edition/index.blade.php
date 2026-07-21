<x-app-layout>
    <div class="py-16 md:py-24">
        <div class="mx-auto max-w-6xl px-6">
            <div x-data x-reveal>
                <x-forge.heading eyebrow="The Legacy">Editions</x-forge.heading>
            </div>

            <div class="space-y-6">
                @foreach ($editions as $i => $edition)
                    <div x-data x-reveal.{{ ($i % 3) * 120 }}>
                        <x-edition-card :edition="$edition" />
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
