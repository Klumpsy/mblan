<x-app-layout>
    <div class="relative">
        <div class="pointer-events-none absolute inset-0 bg-grid opacity-30"></div>
        <div class="relative mx-auto max-w-6xl px-6 py-12">

            <x-forge.heading eyebrow="Archief">Edities</x-forge.heading>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($editions as $edition)
                    <a href="{{ $edition->is_active ? route('schedule') : route('editions.show', $edition) }}"
                        class="group frame-wood block p-6 transition hover:-translate-y-0.5">
                        @if ($edition->hero_image_path)
                            <img src="{{ asset('storage/'.$edition->hero_image_path) }}" alt="{{ $edition->name }}"
                                class="clip-corner mb-4 aspect-video w-full object-cover" />
                        @endif
                        <div class="flex items-center justify-between">
                            <span class="inline-block h-4 w-4 clip-corner"
                                style="background: {{ $edition->primary_color }}"></span>
                            @if ($edition->is_active)
                                <x-forge.badge>Huidige editie</x-forge.badge>
                            @else
                                <x-forge.badge class="!text-forge-steel/50">Archief</x-forge.badge>
                            @endif
                        </div>
                        <h3 class="mt-4 font-display text-2xl font-bold uppercase tracking-wide text-white transition group-hover:text-primary-300">
                            {{ $edition->name }}
                        </h3>
                        <p class="mt-1 font-pixel text-[9px] uppercase tracking-[0.2em] text-forge-steel/50">{{ $edition->year }}</p>
                        @if ($edition->tagline)
                            <p class="mt-3 text-sm text-forge-steel/70">{{ $edition->tagline }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
