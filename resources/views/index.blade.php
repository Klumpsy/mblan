<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="MBLAN26. High tech in een houten schuur, de Martin en Bart LAN party.">

    <title>MBLAN26</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=chakra-petch:400,500,600,700|montserrat:400,500,600,700&display=swap" rel="stylesheet" />

    <x-theme-vars :color="$activeEdition?->color" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased bg-forge-black text-forge-steel overflow-hidden">
    <x-flash-message />

    <main class="relative flex min-h-screen items-center justify-center">
        {{-- ===== Deep space backdrop ===== --}}
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-[#04100c] via-forge-black to-[#030605]"></div>
        <div class="pointer-events-none absolute inset-0 starfield"></div>
        <div class="pointer-events-none absolute inset-0 starfield starfield-2"></div>
        <div class="pointer-events-none absolute inset-0 bg-grid opacity-[0.10]"></div>
        <div class="pointer-events-none absolute left-1/2 top-1/2 h-[60vmax] w-[60vmax] -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary-500/10 blur-[130px]"></div>
        <x-forge.embers class="opacity-70" />

        {{-- ===== Animated space scene: planets + walking astronauts ===== --}}
        <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
            {{-- distant ringed planet, top-right --}}
            <div class="planet absolute right-[8%] top-[12%] h-28 w-28 animate-float md:h-40 md:w-40">
                <span class="planet__ring"></span>
            </div>
            {{-- small moon, upper-left --}}
            <div class="planet absolute left-[10%] top-[20%] h-12 w-12 opacity-80"></div>

            {{-- glowing planet horizon the astronauts walk on --}}
            <div class="planet-surface"></div>

            {{-- a gamer floating & tumbling near the ringed planet ("doing stuff in space") --}}
            <div class="walker walker--float" style="--bottom: 62%; --dur: 15s; --delay: 0s; --scale: 0.85;">
                <x-forge.gamer />
            </div>

            {{-- astronauts strolling across the horizon --}}
            <div class="walker" style="--bottom: 9%; --dur: 24s; --delay: 0s; --scale: 1.15;"><x-forge.gamer /></div>
            <div class="walker walker--reverse" style="--bottom: 13%; --dur: 30s; --delay: 3s; --scale: 0.8;"><x-forge.gamer /></div>
            <div class="walker" style="--bottom: 7%; --dur: 21s; --delay: 6s; --scale: 0.95;"><x-forge.gamer /></div>
            <div class="walker" style="--bottom: 16%; --dur: 34s; --delay: 1.5s; --scale: 0.65;"><x-forge.gamer /></div>
            <div class="walker walker--reverse" style="--bottom: 11%; --dur: 27s; --delay: 9s; --scale: 1;"><x-forge.gamer /></div>
        </div>

        {{-- ===== Center content ===== --}}
        <div class="relative z-10 mx-auto flex max-w-4xl flex-col items-center px-6 text-center">
            {{-- Wordmark: silver MBLAN + neon 26, blends into the dark --}}
            <div class="select-none [transform:skewX(-6deg)]" x-data x-reveal>
                <h1 class="flex items-baseline justify-center font-display font-bold leading-none tracking-tight">
                    <span class="bg-gradient-to-b from-white via-[#e7edeb] to-[#7f8f89] bg-clip-text text-transparent text-[clamp(3.5rem,15vw,10rem)] drop-shadow-[0_3px_12px_rgba(0,0,0,0.7)]">MBLAN</span>
                    <span class="bg-gradient-to-b from-primary-200 via-primary-400 to-primary-600 bg-clip-text text-transparent text-[clamp(3.5rem,15vw,10rem)] drop-shadow-[0_0_30px_rgb(var(--c-primary-500)/0.7)]">26</span>
                </h1>
            </div>

            <p class="mt-8 max-w-md text-sm uppercase tracking-[0.25em] text-forge-steel/70 md:text-base">
                Log in voor het schema en de toernooien
            </p>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                @auth
                    <x-forge.btn href="{{ route('schedule') }}">Betreed De Schuur</x-forge.btn>
                @else
                    <x-forge.btn href="{{ route('login') }}">Inloggen</x-forge.btn>
                    @if (Route::has('register'))
                        <x-forge.btn variant="ghost" href="{{ route('register') }}">Registreren</x-forge.btn>
                    @endif
                @endauth
            </div>
        </div>
    </main>

    @livewireScripts
</body>

</html>
