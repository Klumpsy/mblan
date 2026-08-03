@php([$base, $accent] = \App\Models\Edition::currentBrand())
<span {{ $attributes->merge(['class' => 'font-display text-2xl font-bold uppercase tracking-widest text-primary-200 text-glow']) }}>
    {{ $base }}@if ($accent !== '')<span class="text-primary-400">{{ $accent }}</span>@endif
</span>
