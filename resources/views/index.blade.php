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

    <main class="relative min-h-screen overflow-hidden">
        {{-- ===== Dark techy backdrop ===== --}}
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-[#0a140f] via-forge-black to-[#040806]"></div>
        <div class="pointer-events-none absolute inset-0 bg-grid opacity-[0.14]"></div>
        <div class="pointer-events-none absolute left-1/2 top-1/2 h-[55vmax] w-[55vmax] -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary-500/10 blur-[130px]"></div>
        <x-forge.embers class="opacity-60" />

        {{-- glowing floor line the gamers walk on --}}
        <div class="pointer-events-none absolute inset-x-0 bottom-[20%] h-px bg-gradient-to-r from-transparent via-primary-400/60 to-transparent"></div>
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-[20%] bg-gradient-to-t from-primary-500/[0.06] to-transparent"></div>

        {{-- ===== The barn (centerpiece) ===== --}}
        <div class="absolute bottom-[20%] left-1/2 z-0 -translate-x-1/2">
            <div class="barn">
                <div class="barn__cable"></div>
                <div class="barn__vane"></div>
                <div class="barn__roof"></div>
                <div class="barn__loft"></div>
                <div class="barn__eave"></div>
                <div class="barn__body">
                    <div class="barn__sign">Forged in the Barn</div>
                    <div class="barn__window barn__window--l"></div>
                    <div class="barn__window barn__window--r"></div>
                    <div class="barn__door">
                        <div class="barn__glow"></div>
                        <div class="barn__screen"></div>
                        <div class="barn__sitter"></div>
                        <div class="barn__threshold"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Little stories: everyone walks into the barn ===== --}}
        <div class="pointer-events-none absolute inset-0 z-[5] overflow-hidden" aria-hidden="true">
            {{-- arriving from the left with their rig --}}
            <div class="walker walker--arrive" style="--bottom: 20%; --dur: 15s; --delay: 0s; --scale: 1.1;">
                <x-forge.gamer gear />
            </div>
            <div class="walker walker--arrive" style="--bottom: 20%; --dur: 19s; --delay: 6s; --scale: 0.85;">
                <x-forge.gamer />
            </div>
            <div class="walker walker--arrive" style="--bottom: 20%; --dur: 23s; --delay: 11s; --scale: 0.7;">
                <x-forge.gamer gear />
            </div>
            {{-- arriving from the right --}}
            <div class="walker walker--arrive-right" style="--bottom: 20%; --dur: 17s; --delay: 3s; --scale: 1;">
                <x-forge.gamer gear />
            </div>
            <div class="walker walker--arrive-right" style="--bottom: 20%; --dur: 21s; --delay: 9s; --scale: 0.8;">
                <x-forge.gamer />
            </div>
        </div>

        {{-- ===== Wordmark (top) ===== --}}
        <div class="absolute left-1/2 top-[8%] z-20 w-full -translate-x-1/2 px-6 text-center">
            <div class="select-none [transform:skewX(-6deg)]" x-data x-reveal>
                <h1 class="flex items-baseline justify-center font-display font-bold leading-none tracking-tight">
                    <span class="bg-gradient-to-b from-white via-[#e7edeb] to-[#7f8f89] bg-clip-text text-transparent text-[clamp(3rem,12vw,8rem)] drop-shadow-[0_3px_12px_rgba(0,0,0,0.7)]">MBLAN</span>
                    <span class="bg-gradient-to-b from-primary-200 via-primary-400 to-primary-600 bg-clip-text text-transparent text-[clamp(3rem,12vw,8rem)] drop-shadow-[0_0_30px_rgb(var(--c-primary-500)/0.7)]">26</span>
                </h1>
            </div>
        </div>

        {{-- ===== Subtitle + actions (bottom) ===== --}}
        <div class="absolute bottom-[5%] left-1/2 z-20 w-full -translate-x-1/2 px-6 text-center">
            <p class="mb-6 text-xs uppercase tracking-[0.3em] text-forge-steel/70 md:text-sm">
                Log in voor het schema en de toernooien
            </p>
            <div class="flex flex-wrap items-center justify-center gap-4">
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
