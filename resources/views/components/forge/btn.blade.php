@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
])
@php
    $base = 'relative overflow-hidden inline-flex items-center justify-center gap-2 font-pixel uppercase tracking-wider text-[10px] leading-none px-6 py-3.5 transition-all duration-200 shine';
    $variants = [
        'primary' => 'text-forge-black bg-primary-500 border-2 border-[#1f3a2c] hover:bg-primary-400 hover:-translate-y-0.5 [box-shadow:inset_0_2px_0_rgb(255_255_255/0.4),0_3px_0_#1f3a2c]',
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
