<a href="{{ route('games.show', $game->id) }}"
    class="mb-4 block w-full bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
    <div class="flex flex-col lg:flex-row">
        <div class="lg:w-1/2 xl:w-2/5 flex-shrink-0">
            <div class="w-full aspect-video overflow-hidden">
                @if ($game->image)
                    <img src="{{ asset('storage/' . $game->image) }}" alt="{{ $game->name }}"
                        class="w-full h-full object-cover" />
                @else
                    <div class="flex items-center justify-center w-full h-full bg-gray-200 text-gray-500">
                        <span>No image available</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="lg:w-1/2 xl:w-3/5 p-4 lg:p-6 flex flex-col justify-between min-h-0">
            <div>
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-3 gap-3">
                    <h5
                        class="text-xl lg:text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex-shrink-0">
                        {{ $game->name }}
                    </h5>
                    <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                        @each('components.tag', $game->tags, 'tag')
                    </div>
                </div>

                <div class="text-gray-700 dark:text-gray-300 text-sm lg:text-base leading-relaxed mb-4">
                    {!! $game->short_description !!}
                </div>
            </div>
            <div class="flex justify-between items-center pt-2 border-t border-gray-100 dark:border-gray-700">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Released: {{ $game->year_of_release ?? 'Unknown' }}
                </span>
                <div onclick="event.preventDefault(); event.stopPropagation();">
                    <livewire:game.like :game="$game" />
                </div>
            </div>
        </div>
    </div>
</a>
