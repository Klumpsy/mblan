@php
    $current = app(\App\Support\CurrentEdition::class)->get();
    $editions = \App\Models\Edition::orderByDesc('year')->get()
        ->filter(fn ($e) => $e->hasExclusiveAccess(auth()->user()));
@endphp

@if ($editions->count() > 1 && $current)
    <x-dropdown align="right" width="48" {{ $attributes }}>
        <x-slot name="trigger">
            <button type="button"
                class="inline-flex items-center gap-2 border border-primary-500/20 clip-corner px-3 py-2 font-display text-xs uppercase tracking-wider text-forge-steel bg-forge-graphite transition hover:text-primary-300 hover:border-primary-500/40 focus:outline-none">
                <span class="h-2.5 w-2.5 rounded-full"
                    style="background: {{ $current->color }}; box-shadow: 0 0 10px {{ $current->color }};"></span>
                <span class="max-w-[140px] truncate">{{ $current->name }}</span>
            </button>
        </x-slot>

        <x-slot name="content">
            <div class="px-4 py-2 font-display text-xs uppercase tracking-widest text-primary-400/70">
                Switch Edition
            </div>
            @foreach ($editions as $edition)
                <a href="{{ route('editions.switch', $edition->slug) }}"
                    class="flex items-center gap-2 px-4 py-2 text-sm transition {{ $current->id === $edition->id ? 'text-primary-300 bg-primary-500/10' : 'text-forge-steel hover:text-primary-200 hover:bg-primary-500/10' }}">
                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $edition->color }};"></span>
                    <span class="truncate">{{ $edition->name }}</span>
                    <span class="ml-auto text-xs text-forge-steel/50">{{ $edition->year }}</span>
                </a>
            @endforeach
        </x-slot>
    </x-dropdown>
@endif
