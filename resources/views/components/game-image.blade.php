@props(['game'])

<div
    class="aspect-video w-full md:aspect-[16/9] sm:w-[180px] overflow-hidden rounded-lg bg-gray-200 dark:bg-gray-700 flex-shrink-0">
    @if ($game->image)
        <img src="{{ asset('storage/' . $game->image) }}" alt="{{ $game->name }}" class="w-full h-full object-cover"
            loading="lazy">
    @else
        <div class="w-full h-full flex items-center justify-center bg-gray-300 dark:bg-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400 dark:text-gray-500" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </div>
    @endif
</div>
