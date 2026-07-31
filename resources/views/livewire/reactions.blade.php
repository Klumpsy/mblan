<div class="flex flex-wrap items-center gap-2">
    @foreach ($emojis as $slug => $emoji)
        @php($active = in_array($slug, $mine, true))
        @php($who = $reactors[$slug] ?? null)
        <div class="relative"
            x-data="{
                open: false,
                hoverTimer: null,
                pressTimer: null,
                suppressClick: false,
                show() { this.open = true },
                hide() { clearTimeout(this.hoverTimer); this.open = false },
            }"
            @click.outside="hide()">
            <button type="button" wire:loading.attr="disabled"
                x-on:click="if (suppressClick) { suppressClick = false } else { $wire.toggle('{{ $slug }}') }"
                x-on:mouseenter="hoverTimer = setTimeout(() => show(), 150)"
                x-on:mouseleave="hide()"
                x-on:focus="show()"
                x-on:blur="hide()"
                x-on:touchstart="pressTimer = setTimeout(() => { show(); suppressClick = true }, 500)"
                x-on:touchend="clearTimeout(pressTimer); if (suppressClick) setTimeout(() => suppressClick = false, 400)"
                x-on:touchmove="clearTimeout(pressTimer)"
                x-on:contextmenu="if (open) $event.preventDefault()"
                style="-webkit-touch-callout: none"
                class="clip-corner flex select-none items-center gap-1.5 border px-2.5 py-1 text-sm transition
                    {{ $active
                        ? 'border-primary-500/60 bg-primary-500/15 text-white'
                        : 'border-primary-500/15 bg-forge-graphite/40 text-forge-steel/80 hover:border-primary-500/40 hover:text-white' }}">
                <span class="leading-none">{{ $emoji }}</span>
                <span class="font-pixel text-[9px] tabular-nums tracking-widest">{{ $counts[$slug] ?? 0 }}</span>
            </button>

            @if ($who && $who['names'] !== [])
                <div x-cloak x-show="open" x-transition.opacity.duration.100ms
                    class="clip-corner absolute bottom-full left-0 z-20 mb-2 w-max max-w-[14rem] border border-primary-500/30 bg-forge-graphite px-3 py-2 text-xs text-forge-steel/90 shadow-lg">
                    @foreach ($who['names'] as $name)
                        <div class="truncate leading-5">{{ $name }}</div>
                    @endforeach
                    @if ($who['more'] > 0)
                        <div class="leading-5 text-forge-steel/60">+{{ $who['more'] }} anderen</div>
                    @endif
                </div>
            @endif
        </div>
    @endforeach
</div>
