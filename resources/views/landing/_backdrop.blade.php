@php
    // The landing is MBLAN's permanent "Arti in Space" showpiece, so it always
    // wears the space scene regardless of the active edition's page-scenery
    // set. Every colour in here reads from the edition palette (--c-primary-*)
    // on top of a fixed deep-navy base, so the scene re-tints per edition.
    // Sprites come from the built-in space set.
    $sp = fn (string $name) => asset('images/scenery/space/'.$name.'.png');
@endphp

<div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
    {{-- deep-space base: navy black, the edition colour glowing in from the top --}}
    <div class="showcase-base absolute inset-0"></div>

    {{-- canvas starfield: twinkle, slow drift, shooting stars (resources/js/space-stars.js) --}}
    <div class="parallax-layer parallax-layer-bleed showcase-enter-fade" data-parallax="0.35">
        <canvas data-space-stars class="block h-full w-full"></canvas>
    </div>

    {{-- nebula clouds: two layers drifting against each other for depth --}}
    <div class="parallax-layer parallax-layer-bleed showcase-enter-fade" data-parallax="0.18">
        <div class="showcase-nebula absolute inset-0"></div>
    </div>
    <div class="parallax-layer parallax-layer-bleed showcase-enter-fade" data-parallax="0.10">
        <div class="showcase-nebula-2 absolute inset-0"></div>
    </div>

    {{-- big soft galaxy glow, centre, slowly breathing --}}
    <div class="parallax-layer" data-parallax="0.06">
        <div class="showcase-galaxy absolute left-1/2 top-1/2"></div>
    </div>

    {{-- distant star cluster, slowly rotating --}}
    <div class="parallax-layer" data-parallax="0.28">
        <img src="{{ $sp('star_cluster') }}" alt="" class="pixel showcase-cluster absolute"
             style="top: 12%; right: 8%; width: clamp(40px, 8vw, 96px); opacity: 0.8;" />
    </div>

    {{-- dominant planet: breathing glow + planet + orbiting moon + a small swirl planet --}}
    <div class="parallax-layer" data-parallax="0.5">
        <div class="showcase-planet-wrap absolute left-1/2 -translate-x-1/2"
             style="top: -6vh; width: clamp(190px, 42vw, 360px); aspect-ratio: 1;">
            <div class="showcase-glow absolute left-1/2 top-1/2" style="width: 160%; height: 160%;"></div>
            <img src="{{ $sp('planet_ring_blue') }}" alt="" class="pixel showcase-planet absolute inset-0 h-full w-full object-contain" />
            <div class="showcase-orbit absolute inset-[-16%]">
                <img src="{{ $sp('moon') }}" alt="" class="pixel anim-wobble absolute left-1/2 top-0 -translate-x-1/2"
                     style="width: clamp(26px, 6vw, 54px);" />
            </div>
            <img src="{{ $sp('planet_swirl') }}" alt="" class="pixel anim-pulse absolute"
                 style="right: -22%; bottom: 6%; width: clamp(40px, 9vw, 96px); opacity: 0.9;" />
        </div>
    </div>

    {{-- scripted vignettes: something is always happening. Outer wrappers travel,
         inner elements arc/bob/tumble, so every path is a curve, not a line. --}}
    <div class="parallax-layer" data-parallax="0.4">
        <div class="vignette-rocket absolute" style="top: 0; left: 0;">
            <img src="{{ $sp('rocket') }}" alt="" class="vignette-rocket-body pixel block"
                 style="width: clamp(34px, 7vw, 74px); filter: drop-shadow(0 0 8px rgba(255,180,80,0.55));" />
        </div>

        <div class="vignette-ufo absolute" style="top: 22%; left: 0;">
            <div class="vignette-ufo-body relative">
                <div class="ufo-beam"></div>
                <img src="{{ $sp('ufo') }}" alt="" class="pixel relative block" style="width: clamp(40px, 8vw, 84px);" />
            </div>
        </div>

        <div class="vignette-comet absolute" style="top: 0; left: 0;">
            <img src="{{ $sp('comet') }}" alt="" class="vignette-comet-body pixel block"
                 style="width: clamp(30px, 6vw, 62px); filter: drop-shadow(0 0 12px rgb(var(--c-primary-300) / 0.85));" />
        </div>

        <div class="vignette-astronaut absolute" style="top: 0; left: 0;">
            <img src="{{ $sp('astronaut') }}" alt="" class="pixel anim-bob block" style="width: clamp(30px, 6vw, 60px); opacity: 0.92;" />
        </div>
    </div>

    {{-- stationary drifters with their own little animations --}}
    <div class="parallax-layer" data-parallax="0.3">
        <div class="showcase-sprite absolute" style="top: 58%; right: 10%; animation-duration: 18s;">
            <img src="{{ $sp('satellite') }}" alt="" class="pixel anim-spin block" style="width: clamp(30px, 5vw, 58px); opacity: 0.85;" />
        </div>
        <div class="showcase-sprite absolute" style="top: 44%; left: 8%; animation-duration: 15s; animation-delay: -4s;">
            <img src="{{ $sp('alien') }}" alt="" class="pixel anim-wobble block" style="width: clamp(28px, 5vw, 52px); opacity: 0.9;" />
        </div>
        <div class="showcase-sprite absolute" style="top: 68%; left: 26%; animation-duration: 21s; animation-delay: -7s;">
            <img src="{{ $sp('moon') }}" alt="" class="pixel anim-pulse block" style="width: clamp(22px, 4vw, 40px); opacity: 0.7;" />
        </div>
    </div>

    {{-- floating dust motes in the edition colour --}}
    <span class="dust absolute rounded-full" style="top: 40%; left: 30%; width: 3px; height: 3px; animation-delay: -2s;"></span>
    <span class="dust absolute rounded-full" style="top: 60%; left: 70%; width: 2px; height: 2px; animation-delay: -6s;"></span>
    <span class="dust absolute rounded-full" style="top: 52%; left: 50%; width: 3px; height: 3px; animation-delay: -9s;"></span>
    <span class="dust absolute rounded-full" style="top: 30%; left: 82%; width: 2px; height: 2px; animation-delay: -11s;"></span>

    {{-- edge darkening + bottom scrim so the content panel stays legible --}}
    <div class="showcase-vignette absolute inset-0"></div>
    <div class="showcase-scrim absolute inset-x-0 bottom-0 h-2/3"></div>
</div>
