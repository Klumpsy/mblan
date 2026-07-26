@props(['variant' => 'default'])

{{--
    Per-page pixel-farm backdrop. Uses the game's tile sprite library, scattered
    large and faded in the corners so each page feels distinct while staying
    clean and readable. Fixed, behind everything, and pointer-events-none.
    Mobile keeps smaller sprites; the gradient + silhouette carry the base look.
--}}
@php
    // [tile number, top%, left%] — placed toward the edges, away from centred text.
    $scenes = [
        'timeline' => [['0003', '14%', '5%'], ['0015', '80%', '6%'], ['0040', '20%', '93%'], ['0052', '52%', '95%'], ['0039', '86%', '90%']],
        'tournaments' => [['0009', '15%', '6%'], ['0120', '82%', '7%'], ['0042', '20%', '92%'], ['0072', '84%', '91%'], ['0027', '48%', '95%']],
        'news' => [['0088', '16%', '6%'], ['0027', '82%', '6%'], ['0075', '80%', '92%'], ['0056', '20%', '93%'], ['0040', '50%', '95%']],
        'profile' => [['0015', '16%', '6%'], ['0053', '82%', '8%'], ['0085', '22%', '93%'], ['0123', '84%', '90%'], ['0052', '50%', '95%']],
        'default' => [['0003', '15%', '5%'], ['0027', '82%', '7%'], ['0078', '22%', '92%'], ['0064', '84%', '90%'], ['0039', '50%', '95%']],
    ];
    $sprites = $scenes[$variant] ?? $scenes['default'];
@endphp

<div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-gradient-to-b from-forge-forest/60 via-forge-black to-forge-black"></div>
    <div class="absolute inset-0 bg-primary-900/15"></div>
    <div class="absolute inset-0 bg-grid opacity-[0.08]"></div>
    <div class="absolute left-1/2 top-0 h-[45vmax] w-[45vmax] -translate-x-1/2 -translate-y-1/3 rounded-full bg-primary-500/12 blur-[130px]"></div>

    @foreach ($sprites as [$tile, $top, $left])
        <img src="{{ asset('images/farm/tile_'.$tile.'.png') }}" alt=""
            class="pixel absolute w-14 opacity-[0.09] sm:w-24"
            style="top: {{ $top }}; left: {{ $left }}; transform: translate(-50%, -50%);" />
    @endforeach

    <img src="{{ asset('images/farm/backdrop.png') }}" alt=""
        class="pixel absolute inset-x-0 bottom-0 h-40 w-full object-cover opacity-[0.20] sm:h-56"
        style="-webkit-mask-image: linear-gradient(to top, #000, transparent); mask-image: linear-gradient(to top, #000, transparent);" />
</div>
