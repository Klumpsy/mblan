@props(['tag'])

<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 clip-corner border border-primary-500/30 bg-primary-500/15 font-display text-[0.7rem] font-medium uppercase tracking-wider text-primary-300">
    <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $tag->color ?: 'rgb(var(--c-primary-400))' }};"></span>
    {{ $tag->name }}
</span>
