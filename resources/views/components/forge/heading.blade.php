@props([
    'eyebrow' => null,
    'as' => 'h2',
])
<div {{ $attributes->merge(['class' => 'mb-10']) }}>
    @if ($eyebrow)
        <div class="mb-3">
            <span class="font-pixel text-[9px] uppercase tracking-[0.2em] text-primary-400 md:text-[10px]">{{ $eyebrow }}</span>
        </div>
    @endif
    <{{ $as }} class="font-display text-3xl font-bold uppercase tracking-wide text-white md:text-5xl">{{ $slot }}</{{ $as }}>
</div>
