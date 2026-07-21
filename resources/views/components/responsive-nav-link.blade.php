@props(['active'])

@php
    $classes =
        ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary-400 text-start font-display text-sm uppercase tracking-widest text-primary-300 bg-primary-500/10 focus:outline-none transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start font-display text-sm uppercase tracking-widest text-forge-steel hover:text-primary-300 hover:bg-primary-500/5 hover:border-primary-500/40 focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
