@php
    $edition = \App\Models\Edition::current();
    $planet = $edition?->sceneryLandmark();
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

    {{-- starfield --}}
    <div class="showcase-stars absolute inset-0"
         style="background-image:
            radial-gradient(1px 1px at 20% 30%, #cfefff 40%, transparent),
            radial-gradient(1px 1px at 70% 15%, #eafff2 40%, transparent),
            radial-gradient(1px 1px at 40% 70%, #b7ffd6 40%, transparent),
            radial-gradient(1px 1px at 85% 55%, #cfefff 40%, transparent),
            radial-gradient(1px 1px at 55% 45%, #9fe6bd 40%, transparent),
            radial-gradient(1px 1px at 12% 82%, #eafff2 40%, transparent),
            radial-gradient(1px 1px at 33% 22%, #cfefff 40%, transparent);"></div>

    {{-- dominant landmark (planet), top-centre --}}
    @if ($planet)
        <img src="{{ $planet }}" alt=""
             class="pixel showcase-planet absolute left-1/2 -translate-x-1/2"
             style="top: -6vh; width: clamp(180px, 42vw, 360px);" />
    @endif

    {{-- drifting scenery sprites --}}
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

    {{-- bottom scrim so the text panel stays legible --}}
    <div class="absolute inset-x-0 bottom-0 h-2/3"
         style="background: linear-gradient(to top, rgba(4,12,8,0.94) 8%, rgba(4,12,8,0.55) 45%, transparent);"></div>
</div>
