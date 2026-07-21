@props([
    'eyebrow' => null,
    'as' => 'h2',
])
<div {{ $attributes->merge(['class' => 'mb-10']) }}>
    @if ($eyebrow)
        <div class="mb-3">
            <span class="font-display text-xs uppercase tracking-[0.3em] text-primary-400">{{ $eyebrow }}</span>
        </div>
    @endif
    <{{ $as }} class="font-display text-3xl font-bold uppercase tracking-wide text-white md:text-5xl">{{ $slot }}</{{ $as }}>
</div>
