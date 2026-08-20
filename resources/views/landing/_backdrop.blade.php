@php
    // The landing is MBLAN's permanent "Arti in Space" showpiece, so it always
    // wears the rich space scene regardless of the active edition's page-scenery
    // set. The edition palette still tints the base glow + wordmark via
    // <x-edition-theme>. Sprites come from the built-in space set.
    $sp = fn (string $name) => asset('images/scenery/space/'.$name.'.png');
@endphp

<div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
    {{-- deep-space base gradient, top glow tinted by the edition palette --}}
    <div class="absolute inset-0"
         style="background:
            radial-gradient(130% 100% at 50% -10%, color-mix(in srgb, var(--c-primary-700, #14532d) 40%, #070312) 0%, #070312 46%, #04030a 100%);"></div>

    {{-- nebula layer 1 (bright, drifting) --}}
    <div class="showcase-nebula absolute inset-0"
         style="background-image:
            radial-gradient(ellipse 54% 46% at 20% 28%, rgba(167,139,250,0.80), rgba(167,139,250,0) 70%),
            radial-gradient(ellipse 50% 40% at 84% 20%, rgba(244,114,182,0.72), rgba(244,114,182,0) 70%),
            radial-gradient(ellipse 60% 48% at 74% 70%, rgba(34,211,238,0.58), rgba(34,211,238,0) 72%),
            radial-gradient(ellipse 54% 42% at 10% 78%, rgba(96,165,250,0.62), rgba(96,165,250,0) 72%);"></div>

    {{-- nebula layer 2 (deeper hues, parallax drift the other way) --}}
    <div class="showcase-nebula-2 absolute inset-0"
         style="background-image:
            radial-gradient(ellipse 46% 40% at 62% 40%, rgba(129,140,248,0.55), rgba(129,140,248,0) 72%),
            radial-gradient(ellipse 42% 36% at 34% 60%, rgba(217,70,239,0.45), rgba(217,70,239,0) 72%),
            radial-gradient(ellipse 50% 42% at 88% 62%, rgba(16,185,129,0.42), rgba(16,185,129,0) 74%);"></div>

    {{-- big soft galaxy glow, centre --}}
    <div class="showcase-galaxy absolute left-1/2 top-1/2"
         style="width: 90vw; height: 90vw; max-width: 1100px; max-height: 1100px;
                background: radial-gradient(circle, rgba(190,160,255,0.30) 0%, rgba(120,90,200,0.12) 35%, transparent 62%);"></div>

    {{-- far starfield (tiny, many) --}}
    <div class="star-far absolute inset-0"
         style="background-image:
            radial-gradient(1px 1px at 20% 30%, #cfefff 40%, transparent),
            radial-gradient(1px 1px at 70% 15%, #eafff2 40%, transparent),
            radial-gradient(1px 1px at 40% 70%, #b7ffd6 40%, transparent),
            radial-gradient(1px 1px at 85% 55%, #cfefff 40%, transparent),
            radial-gradient(1px 1px at 55% 45%, #9fe6bd 40%, transparent),
            radial-gradient(1px 1px at 12% 82%, #eafff2 40%, transparent),
            radial-gradient(1px 1px at 33% 22%, #cfefff 40%, transparent),
            radial-gradient(1px 1px at 90% 80%, #cfefff 40%, transparent),
            radial-gradient(1px 1px at 5% 50%, #eafff2 40%, transparent),
            radial-gradient(1px 1px at 48% 88%, #b7ffd6 40%, transparent);"></div>

    {{-- near starfield (bigger, coloured) --}}
    <div class="star-near absolute inset-0"
         style="background-image:
            radial-gradient(2px 2px at 16% 42%, #f0abfc 55%, transparent),
            radial-gradient(2px 2px at 62% 26%, #7dd3fc 55%, transparent),
            radial-gradient(2px 2px at 78% 62%, #fde68a 55%, transparent),
            radial-gradient(2px 2px at 44% 18%, #99f6e4 55%, transparent),
            radial-gradient(2px 2px at 30% 66%, #f9a8d4 55%, transparent),
            radial-gradient(2px 2px at 88% 34%, #c4b5fd 55%, transparent);"></div>

    {{-- distant star cluster, slowly rotating --}}
    <img src="{{ $sp('star_cluster') }}" alt="" class="pixel showcase-cluster absolute"
         style="top: 12%; right: 8%; width: clamp(40px, 8vw, 96px); opacity: 0.75;
                filter: drop-shadow(0 0 12px rgba(196,181,253,0.6));" />

    {{-- shooting stars: quick streaks, long idle gaps --}}
    <span class="shooting-star absolute" style="top: 14%; left: 78%; animation-delay: -1s;"></span>
    <span class="shooting-star absolute" style="top: 30%; left: 92%; animation-delay: -5s; animation-duration: 11s;"></span>
    <span class="shooting-star absolute" style="top: 8%;  left: 58%; animation-delay: -8s; animation-duration: 13s;"></span>
    <span class="shooting-star absolute" style="top: 22%; left: 40%; animation-delay: -3s; animation-duration: 10s;"></span>

    {{-- dominant planet: breathing glow + planet + orbiting moon --}}
    <div class="absolute left-1/2 -translate-x-1/2" style="top: -6vh; width: clamp(190px, 44vw, 380px); aspect-ratio: 1;">
        <div class="showcase-glow absolute left-1/2 top-1/2" style="width: 150%; height: 150%;
             background: radial-gradient(circle, rgba(101,229,154,0.40) 0%, rgba(101,229,154,0) 62%);"></div>
        <img src="{{ $sp('planet_ring') }}" alt="" class="pixel showcase-planet absolute inset-0 h-full w-full object-contain" />
        <div class="showcase-orbit absolute inset-[-16%]">
            <img src="{{ $sp('moon') }}" alt="" class="pixel anim-wobble absolute left-1/2 top-0 -translate-x-1/2"
                 style="width: clamp(26px, 6vw, 54px);" />
        </div>
        <img src="{{ $sp('planet_swirl') }}" alt="" class="pixel absolute anim-pulse"
             style="right: -22%; bottom: 6%; width: clamp(40px, 9vw, 96px); opacity: 0.85;" />
    </div>

    {{-- scripted vignettes: something is always happening --}}
    <img src="{{ $sp('rocket') }}" alt="" class="vignette-rocket pixel absolute"
         style="top: 0; left: 0; width: clamp(34px, 7vw, 74px); filter: drop-shadow(0 0 8px rgba(255,180,80,0.5));" />

    <div class="vignette-ufo absolute" style="top: 22%; left: 0;">
        <div class="relative">
            <div class="ufo-beam"></div>
            <img src="{{ $sp('ufo') }}" alt="" class="ufo-body pixel relative" style="width: clamp(40px, 8vw, 84px);" />
        </div>
    </div>

    <img src="{{ $sp('comet') }}" alt="" class="vignette-comet pixel absolute"
         style="top: 0; left: 0; width: clamp(30px, 6vw, 62px); filter: drop-shadow(0 0 12px rgba(125,211,252,0.8));" />

    <div class="vignette-astronaut absolute" style="top: 0; left: 0;">
        <img src="{{ $sp('astronaut') }}" alt="" class="pixel anim-bob" style="width: clamp(30px, 6vw, 60px); opacity: 0.92;" />
    </div>

    {{-- stationary drifters with their own little animations --}}
    <div class="showcase-sprite absolute" style="top: 58%; right: 10%; animation-duration: 16s;">
        <img src="{{ $sp('satellite') }}" alt="" class="pixel anim-spin" style="width: clamp(30px, 5vw, 58px); opacity: 0.85;" />
    </div>
    <div class="showcase-sprite absolute" style="top: 44%; left: 8%; animation-duration: 13s; animation-delay: -4s;">
        <img src="{{ $sp('alien') }}" alt="" class="pixel anim-wobble" style="width: clamp(28px, 5vw, 52px); opacity: 0.9;" />
    </div>
    <div class="showcase-sprite absolute" style="top: 68%; left: 26%; animation-duration: 18s; animation-delay: -7s;">
        <img src="{{ $sp('moon') }}" alt="" class="pixel anim-pulse" style="width: clamp(22px, 4vw, 40px); opacity: 0.7;" />
    </div>

    {{-- floating dust motes --}}
    <span class="dust absolute rounded-full" style="top: 40%; left: 30%; width: 3px; height: 3px; background: #e9d5ff; animation-delay: -2s;"></span>
    <span class="dust absolute rounded-full" style="top: 60%; left: 70%; width: 2px; height: 2px; background: #a5f3fc; animation-delay: -6s;"></span>
    <span class="dust absolute rounded-full" style="top: 52%; left: 50%; width: 3px; height: 3px; background: #fbcfe8; animation-delay: -9s;"></span>

    {{-- edge darkening + bottom scrim so the text panel stays legible --}}
    <div class="absolute inset-0" style="background: radial-gradient(120% 90% at 50% 40%, transparent 55%, rgba(4,3,10,0.6) 100%);"></div>
    <div class="absolute inset-x-0 bottom-0 h-2/3"
         style="background: linear-gradient(to top, rgba(4,3,10,0.95) 8%, rgba(4,3,10,0.55) 45%, transparent);"></div>
</div>
