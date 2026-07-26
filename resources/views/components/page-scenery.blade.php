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

    // Size buckets: [width classes, opacity, parallax speed]. Big = near = fast.
    $sizes = [
        ['w' => 'w-10 sm:w-12', 'op' => '0.32', 'spd' => '0.07'], // small / far
        ['w' => 'w-16 sm:w-20', 'op' => '0.44', 'spd' => '0.15'],
        ['w' => 'w-24 sm:w-28', 'op' => '0.56', 'spd' => '0.26'],
        ['w' => 'w-32 sm:w-40', 'op' => '0.68', 'spd' => '0.38'], // big / near
    ];

    // Gutter grid — columns hug the screen edges so sprites never crowd the
    // left-aligned headings. Each column caps its max size: [x%, maxSizeIdx].
    // Inner columns stay small; only the outermost columns get the big sprites.
    $cols = [[1, 3], [10, 1], [90, 3], [99, 3]];
    $rows = [6, 22, 38, 54, 70, 86];
    $cells = [];
    foreach ($cols as [$cx, $maxIdx]) {
        foreach ($rows as $cy) {
            $cells[] = [$cx, $cy, $maxIdx];
        }
    }
    shuffle($cells);
    $cells = array_slice($cells, 0, mt_rand(18, 24));

    // Seed variant identity into the shuffle so sections still differ a little.
    $offset = ['timeline' => 0, 'tournaments' => 5, 'news' => 11, 'profile' => 17][$variant] ?? 0;

    $placements = [];
    foreach ($cells as $j => [$cx, $cy, $maxIdx]) {
        $sprite = $pool[($j * 7 + $offset + mt_rand(0, count($pool) - 1)) % count($pool)];
        $s = $sizes[mt_rand(0, $maxIdx)];
        $placements[] = [
            'src'  => $sprite.'.png',
            'left' => max(0, min(99, $cx + mt_rand(-2, 2))),
            'top'  => max(2, min(92, $cy + mt_rand(-4, 4))),
            's'    => $s,
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
