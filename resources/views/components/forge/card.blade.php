@props(['tilt' => false])
<div
    @if ($tilt) x-data x-tilt @endif
    {{ $attributes->merge(['class' => 'group relative frame-wood p-6 transition-shadow duration-300 hover:shadow-glow-sm']) }}
>
    <span class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-primary-400/80 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></span>
    {{ $slot }}
</div>
