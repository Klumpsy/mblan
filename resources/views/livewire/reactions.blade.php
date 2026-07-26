<div class="flex flex-wrap items-center gap-2">
    @foreach ($emojis as $slug => $emoji)
        @php($active = in_array($slug, $mine, true))
        <button type="button" wire:click="toggle('{{ $slug }}')" wire:loading.attr="disabled"
            class="clip-corner flex items-center gap-1.5 border px-2.5 py-1 text-sm transition
                {{ $active
                    ? 'border-primary-500/60 bg-primary-500/15 text-white'
                    : 'border-primary-500/15 bg-forge-graphite/40 text-forge-steel/80 hover:border-primary-500/40 hover:text-white' }}">
            <span class="leading-none">{{ $emoji }}</span>
            <span class="font-pixel text-[9px] tabular-nums tracking-widest">{{ $counts[$slug] ?? 0 }}</span>
        </button>
    @endforeach
</div>
