<button {{ $attributes->merge(['type' => 'submit', 'class' => 'relative overflow-hidden inline-flex items-center justify-center gap-2 font-display font-semibold uppercase tracking-wider text-sm px-7 py-3 clip-corner transition-all duration-300 shine bg-primary-500 text-forge-black hover:bg-primary-400 hover:shadow-glow hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-forge-black']) }}>
    {{ $slot }}
</button>
