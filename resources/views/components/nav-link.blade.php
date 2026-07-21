@props(['active'])

@php
    $classes =
        ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-primary-400 font-display text-xs uppercase tracking-widest leading-5 text-white focus:outline-none transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent font-display text-xs uppercase tracking-widest leading-5 text-forge-steel hover:text-primary-300 hover:border-primary-500/40 focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
