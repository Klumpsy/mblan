@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-display uppercase tracking-wider text-xs text-forge-steel']) }}>
    {{ $value ?? $slot }}
</label>
