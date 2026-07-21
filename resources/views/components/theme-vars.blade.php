@props(['color' => null])
@php
    // Explicit colour wins (e.g. the public landing page); otherwise follow the
    // edition the visitor is currently viewing (navbar switcher / active edition).
    $service = app(\App\Support\ThemeService::class);
    $hex = $color ?: app(\App\Support\CurrentEdition::class)->color();
    $palette = $service->paletteFor($hex);
@endphp
<style>
    :root {
        @foreach ($palette as $shade => $channels)
        --c-primary-{{ $shade }}: {{ $channels }};
        @endforeach
    }
</style>
