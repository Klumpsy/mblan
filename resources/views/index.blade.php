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
    <main
        x-data="barnGame({ startX: 10, startY: 84, barnX: 80, barnY: 27, radius: 11 })"
        x-init="@if ($errors->any()) open = true @endif"
        class="relative min-h-screen overflow-hidden select-none"
    >
        {{-- ===== The farm map (walkable) ===== --}}
        <div x-ref="map" @click="walkTo($event)" class="absolute inset-0 cursor-pointer">
            <img src="{{ asset('images/farm/backdrop.png') }}" alt=""
                class="pixel absolute inset-0 h-full w-full object-cover" />

            {{-- green techy blend over the bright farm --}}
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-forge-black/55 via-forge-forest/25 to-forge-black/75"></div>
            <div class="pointer-events-none absolute inset-0 bg-grid opacity-[0.10]"></div>
            <div class="pointer-events-none absolute inset-0" style="box-shadow: inset 0 0 220px 70px rgba(4,8,6,0.85);"></div>
            <div class="pointer-events-none absolute h-[38vmax] w-[38vmax] -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary-500/15 blur-[120px]" style="left:80%; top:27%;"></div>
            <x-forge.embers class="opacity-50" />

            {{-- barn (goal, top-right) --}}
            <div class="pointer-events-none absolute z-10" style="left:80%; top:27%; transform: translate(-50%,-50%);">
                <div class="relative animate-glow-pulse" style="filter: drop-shadow(0 0 22px rgb(var(--c-primary-500)/0.4));">
                    <img src="{{ asset('images/farm/barn.png') }}" alt="Schuur"
                        class="pixel w-[clamp(120px,16vw,210px)]" />
                    <div class="absolute left-1/2 top-[43%] -translate-x-1/2 whitespace-nowrap rounded-sm border border-primary-500/70 bg-forge-black/80 px-2 py-0.5 font-display text-[9px] uppercase tracking-[0.2em] text-primary-200 md:text-[11px]"
                        style="text-shadow: 0 0 8px rgb(var(--c-primary-400)/0.9);">
                        Forged in the Barn
                    </div>
                </div>
            </div>

            {{-- click-to-walk marker --}}
            <template x-if="tx !== null && !done">
                <div class="walk-target z-10" :style="'left:' + tx + '%; top:' + ty + '%'"></div>
            </template>

            {{-- the player character --}}
            <div class="player" :class="{ 'is-moving': moving }"
                :style="'left:' + px + '%; top:' + py + '%; width: clamp(40px,3.6vw,60px);'">
                <img src="{{ asset('images/farm/tile_0109.png') }}" alt="Speler"
                    class="pixel" :style="'scale: ' + facing + ' 1;'"
                    style="filter: drop-shadow(0 3px 4px rgba(0,0,0,0.5));" />
            </div>
        </div>

        {{-- ===== MBLAN26 wordmark (top) ===== --}}
        <div class="pointer-events-none absolute left-1/2 top-[6%] z-30 w-full -translate-x-1/2 px-6 text-center">
            <div class="select-none [transform:skewX(-6deg)]">
                <h1 class="flex items-baseline justify-center font-display font-bold leading-none tracking-tight">
                    <span class="bg-gradient-to-b from-white via-[#e7edeb] to-[#7f8f89] bg-clip-text text-transparent text-[clamp(2.5rem,9vw,6rem)] drop-shadow-[0_3px_12px_rgba(0,0,0,0.8)]">MBLAN</span>
                    <span class="bg-gradient-to-b from-primary-200 via-primary-400 to-primary-600 bg-clip-text text-transparent text-[clamp(2.5rem,9vw,6rem)] drop-shadow-[0_0_30px_rgb(var(--c-primary-500)/0.7)]">26</span>
                </h1>
            </div>
        </div>

        {{-- corner login (fallback + shortcut) --}}
        <a href="{{ route('login') }}" @click.prevent.stop="open = true"
            class="absolute right-5 top-5 z-30 border border-primary-500/30 bg-forge-black/60 px-4 py-2 font-display text-xs uppercase tracking-widest text-forge-steel backdrop-blur transition hover:text-primary-300 hover:border-primary-400 clip-corner">
            Inloggen
        </a>

        {{-- walk hint --}}
        <div x-show="!open && !done" x-transition.opacity
            class="pointer-events-none absolute bottom-[6%] left-1/2 z-30 w-full -translate-x-1/2 px-6 text-center">
            <p class="font-display text-xs uppercase tracking-[0.3em] text-forge-steel/80 md:text-sm">
                Loop naar de schuur
            </p>
            <p class="mt-2 text-[10px] uppercase tracking-[0.25em] text-forge-steel/50">
                WASD of pijltjes &middot; of tik op de kaart
            </p>
        </div>

        {{-- ===== Login modal (opens when you reach the barn) ===== --}}
        <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-forge-black/80 backdrop-blur" @click="open = false"></div>

            <div x-show="open" x-transition
                class="relative w-full max-w-md clip-corner metal-edge bg-forge-panel/95 p-8 shadow-glow">
                <div class="mb-1 font-display text-xs uppercase tracking-[0.3em] text-primary-400">De schuur is open</div>
                <h2 class="mb-6 font-display text-2xl font-bold uppercase tracking-wide text-white">Welkom bij MBLAN<span class="text-primary-400">26</span></h2>

                @auth
                    <p class="mb-6 text-sm text-forge-steel/80">Je bent ingelogd. Betreed de schuur voor het schema en de toernooien.</p>
                    <x-forge.btn href="{{ route('schedule') }}" class="w-full justify-center">Betreed De Schuur</x-forge.btn>
                @else
                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf
                        <x-validation-errors />

                        <div>
                            <x-label for="email" value="E-mail" />
                            <x-input id="email" class="mt-1 block w-full" type="email" name="email"
                                :value="old('email')" required autofocus />
                        </div>
                        <div>
                            <x-label for="password" value="Wachtwoord" />
                            <x-input id="password" class="mt-1 block w-full" type="password" name="password"
                                required autocomplete="current-password" />
                        </div>
                        <label class="flex items-center">
                            <x-checkbox name="remember" />
                            <span class="ms-2 text-sm text-forge-steel/70">Onthoud mij</span>
                        </label>

                        <x-forge.btn type="submit" class="w-full justify-center">Inloggen</x-forge.btn>
                    </form>

                    <div class="mt-6 flex items-center justify-between text-xs uppercase tracking-widest">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-forge-steel/60 hover:text-primary-300">Wachtwoord vergeten?</a>
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
