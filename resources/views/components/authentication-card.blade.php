<div class="relative min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-6 overflow-hidden wood-panel">
    <div class="absolute inset-0 bg-grid opacity-40"></div>
    <x-forge.embers />
    <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-forge-black/40 via-transparent to-forge-black"></div>

    <div class="relative z-10">
        {{ $logo }}
    </div>

    <div x-data x-reveal class="relative z-10 w-full sm:max-w-md mt-6 px-6 py-8 sm:px-8 clip-corner metal-edge overflow-hidden">
        <span class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-primary-400/80 to-transparent"></span>
        {{ $slot }}
    </div>
</div>
