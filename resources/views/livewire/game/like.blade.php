<span class="inline-flex items-center gap-1.5 clip-corner metal-edge px-3 py-1.5 font-display text-sm uppercase tracking-wider text-forge-steel cursor-pointer group transition-all duration-200 hover:text-white hover:shadow-glow-sm"
    wire:click="toggleLike"
    wire:loading.class="opacity-50">
    <span class="{{ $isLiked ? 'text-primary-400 text-glow' : '' }}">
        {{ $isLiked ? 'Liked' : 'Like' }}
    </span>
    <span class="text-primary-300" wire:loading.delay.class="opacity-50">{{ $likesCount }}</span>
</span>
