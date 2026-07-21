@props([
    'value' => '',
    'label' => '',
])
<div {{ $attributes->merge(['class' => 'text-center']) }}>
    <div class="font-display text-4xl font-bold neon-text md:text-5xl">{{ $value }}</div>
    <div class="mt-1 text-xs uppercase tracking-[0.2em] text-forge-steel/70">{{ $label }}</div>
</div>
