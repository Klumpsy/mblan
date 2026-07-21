@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'bg-forge-graphite border border-primary-500/20 text-white placeholder-forge-steel/40 focus:border-primary-400 focus:ring-primary-500 rounded-none clip-corner shadow-sm transition']) !!}>
