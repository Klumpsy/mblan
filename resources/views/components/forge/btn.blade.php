@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
])
@php
    $base = 'relative overflow-hidden inline-flex items-center justify-center gap-2 font-display font-semibold uppercase tracking-wider text-sm px-7 py-3.5 clip-corner transition-all duration-300 shine';
    $variants = [
        'primary' => 'bg-primary-500 text-forge-black hover:bg-primary-400 hover:shadow-glow hover:-translate-y-0.5',
        'ghost' => 'metal-edge text-primary-300 hover:text-white hover:shadow-glow-sm hover:-translate-y-0.5',
        'anvil' => 'bg-forge-graphite text-forge-steel border border-primary-500/30 hover:text-white hover:border-primary-400 hover:shadow-glow-sm',
    ];
    $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
