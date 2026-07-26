@props(['variant' => 'default'])

{{--
    Per-page pixel-farm scene. A dark fade is the base; over it a randomised
    scatter of curated farm sprites is placed in the side gutters (a grid of
    cells, shuffled and jittered so placement looks random but sprites never
    overlap and never cross the readable centre column). Each sprite gets a
    random size, and size drives depth: big sprites sit "near" and parallax
    fast, small ones sit "far" and barely move, for a pronounced effect. Layout
    re-randomises every request, so no two visits look the same. Shown from lg
    up (where the content leaves gutters); behind everything (-z-10),
    pointer-events-none, parallax disabled under prefers-reduced-motion.
--}}
@php
    // Curated pool — animals, crops, props, trees, farmers, plus barn + Arti.
    $pool = [
        'tile_0003', 'tile_0015', 'tile_0027', 'tile_0039', 'tile_0078', // trees + bushes
        'tile_0032', 'tile_0044', 'tile_0068', 'tile_0083', 'tile_0059', 'tile_0047', // crops
        'tile_0085', 'tile_0076', 'tile_0089', 'tile_0096', 'tile_0097', // props
        'tile_0108', 'tile_0109', // farmers
        'tile_0120', 'tile_0121', 'tile_0122', // sheep, cow, chicken
        'barn', 'arti',
    ];

    // Six sprites across three depth tiers for a nice parallax: big = near =
    // fast, small = far = slow. [width classes, opacity, parallax speed].
    $big   = ['w' => 'w-40 sm:w-52', 'op' => '0.70', 'spd' => '0.34'];
    $med   = ['w' => 'w-24 sm:w-28', 'op' => '0.54', 'spd' => '0.22'];
    $small = ['w' => 'w-12 sm:w-16', 'op' => '0.42', 'spd' => '0.10'];

    // Six anchors hugging the side gutters (top / middle / bottom, both sides),
    // spaced so sprites never overlap and never reach the centred content.
    $slots = [
        [mt_rand(0, 3),   mt_rand(9, 16)],   // top-left
        [mt_rand(90, 97), mt_rand(9, 16)],   // top-right
        [mt_rand(0, 3),   mt_rand(44, 52)],  // middle-left
        [mt_rand(90, 97), mt_rand(44, 52)],  // middle-right
        [mt_rand(0, 3),   mt_rand(76, 85)],  // bottom-left
        [mt_rand(88, 96), mt_rand(76, 85)],  // bottom-right
    ];

    // Pick six distinct sprites; two big, two medium, two small, shuffled onto
    // the slots so sizes land in random corners each request.
    $picks = $pool;
    shuffle($picks);
    $picks = array_slice($picks, 0, 6);
    $sizeBag = [$big, $big, $med, $med, $small, $small];
    shuffle($sizeBag);

    $placements = [];
    foreach ($slots as $i => [$lx, $ly]) {
        $placements[] = [
            'src'  => $picks[$i].'.png',
            'left' => $lx,
            'top'  => $ly,
            's'    => $sizeBag[$i],
        ];
    }
@endphp

<div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
    {{-- base wash: dark forge gradient keeps the centre readable --}}
    <div class="absolute inset-0 bg-gradient-to-b from-forge-forest/50 via-forge-black to-forge-black"></div>
    <div class="absolute inset-0 bg-grid opacity-[0.05]"></div>
    <div class="absolute left-1/2 top-0 h-[45vmax] w-[45vmax] -translate-x-1/2 -translate-y-1/3 rounded-full bg-primary-500/12 blur-[130px]"></div>

    {{-- randomised, non-overlapping sprite scatter (lg+ where gutters exist) --}}
    @foreach ($placements as $p)
        <img data-parallax="{{ $p['s']['spd'] }}"
            src="{{ asset('images/farm/'.$p['src']) }}" alt=""
            class="pixel absolute {{ $p['s']['w'] }} hidden lg:block {{ (float) $p['s']['op'] > 0.5 ? 'drop-shadow-[0_0_16px_rgba(101,229,154,0.22)]' : '' }}"
            style="left: {{ $p['left'] }}%; top: {{ $p['top'] }}%; opacity: {{ $p['s']['op'] }}; will-change: transform;" />
    @endforeach
</div>

<script>
    (function () {
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        var els = document.querySelectorAll('[data-parallax]');
        if (!els.length) return;
        var ticking = false;
        function update() {
            var y = window.pageYOffset || document.documentElement.scrollTop || 0;
            for (var i = 0; i < els.length; i++) {
                var s = parseFloat(els[i].getAttribute('data-parallax')) || 0;
                els[i].style.transform = 'translate3d(0,' + (-y * s).toFixed(1) + 'px,0)';
            }
            ticking = false;
        }
        window.addEventListener('scroll', function () {
            if (!ticking) { window.requestAnimationFrame(update); ticking = true; }
        }, { passive: true });
        update();
    })();
</script>
