<x-app-layout>
    <x-slot name="header">
        <x-forge.heading eyebrow="Forged Through Play" class="!mb-0">Achievements</x-forge.heading>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">

            @if ($achievements->isEmpty())
                <div class="clip-corner metal-edge p-10 text-center">
                    <p class="font-display uppercase tracking-widest text-forge-steel/70">
                        You haven’t earned any achievements yet - keep playing!
                    </p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                    @foreach ($achievements as $i => $achievement)
                        @php
                            $pivot = $achievement->users->first()?->pivot;
                            $unlocked = (bool) $pivot?->achieved_at;
                            $progress = $pivot?->progress ?? 0;
                            $threshold = $achievement->threshold;
                            $pct = $threshold ? min(100, round((($unlocked ? $threshold : $progress) / $threshold) * 100)) : 0;
                        @endphp

                        <div x-data x-reveal.{{ ($i % 4) * 100 }}>
                            <div class="group relative h-full clip-corner metal-edge p-5 flex flex-col items-center text-center transition-all duration-300 hover:-translate-y-0.5 {{ $unlocked ? 'hover:shadow-glow-sm' : '' }}">
                                <span class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-primary-400/80 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></span>

                                <div class="relative mb-3">
                                    <img src="{{ Storage::url($achievement->icon_path) }}" alt="{{ $achievement->name }}"
                                        class="w-16 h-16 rounded-md {{ $unlocked ? '' : 'grayscale opacity-40' }}"
                                        style="{{ $unlocked ? 'filter: drop-shadow(0 0 8px rgb(var(--c-primary-500) / 0.6));' : '' }}">
                                </div>

                                <h4 class="font-display text-sm font-semibold uppercase tracking-wide text-white">
                                    {{ $achievement->name }}
                                </h4>

                                <p class="mt-1 text-xs text-forge-steel/70">
                                    {{ $achievement->description }}
                                </p>

                                @if ($threshold)
                                    <div class="mt-3 w-full">
                                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-forge-graphite">
                                            <div class="h-full rounded-full bg-primary-500 transition-all duration-500"
                                                style="width: {{ $pct }}%;"></div>
                                        </div>
                                        <div class="mt-1.5 text-[10px] uppercase tracking-widest text-forge-steel/60">
                                            {{ $unlocked ? $threshold : $progress }} / {{ $threshold }}
                                        </div>
                                    </div>
                                @endif

                                <div class="mt-3">
                                    @if ($unlocked)
                                        <span class="inline-flex items-center border border-primary-500/30 bg-primary-500/15 px-2.5 py-0.5 font-display text-[10px] uppercase tracking-wider text-primary-300 clip-corner">
                                            Unlocked
                                        </span>
                                    @else
                                        <span class="inline-flex items-center border border-primary-500/10 bg-forge-graphite px-2.5 py-0.5 font-display text-[10px] uppercase tracking-wider text-forge-steel/50 clip-corner">
                                            Locked
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
