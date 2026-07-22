<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="MBLAN26. Gesmeed in de Schuur. High tech in een houten schuur, de Martin en Bart LAN party.">

    <title>MBLAN26 - Gesmeed in de Schuur</title>

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
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-[#050b09] via-forge-black to-[#040706]"></div>
        <div class="pointer-events-none absolute inset-0 starfield"></div>
        <div class="pointer-events-none absolute inset-0 starfield starfield-2"></div>
        <div class="pointer-events-none absolute inset-0 bg-grid opacity-[0.12]"></div>
        {{-- radial glow behind the logo --}}
        <div class="pointer-events-none absolute left-1/2 top-1/2 -z-0 h-[60vmax] w-[60vmax] -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary-500/10 blur-[120px]"></div>
        {{-- forge embers --}}
        <x-forge.embers class="opacity-70" />

        {{-- ===== Floating gaming avatars ===== --}}
        <div class="pointer-events-none absolute inset-0 hidden sm:block" aria-hidden="true">
            @php
                $spots = [
                    ['top-[18%] left-[12%]', '11s', '0s'],
                    ['top-[26%] right-[14%]', '13s', '1.2s'],
                    ['bottom-[22%] left-[18%]', '12s', '0.6s'],
                    ['bottom-[28%] right-[16%]', '14s', '2s'],
                    ['top-[42%] left-[6%]', '15s', '0.9s'],
                    ['top-[60%] right-[8%]', '12.5s', '1.6s'],
                    ['top-[12%] left-[46%]', '13.5s', '0.3s'],
                    ['bottom-[14%] left-[52%]', '12s', '2.4s'],
                ];
            @endphp
            @foreach ($avatarNames as $i => $name)
                @php $spot = $spots[$i % count($spots)]; @endphp
                <div class="avatar-orb absolute {{ $spot[0] }}" style="--dur: {{ $spot[1] }}; --delay: {{ $spot[2] }};">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full border border-primary-500/40 bg-forge-panel/70 backdrop-blur shadow-glow-sm">
                        <span class="font-display text-sm uppercase text-primary-200">{{ \Illuminate\Support\Str::of($name)->substr(0, 2) }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ===== Center content ===== --}}
        <div class="relative z-10 mx-auto flex max-w-3xl flex-col items-center px-6 text-center">
            <div class="mb-6 animate-glow-pulse" x-data x-reveal>
                <span class="font-display text-xs uppercase tracking-[0.4em] text-primary-300">
                    {{ $activeEdition?->name ?? 'MBLAN26' }}
                </span>
            </div>

            <img src="{{ asset('images/mblan26-logo.jpg') }}" alt="MBLAN26"
                class="mb-8 w-full max-w-xl mix-blend-screen drop-shadow-[0_0_55px_rgb(var(--c-primary-500)/0.45)] transition-transform duration-700 hover:scale-[1.02]" />

            <p class="mb-4 font-display text-lg uppercase tracking-[0.4em] text-primary-300 text-glow md:text-2xl">
                Gesmeed in de Schuur
            </p>
            <p class="mb-10 max-w-md text-sm text-forge-steel/70 md:text-base">
                High tech in een houten schuur. Log in om het speelschema en de toernooien te bekijken.
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

            <p class="mt-16 font-display text-[0.65rem] uppercase tracking-[0.35em] text-forge-steel/40">
                Martin en Bart LAN Party
            </p>
        </div>
    </main>

    @livewireScripts
</body>

</html>
