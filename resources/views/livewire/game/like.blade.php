<span class="inline-flex items-center text-sm text-gray-500 cursor-pointer group" wire:click="toggleLike"
    wire:loading.class="opacity-50">
    <span
        class="mr-1 transition-transform duration-200 ease-in-out {{ $isLiked ? 'text-red-500 scale-110' : 'group-hover:scale-110' }}">
        @if ($isLiked)
            ❤️
        @else
            🤍
        @endif
    </span>
    <span wire:loading.delay.class="opacity-50">{{ $likesCount }}</span>
</span>
