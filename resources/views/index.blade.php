<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="MBLAN26. High tech in een houten schuur, de Martin en Bart LAN party.">

    <title>MBLAN26</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=chakra-petch:400,500,600,700|montserrat:400,500,600,700|press-start-2p:400&display=swap" rel="stylesheet" />

    <x-theme-vars :color="$activeEdition?->color" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased bg-forge-black text-forge-steel overflow-hidden">
    <main
        x-data="barnGame({ startX: 10, startY: 84, barnX: 80, barnY: 27, radius: 11, rockX: 49, rockY: 54, rockR: 8.5 })"
        x-init="@if ($errors->any()) open = true @endif"
        class="relative min-h-screen overflow-hidden select-none"
    >
        {{-- ===== The farm map (walkable) ===== --}}
        <div x-ref="map" @click="walkTo($event)" class="absolute inset-0 cursor-pointer">
            <img src="{{ asset('images/farm/backdrop.png') }}" alt=""
                class="pixel absolute inset-0 h-full w-full object-cover" />

            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-forge-black/50 via-forge-forest/20 to-forge-black/70"></div>
            <div class="pointer-events-none absolute inset-0 bg-grid opacity-[0.10]"></div>
            <div class="pointer-events-none absolute inset-0" style="box-shadow: inset 0 0 220px 70px rgba(4,8,6,0.8);"></div>
            <div class="pointer-events-none absolute h-[38vmax] w-[38vmax] -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary-500/15 blur-[120px]" style="left:80%; top:27%;"></div>
            <x-forge.embers class="opacity-50" />

            {{-- barn (goal) --}}
            <div class="pointer-events-none absolute z-10" style="left:80%; top:27%; transform: translate(-50%,-50%);">
                <div class="relative animate-glow-pulse" style="filter: drop-shadow(0 0 22px rgb(var(--c-primary-500)/0.4));">
                    <img src="{{ asset('images/farm/barn.png') }}" alt="Schuur" class="pixel w-[clamp(120px,16vw,210px)]" />
                    <div class="absolute left-1/2 top-[43%] -translate-x-1/2 whitespace-nowrap border-2 border-[#5a3d22] bg-forge-black/85 px-2 py-0.5 font-pixel text-[7px] uppercase tracking-[0.15em] text-primary-200 md:text-[9px]"
                        style="text-shadow: 0 0 8px rgb(var(--c-primary-400)/0.9);">
                        Forged in the Barn
                    </div>
                </div>
            </div>

            {{-- big rock you must walk around --}}
            <img src="{{ asset('images/farm/tile_0089.png') }}" alt=""
                class="pixel pointer-events-none absolute z-10 w-[clamp(74px,9vw,132px)]"
                style="left:49%; top:54%; transform: translate(-50%,-60%); filter: drop-shadow(0 6px 6px rgba(0,0,0,0.5));" />

            {{-- Arti the dog (patrols; touch = back to start) --}}
            <div class="dog" :style="'left:' + arti.x + '%; top:' + arti.y + '%; width: clamp(48px,4.8vw,76px);'">
                <span class="absolute -top-4 left-1/2 -translate-x-1/2 whitespace-nowrap font-pixel text-[7px] uppercase tracking-widest text-primary-200"
                    style="text-shadow:0 0 6px rgb(var(--c-primary-500)/0.9);">Arti</span>
                <img src="{{ asset('images/farm/arti.png') }}" alt="Arti" class="pixel" :style="'scale: ' + arti.dir + ' 1;'" />
            </div>

            {{-- click-to-walk marker --}}
            <template x-if="tx !== null && !done">
                <div class="walk-target z-10" :style="'left:' + tx + '%; top:' + ty + '%'"></div>
            </template>

            {{-- the player --}}
            <div class="player" :class="{ 'is-moving': moving }"
                :style="'left:' + px + '%; top:' + py + '%; width: clamp(40px,3.6vw,60px);' + (caught ? 'opacity:.4;' : '')">
                <img src="{{ asset('images/farm/tile_0109.png') }}" alt="Speler"
                    class="pixel" :style="'scale: ' + facing + ' 1;'"
                    style="filter: drop-shadow(0 3px 4px rgba(0,0,0,0.5));" />
            </div>
        </div>

        {{-- caught-by-Arti flash --}}
        <div x-show="caught" x-transition.opacity class="caught-flash"></div>
        <div x-show="caught" x-cloak class="pointer-events-none fixed inset-0 z-40 flex items-center justify-center">
            <span class="font-pixel text-sm uppercase tracking-widest text-white" style="text-shadow:0 0 10px rgb(180 40 30);">Arti pakte je!</span>
        </div>

        {{-- MBLAN26 wordmark on a wooden plaque --}}
        <div class="pointer-events-none absolute left-1/2 top-[5%] z-30 -translate-x-1/2 px-6 text-center">
            <div class="frame-wood inline-block px-6 py-3 [transform:skewX(-5deg)]">
                <h1 class="flex items-baseline justify-center font-display font-bold leading-none tracking-tight">
                    <span class="bg-gradient-to-b from-white via-[#e7edeb] to-[#7f8f89] bg-clip-text text-transparent text-[clamp(2.2rem,8vw,5rem)] drop-shadow-[0_2px_8px_rgba(0,0,0,0.8)]">MBLAN</span>
                    <span class="bg-gradient-to-b from-primary-200 via-primary-400 to-primary-600 bg-clip-text text-transparent text-[clamp(2.2rem,8vw,5rem)] drop-shadow-[0_0_26px_rgb(var(--c-primary-500)/0.7)]">26</span>
                </h1>
            </div>
        </div>

        {{-- corner login (wooden sign) --}}
        <a href="{{ route('login') }}" @click.prevent.stop="open = true"
            class="btn-wood clip-corner absolute right-5 top-5 z-30 !px-4 !py-2 text-[10px]">
            Inloggen
        </a>

        {{-- walk hint --}}
        <div x-show="!open && !done" x-transition.opacity
            class="pointer-events-none absolute bottom-[6%] left-1/2 z-30 w-full -translate-x-1/2 px-6 text-center">
            <p class="font-pixel text-[10px] uppercase tracking-[0.2em] text-white/85 md:text-xs" style="text-shadow:0 2px 4px rgba(0,0,0,0.8);">
                Loop naar de schuur
            </p>
            <p class="mt-3 font-pixel text-[7px] uppercase tracking-[0.15em] text-white/55">
                WASD / pijltjes &middot; of tik &middot; ontwijk Arti &amp; de rots
            </p>
        </div>

        {{-- ===== Login modal ===== --}}
        <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-forge-black/80 backdrop-blur" @click="closeModal()"></div>

            <div x-show="open" x-transition class="frame-wood relative w-full max-w-md p-8">
                <button type="button" @click="closeModal()"
                    class="absolute right-3 top-3 font-pixel text-xs text-forge-steel/60 hover:text-primary-300">X</button>

                <div class="mb-1 font-pixel text-[8px] uppercase tracking-[0.2em] text-primary-400">De schuur is open</div>
                <h2 class="mb-6 font-display text-2xl font-bold uppercase tracking-wide text-white">Welkom bij MBLAN<span class="text-primary-400">26</span></h2>

                @auth
                    <p class="mb-6 text-sm text-forge-steel/80">Je bent ingelogd. Betreed de schuur voor het schema en de toernooien.</p>
                    <a href="{{ route('schedule') }}" class="btn-wood clip-corner w-full text-xs">Betreed De Schuur</a>
                @else
                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf
                        <x-validation-errors />
                        <div>
                            <x-label for="email" value="E-mail" />
                            <x-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus />
                        </div>
                        <div>
                            <x-label for="password" value="Wachtwoord" />
                            <x-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
                        </div>
                        <label class="flex items-center">
                            <x-checkbox name="remember" />
                            <span class="ms-2 text-sm text-forge-steel/70">Onthoud mij</span>
                        </label>
                        <button type="submit" class="btn-wood clip-corner w-full text-xs">Inloggen</button>
                    </form>

                    <div class="mt-6 flex items-center justify-between font-pixel text-[8px] uppercase tracking-widest">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-forge-steel/60 hover:text-primary-300">Wachtwoord?</a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-primary-300 hover:text-primary-200">Registreren</a>
                        @endif
                    </div>
                @endauth
            </div>
        </div>
    </main>

    @livewireScripts
</body>

</html>
