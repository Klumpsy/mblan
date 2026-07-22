<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-xl font-bold uppercase tracking-wide text-white leading-tight">
            {{ __('Toernooien') }}
        </h2>
    </x-slot>

    <div class="relative">
        <div class="pointer-events-none absolute inset-0 bg-grid opacity-30"></div>
        <div class="relative mx-auto max-w-6xl px-6 py-12">
            <x-forge.heading eyebrow="{{ $edition?->name ?? 'MBLAN' }}">Toernooien &amp; Ladders</x-forge.heading>

            @php
                $live = $tournaments->filter(fn ($t) => $t->is_active);
                $upcoming = $tournaments->filter(fn ($t) => !$t->is_active && $t->hasYetToStart());
                $past = $tournaments->filter(fn ($t) => !$t->is_active && !$t->hasYetToStart());
            @endphp

            @if ($tournaments->isEmpty())
                <x-forge.card>
                    <p class="text-forge-steel/60">Er zijn nog geen toernooien voor deze editie.</p>
                </x-forge.card>
            @else
                @if ($live->isNotEmpty())
                    <div class="mb-6 flex items-center gap-3">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary-400 opacity-75"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-primary-400"></span>
                        </span>
                        <span class="font-display text-sm uppercase tracking-widest text-primary-300">Nu Live</span>
                    </div>
                    <div class="mb-14 grid gap-6 lg:grid-cols-2">
                        @foreach ($live as $tournament)
                            <div x-data x-reveal>
                                <livewire:tournament.ladder :tournament="$tournament" :key="'live-' . $tournament->id" />
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($upcoming->isNotEmpty())
                    <h3 class="mb-6 font-display text-lg uppercase tracking-widest text-forge-steel/70">Aankomende toernooien</h3>
                    <div class="mb-14 grid gap-6 lg:grid-cols-2">
                        @foreach ($upcoming as $tournament)
                            <div x-data x-reveal>
                                <livewire:tournament.ladder :tournament="$tournament" :key="'up-' . $tournament->id" />
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($past->isNotEmpty())
                    <h3 class="mb-6 font-display text-lg uppercase tracking-widest text-forge-steel/70">Afgelopen toernooien</h3>
                    <div class="grid gap-6 lg:grid-cols-2">
                        @foreach ($past as $tournament)
                            <div x-data x-reveal>
                                <livewire:tournament.ladder :tournament="$tournament" :key="'past-' . $tournament->id" />
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
