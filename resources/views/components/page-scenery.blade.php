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

    // Just four sprites — two big (near, parallax fast) and two small (far,
    // parallax slow), one tucked in each corner so they stay well scattered
    // and never overlap. [width classes, opacity, parallax speed].
    $big   = ['w' => 'w-40 sm:w-52', 'op' => '0.70', 'spd' => '0.34'];
    $small = ['w' => 'w-12 sm:w-16', 'op' => '0.42', 'spd' => '0.10'];

    // Four corner anchors in the gutters, hugging the edges (jittered).
    $quads = [
        [mt_rand(0, 3),   mt_rand(8, 20)],   // top-left
        [mt_rand(90, 97), mt_rand(8, 20)],   // top-right
        [mt_rand(0, 3),   mt_rand(66, 82)],  // bottom-left
        [mt_rand(88, 96), mt_rand(66, 82)],  // bottom-right
    ];

    // Pick four distinct sprites and randomly make two of the corners big.
    $picks = $pool;
    shuffle($picks);
    $picks = array_slice($picks, 0, 4);
    $order = [0, 1, 2, 3];
    shuffle($order);
    $bigCorners = array_slice($order, 0, 2);

    $placements = [];
    foreach ($quads as $i => [$lx, $ly]) {
        $placements[] = [
            'src'  => $picks[$i].'.png',
            'left' => $lx,
            'top'  => $ly,
            's'    => in_array($i, $bigCorners, true) ? $big : $small,
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
