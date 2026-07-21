@php
    // Allow an explicit colour (e.g. an edition's own page) to override the active theme.
    $service = app(\App\Support\ThemeService::class);
    $palette = isset($color) && $color
        ? $service->paletteFor($color)
        : $service->activePalette();
@endphp
<style>
    :root {
        @foreach ($palette as $shade => $channels)
        --c-primary-{{ $shade }}: {{ $channels }};
        @endforeach
    }
</style>
