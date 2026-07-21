<a href="{{ route('games.show', $game->id) }}" class="group relative mb-4 block clip-corner metal-edge overflow-hidden transition-shadow duration-300 hover:shadow-glow-sm">
    <span class="pointer-events-none absolute inset-x-0 top-0 z-10 h-px bg-gradient-to-r from-transparent via-primary-400/80 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></span>
    <div class="flex flex-col lg:flex-row">
        <div class="lg:w-1/2 xl:w-2/5 flex-shrink-0">
            <div class="w-full aspect-video overflow-hidden">
                @if ($game->image)
                    <img src="{{ asset('storage/' . $game->image) }}" alt="{{ $game->name }}"
                        class="w-full h-full object-cover transition duration-500 group-hover:scale-105" />
                @else
                    <div class="flex items-center justify-center w-full h-full bg-forge-graphite text-forge-steel/40">
                        <span class="text-xs uppercase tracking-widest">No image available</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="lg:w-1/2 xl:w-3/5 p-4 lg:p-6 flex flex-col justify-between min-h-0">
            <div>
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-3 gap-3">
                    <h5
                        class="font-display text-xl lg:text-2xl font-bold uppercase tracking-wide text-white flex-shrink-0 transition-colors duration-300 group-hover:text-primary-300">
                        {{ $game->name }}
                    </h5>
                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                        @each('components.tag', $game->tags, 'tag')
                    </div>
                </div>

                <div class="text-forge-steel/80 text-sm lg:text-base leading-relaxed mb-4">
                    {!! $game->short_description !!}
                </div>
            </div>
            <div class="flex justify-between items-center pt-3 border-t border-primary-500/15">
                <span class="font-display text-xs uppercase tracking-widest text-primary-400/80">
                    Released: {{ $game->year_of_release ?? 'Unknown' }}
                </span>
                <div onclick="event.preventDefault(); event.stopPropagation();">
                    <livewire:game.like :game="$game" />
                </div>
            </div>
        </div>
    </div>
</a>
