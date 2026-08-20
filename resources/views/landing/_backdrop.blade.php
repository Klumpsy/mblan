@php
    $edition = \App\Models\Edition::current();
    $planet = $edition?->sceneryLandmark();
    // Built-in space scene gets the scripted vignettes; other themes (or uploaded
    // sprite packages) keep the gentle drift so the page still degrades nicely.
    $isSpace = $edition && $edition->scenery_set === 'space' && empty($edition->scenery_sprites);
    $sp = fn (string $name) => asset('images/scenery/space/'.$name.'.png');
    // A handful of sprites to drift in the mid-layer; skip the planet itself.
    $drifters = collect($edition?->scenerySprites() ?? [])
        ->reject(fn ($url) => $planet && $url === $planet)
        ->take(6)
        ->values();
@endphp

<div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
    {{-- base gradient, tinted by the edition palette --}}
    <div class="absolute inset-0"
         style="background:
            radial-gradient(120% 90% at 50% 0%, color-mix(in srgb, var(--c-primary-700, #0f2417) 45%, #05080a) 0%, #05080a 60%),
            #05080a;"></div>

    {{-- colourful drifting nebula clouds --}}
    <div class="showcase-nebula absolute inset-0"
         style="background-image:
            radial-gradient(ellipse 52% 44% at 22% 30%, rgba(167,139,250,0.75), rgba(167,139,250,0) 70%),
            radial-gradient(ellipse 48% 38% at 82% 22%, rgba(244,114,182,0.68), rgba(244,114,182,0) 70%),
            radial-gradient(ellipse 58% 46% at 72% 68%, rgba(34,211,238,0.55), rgba(34,211,238,0) 72%),
            radial-gradient(ellipse 52% 40% at 12% 76%, rgba(96,165,250,0.60), rgba(96,165,250,0) 72%),
            radial-gradient(ellipse 42% 34% at 50% 46%, rgba(101,229,154,0.55), rgba(101,229,154,0) 74%);"></div>

    {{-- fine starfield --}}
    <div class="showcase-stars absolute inset-0"
         style="background-image:
            radial-gradient(1px 1px at 20% 30%, #cfefff 40%, transparent),
            radial-gradient(1px 1px at 70% 15%, #eafff2 40%, transparent),
            radial-gradient(1px 1px at 40% 70%, #b7ffd6 40%, transparent),
            radial-gradient(1px 1px at 85% 55%, #cfefff 40%, transparent),
            radial-gradient(1px 1px at 55% 45%, #9fe6bd 40%, transparent),
            radial-gradient(1px 1px at 12% 82%, #eafff2 40%, transparent),
            radial-gradient(1px 1px at 33% 22%, #cfefff 40%, transparent);"></div>

    {{-- larger coloured stars --}}
    <div class="showcase-stars-lg absolute inset-0"
         style="background-image:
            radial-gradient(2px 2px at 16% 42%, #f0abfc 50%, transparent),
            radial-gradient(2px 2px at 62% 26%, #7dd3fc 50%, transparent),
            radial-gradient(2px 2px at 78% 62%, #fde68a 50%, transparent),
            radial-gradient(2px 2px at 44% 18%, #99f6e4 50%, transparent),
            radial-gradient(2px 2px at 30% 66%, #f9a8d4 50%, transparent);"></div>

    {{-- shooting stars: quick streaks with long idle gaps --}}
    <span class="shooting-star absolute" style="top: 14%; left: 78%; animation-delay: -1s;"></span>
    <span class="shooting-star absolute" style="top: 30%; left: 92%; animation-delay: -5s; animation-duration: 11s;"></span>
    <span class="shooting-star absolute" style="top: 8%; left: 58%; animation-delay: -8s; animation-duration: 13s;"></span>

    {{-- dominant landmark (planet), top-centre, with an orbiting moon in space --}}
    @if ($planet)
        <div class="absolute left-1/2 -translate-x-1/2" style="top: -6vh; width: clamp(180px, 42vw, 360px); aspect-ratio: 1;">
            <img src="{{ $planet }}" alt="" class="pixel showcase-planet absolute inset-0 h-full w-full object-contain" />
            @if ($isSpace)
                <div class="showcase-orbit absolute inset-[-14%]">
                    <img src="{{ $sp('moon') }}" alt="" class="pixel absolute left-1/2 top-0 -translate-x-1/2"
                         style="width: clamp(26px, 6vw, 52px);" />
                </div>
            @endif
        </div>
    @endif

    @if ($isSpace)
        {{-- scripted space vignettes: something is always happening --}}
        <img src="{{ $sp('rocket') }}" alt="" class="vignette-rocket pixel absolute"
             style="top: 0; left: 0; width: clamp(34px, 7vw, 72px);" />

        <div class="vignette-ufo absolute" style="top: 22%; left: 0;">
            <div class="relative">
                <div class="ufo-beam"></div>
                <img src="{{ $sp('ufo') }}" alt="" class="ufo-body pixel relative" style="width: clamp(40px, 8vw, 82px);" />
            </div>
        </div>

        <img src="{{ $sp('comet') }}" alt="" class="vignette-comet pixel absolute"
             style="top: 0; left: 0; width: clamp(30px, 6vw, 60px); filter: drop-shadow(0 0 10px rgba(125,211,252,0.7));" />

        <img src="{{ $sp('astronaut') }}" alt="" class="vignette-astronaut pixel absolute"
             style="top: 0; left: 0; width: clamp(30px, 6vw, 58px); opacity: 0.9;" />

        <img src="{{ $sp('satellite') }}" alt="" class="pixel showcase-sprite absolute"
             style="top: 58%; right: 10%; width: clamp(30px, 5vw, 56px); animation-duration: 16s; opacity: 0.85;" />
        <img src="{{ $sp('alien') }}" alt="" class="pixel showcase-sprite absolute"
             style="top: 44%; left: 8%; width: clamp(26px, 5vw, 50px); animation-duration: 13s; animation-delay: -4s; opacity: 0.9;" />
    @else
        {{-- non-space themes: gentle drifting scenery sprites --}}
        @foreach ($drifters as $i => $url)
            <img src="{{ $url }}" alt=""
                 class="pixel showcase-sprite absolute"
                 style="
                    top: {{ [18, 26, 40, 52, 34, 60][$i] ?? 30 }}%;
                    {{ $i % 2 ? 'right' : 'left' }}: {{ [8, 14, 20, 12, 24, 6][$i] ?? 12 }}%;
                    width: clamp(28px, {{ 4 + ($i % 3) }}vw, 64px);
                    animation-duration: {{ 11 + $i * 2 }}s;
                    animation-delay: -{{ $i * 3 }}s;
                    opacity: 0.9;" />
        @endforeach
    @endif

    {{-- bottom scrim so the text panel stays legible --}}
    <div class="absolute inset-x-0 bottom-0 h-2/3"
         style="background: linear-gradient(to top, rgba(4,12,8,0.94) 8%, rgba(4,12,8,0.55) 45%, transparent);"></div>
</div>
