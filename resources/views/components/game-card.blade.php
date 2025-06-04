<a href="{{ route('games.show', $game->id) }}"
    class="mb-4 block w-full bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
    <div class="flex flex-col md:flex-row">
        <div class="md:w-1/3 flex-shrink-0">
            <div class="w-full h-48 md:h-64 lg:h-72 overflow-hidden rounded-lg">
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


        <div class="md:w-2/3 p-4">
            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                {{ $game->name }}
            </h5>
            <span class="font-normal text-gray-700 dark:text-white">
                {!! $game->short_description !!}
            </span>
            <div class="mt-4 flex justify-between items-center">
                <span class="text-sm text-gray-500">
                    Released: {{ $game->year_of_release ?? 'Unknown' }}
                </span>
                <div onclick="event.preventDefault(); event.stopPropagation();">
                    <livewire:game.like :game="$game" :wire:key="'game-like-'.$game->id" />
                </div>
            </div>
        </div>
    </div>
</a>
