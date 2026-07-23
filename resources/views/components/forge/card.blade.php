@props(['tilt' => false])
<div
    @if ($tilt) x-data x-tilt @endif
    {{ $attributes->merge(['class' => 'relative frame-wood p-6']) }}
>
    {{ $slot }}
</div>
