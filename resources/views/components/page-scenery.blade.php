@props(['variant' => 'default'])

{{--
    Per-page pixel-farm scene with depth. A plain dark fade is the base; over it
    sit curated farm sprites arranged in three parallax layers:
      back  — distant trees/bushes, small + faint, drift slowly on scroll
      mid   — props & crops, medium
      front — animals + the barn + Arti, big + bright, drift most
    Fixed slot positions in the side gutters keep sprites from overlapping each
    other, and each section fills them with its own mix so pages stay distinct.
    Side sprites show from lg up (where the centred content leaves gutters); the
    barn + Arti anchor the bottom corners from sm up. Behind everything (-z-10),
    pointer-events-none, and parallax is disabled under prefers-reduced-motion.
--}}
@php
    // Slots: [x-position class, top%, layer]. Tuned so sprites never overlap.
    $slots = [
        ['left-[1%]',  '9%',  'back'],  ['right-[1%]', '15%', 'back'],
        ['left-[5%]',  '35%', 'mid'],   ['right-[4%]', '29%', 'mid'],
        ['left-[2%]',  '57%', 'front'], ['right-[2%]', '51%', 'front'],
        ['left-[6%]',  '77%', 'mid'],   ['right-[6%]', '73%', 'mid'],
    ];

    // Per-layer look + parallax speed (fraction of scroll it drifts).
    $layer = [
        'back'  => ['size' => 'w-14 xl:w-16', 'op' => '0.35', 'spd' => '0.05'],
        'mid'   => ['size' => 'w-20 xl:w-28', 'op' => '0.50', 'spd' => '0.11'],
        'front' => ['size' => 'w-28 xl:w-36', 'op' => '0.70', 'spd' => '0.18'],
    ];

    // Eight sprites per section, one per slot — a varied, sensible farm mix.
    // tiles: 0003/0015/0027 trees · 0039/0078 bushes · 0032 corn · 0044 tomato
    // 0068 carrot · 0083 sunflower · 0059 lettuce crate · 0085 barrel · 0076 crate
    // 0089 rock · 0096 hay bale · 0097 haystack · 0108/0109 farmers · 0120 sheep
    // 0121 cow · 0122 chicken
    $scenes = [
        'timeline'    => ['0027', '0083', '0121', '0044', '0122', '0096', '0039', '0068'],
        'tournaments' => ['0015', '0078', '0120', '0085', '0109', '0076', '0003', '0032'],
        'news'        => ['0003', '0083', '0122', '0089', '0108', '0097', '0039', '0044'],
        'profile'     => ['0027', '0068', '0121', '0059', '0120', '0085', '0015', '0083'],
        'default'     => ['0003', '0027', '0120', '0096', '0122', '0076', '0039', '0032'],
    ];
    $tiles = $scenes[$variant] ?? $scenes['default'];
@endphp

<div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
    {{-- base wash: dark forge gradient keeps the centre readable --}}
    <div class="absolute inset-0 bg-gradient-to-b from-forge-forest/50 via-forge-black to-forge-black"></div>
    <div class="absolute inset-0 bg-grid opacity-[0.05]"></div>
    <div class="absolute left-1/2 top-0 h-[45vmax] w-[45vmax] -translate-x-1/2 -translate-y-1/3 rounded-full bg-primary-500/12 blur-[130px]"></div>

    {{-- gutter sprites, layered for parallax depth (lg+ where gutters exist) --}}
    @foreach ($slots as $i => [$x, $top, $lyr])
        @php $L = $layer[$lyr]; @endphp
        <img data-parallax="{{ $L['spd'] }}"
            src="{{ asset('images/farm/tile_'.$tiles[$i].'.png') }}" alt=""
            class="pixel absolute {{ $x }} {{ $L['size'] }} hidden lg:block {{ $lyr === 'front' ? 'drop-shadow-[0_0_16px_rgba(101,229,154,0.25)]' : '' }}"
            style="top: {{ $top }}; opacity: {{ $L['op'] }}; will-change: transform;" />
    @endforeach

    {{-- bottom-corner anchors: the barn + Arti the dog (front layer) --}}
    <img data-parallax="0.18" src="{{ asset('images/farm/barn.png') }}" alt=""
        class="pixel absolute bottom-[6%] right-[2%] w-28 opacity-[0.60] drop-shadow-[0_0_26px_rgba(101,229,154,0.30)] sm:w-40 lg:w-52"
        style="will-change: transform;" />
    <img data-parallax="0.18" src="{{ asset('images/farm/arti.png') }}" alt=""
        class="pixel absolute bottom-[8%] left-[3%] w-20 opacity-[0.65] sm:w-28 lg:w-36"
        style="will-change: transform;" />
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
