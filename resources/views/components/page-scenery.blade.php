@props(['variant' => 'default'])

{{--
    Per-page pixel-farm accents. A plain dark fade carries the base look; big,
    clearly visible sprites from the game's sprite library float over it — a row
    of tiles down each side gutter (shown only from lg up, where the centred
    content leaves room), plus the barn and Arti the dog in the bottom corners.
    No tiled farm field, so it reads as decorative sprites on a clean gradient
    rather than a game map. Each section gets its own tiles so pages stay
    distinct. Fixed, behind everything (-z-10), pointer-events-none, and kept
    clear of the readable content column.
--}}
@php
    // Six gutter tiles per variant: [left-top, left-mid, left-bottom, right-top, right-mid, right-bottom].
    $scenes = [
        'timeline'    => ['0003', '0015', '0040', '0052', '0039', '0027'],
        'tournaments' => ['0009', '0120', '0042', '0072', '0027', '0064'],
        'news'        => ['0088', '0056', '0075', '0027', '0040', '0123'],
        'profile'     => ['0015', '0053', '0085', '0123', '0052', '0078'],
        'default'     => ['0003', '0027', '0078', '0064', '0039', '0042'],
    ];
    $tiles = $scenes[$variant] ?? $scenes['default'];

    // Gutter slots — hug the far edges so nothing crosses the content column.
    $slots = [
        'left-[1%] top-[13%]',   'left-[3%] top-[43%]',   'left-[0%] top-[72%]',
        'right-[1%] top-[15%]',  'right-[3%] top-[45%]',  'right-[0%] top-[70%]',
    ];
@endphp

<div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
    {{-- base wash: dark forge gradient keeps the centre readable --}}
    <div class="absolute inset-0 bg-gradient-to-b from-forge-forest/50 via-forge-black to-forge-black"></div>
    <div class="absolute inset-0 bg-grid opacity-[0.05]"></div>
    <div class="absolute left-1/2 top-0 h-[45vmax] w-[45vmax] -translate-x-1/2 -translate-y-1/3 rounded-full bg-primary-500/12 blur-[130px]"></div>

    {{-- big side-gutter tiles (lg+ only, where the content column leaves room) --}}
    @foreach ($tiles as $i => $tile)
        <img src="{{ asset('images/farm/tile_'.$tile.'.png') }}" alt=""
            class="pixel absolute {{ $slots[$i] }} hidden w-44 opacity-[0.45] drop-shadow-[0_0_18px_rgba(101,229,154,0.25)] lg:block xl:w-52 2xl:w-60"
            style="transform: translateX(-50%);" />
    @endforeach

    {{-- bottom-corner anchors: barn + Arti floating on the fade (no tiled field) --}}
    <img src="{{ asset('images/farm/barn.png') }}" alt=""
        class="pixel absolute bottom-[6%] right-[3%] w-28 opacity-[0.40] drop-shadow-[0_0_26px_rgba(101,229,154,0.30)] sm:w-40 lg:w-52" />

    <img src="{{ asset('images/farm/arti.png') }}" alt=""
        class="pixel absolute bottom-[8%] left-[4%] w-20 opacity-[0.50] sm:w-28 lg:w-36" />
</div>
