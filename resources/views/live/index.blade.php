<x-app-layout>
    @php
        // Twitch requires the embedding domain(s) as "parent" params.
        $channel = 'mblan26';
        $parents = array_values(array_unique(array_filter([
            request()->getHost(),
            'mblan.nl',
            'www.mblan.nl',
        ])));
        $parentQuery = collect($parents)->map(fn ($p) => 'parent=' . $p)->implode('&');
    @endphp

    <div class="relative">
        <div class="pointer-events-none absolute inset-0 bg-grid opacity-30"></div>
        <div class="relative mx-auto max-w-6xl px-6 py-12">
            <x-forge.heading eyebrow="MBLAN26">Live</x-forge.heading>

            <p class="mb-8 max-w-2xl font-pixel text-[9px] uppercase leading-relaxed tracking-[0.15em] text-forge-steel/60">
                De stream van de LAN party. Draait er niks? Dan zijn we nog niet live, of buiten de speeltijden.
            </p>

            <div class="grid gap-4 lg:grid-cols-[1fr_340px]">
                {{-- Player --}}
                <div class="frame-wood overflow-hidden">
                    <div class="relative w-full" style="aspect-ratio: 16 / 9;">
                        <iframe
                            src="https://player.twitch.tv/?channel={{ $channel }}&{{ $parentQuery }}&autoplay=false"
                            class="absolute inset-0 h-full w-full"
                            allowfullscreen
                            title="MBLAN26 Twitch stream"></iframe>
                    </div>
                </div>

                {{-- Chat --}}
                <div class="frame-wood overflow-hidden">
                    <iframe
                        src="https://www.twitch.tv/embed/{{ $channel }}/chat?darkpopout&{{ $parentQuery }}"
                        class="h-[420px] w-full lg:h-full"
                        title="MBLAN26 Twitch chat"></iframe>
                </div>
            </div>

            <div class="mt-6">
                <a href="https://www.twitch.tv/{{ $channel }}" target="_blank" rel="noopener"
                    class="btn-wood clip-corner inline-block text-xs">Open op Twitch</a>
            </div>
        </div>
    </div>
</x-app-layout>
