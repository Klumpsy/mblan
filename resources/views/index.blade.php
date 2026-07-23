<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="description" content="MBLAN26. High tech in een houten schuur, de Martin en Bart LAN party.">

    <title>MBLAN26</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=chakra-petch:400,500,600,700|montserrat:400,500,600,700|press-start-2p:400&display=swap" rel="stylesheet" />

    <x-theme-vars :color="$activeEdition?->color" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

@php $cols = $maze['cols'] ?? 25; $rows = $maze['rows'] ?? 15; @endphp

<body class="font-sans antialiased bg-forge-black text-forge-steel overflow-hidden overscroll-none">
    <main
        x-data="barnGame(@js($maze ?? []))"
        x-init="@if ($errors->any()) open = true @endif"
        class="relative flex min-h-screen items-center justify-center overflow-hidden select-none"
    >
        {{-- viewport ambiance behind the stage --}}
        <div class="pointer-events-none fixed inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gradient-to-b from-[#0a140f] via-forge-black to-[#060b09]"></div>
            <div class="absolute inset-0 bg-grid opacity-[0.08]"></div>
        </div>

        {{-- ===== STAGE: fixed aspect so the map & collision grid align 1:1 ===== --}}
        <div x-ref="map" @click="walkTo($event)"
            class="stage relative overflow-hidden shadow-[0_0_60px_rgba(0,0,0,0.6)]"
            style="aspect-ratio: {{ $cols }} / {{ $rows }}; width: min(100vw, calc(100svh * {{ $cols }} / {{ $rows }})); max-height: 100svh; touch-action: none; cursor: pointer;">

            <img src="{{ asset('images/farm/backdrop.png') }}" alt=""
                class="pixel absolute inset-0 h-full w-full object-cover" />

            {{-- light green tint (keeps the pixel farm crisp, not washed out) --}}
            <div class="pointer-events-none absolute inset-0 bg-primary-500/10 mix-blend-overlay"></div>
            <div class="pointer-events-none absolute inset-0" style="box-shadow: inset 0 0 120px 20px rgba(4,10,7,0.4);"></div>
            <div class="pointer-events-none absolute h-[45%] w-[45%] -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary-500/12 blur-[60px]" :style="'left:'+goal.x+'%; top:'+goal.y+'%;'"></div>
            <x-forge.embers class="opacity-45" />

            {{-- barn (goal) --}}
            <div class="pointer-events-none absolute z-10" :style="'left:'+goal.x+'%; top:'+goal.y+'%; width:15%; transform: translate(-50%,-58%);'">
                <div class="relative animate-glow-pulse" style="filter: drop-shadow(0 0 16px rgb(var(--c-primary-500)/0.4));">
                    <img src="{{ asset('images/farm/barn.png') }}" alt="Schuur" class="pixel w-full" />
                    <div class="absolute left-1/2 top-[43%] -translate-x-1/2 whitespace-nowrap border border-[#5a3d22] bg-forge-black/85 px-1 font-pixel text-[5px] uppercase tracking-[0.1em] text-primary-200 md:text-[7px]"
                        style="text-shadow:0 0 6px rgb(var(--c-primary-400)/0.9);">Forged in the Barn</div>
                </div>
            </div>

            {{-- bones (lure Arti onto one to distract her) --}}
            <template x-for="(b, i) in bones" :key="i">
                <img src="{{ asset('images/farm/bone.png') }}" alt="Bot"
                    class="pixel pointer-events-none absolute z-[8]"
                    :style="'left:' + b.x + '%; top:' + b.y + '%; width:2.6%; transform: translate(-50%,-50%); filter: drop-shadow(0 0 5px rgb(255 255 255 / 0.5));'" />
            </template>

            {{-- hidden axe --}}
            <img src="{{ asset('images/farm/tile_0087.png') }}" alt="Bijl" x-show="!hasAxe"
                class="pixel pointer-events-none absolute z-[8] animate-glow-pulse"
                :style="'left:'+axe.x+'%; top:'+axe.y+'%; width:3%; transform: translate(-50%,-55%); filter: drop-shadow(0 0 6px rgb(var(--c-primary-400)/0.9));'" />

            {{-- choppable gate tree -> stump --}}
            <div class="pointer-events-none absolute z-[9]" :style="'left:'+centerX(gate.c)+'%; top:'+centerY(gate.r)+'%; width:6%; transform: translate(-50%,-60%);'">
                <img x-show="!chopped" src="{{ asset('images/farm/tile_0015.png') }}" alt="Boom" class="pixel w-full"
                    :style="hasAxe ? 'filter: drop-shadow(0 0 8px rgb(var(--c-primary-400)));' : ''" />
                <img x-show="chopped" x-cloak src="{{ asset('images/farm/tile_0014.png') }}" alt="Stronk" class="pixel w-[65%]" />
            </div>

            {{-- Arti --}}
            <div class="pointer-events-none absolute z-[19]" :style="'left:'+arti.x+'%; top:'+arti.y+'%; width:6%; transform: translate(-50%,-88%);'">
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 font-pixel text-[6px] uppercase tracking-widest text-primary-200" style="text-shadow:0 0 6px rgb(var(--c-primary-500)/0.9);">Arti</span>
                <img src="{{ asset('images/farm/arti.png') }}" alt="Arti" class="pixel w-full" style="animation: sprite-bob 0.3s steps(2,end) infinite;" :style="'scale:'+arti.dir+' 1;'" />
            </div>

            {{-- click-to-walk marker --}}
            <template x-if="tx !== null && !done">
                <div class="walk-target z-[11]" :style="'left:'+tx+'%; top:'+ty+'%'"></div>
            </template>

            {{-- player --}}
            <div class="player absolute z-20" :class="{ 'is-moving': moving }"
                :style="'left:'+px+'%; top:'+py+'%; width:5%; transform: translate(-50%,-92%);' + (caught ? 'opacity:.4;' : '')">
                <img src="{{ asset('images/farm/tile_0109.png') }}" alt="Speler" class="pixel w-full"
                    :style="'scale:'+facing+' 1;'" style="filter: drop-shadow(0 2px 3px rgba(0,0,0,0.5));" />
            </div>
        </div>

        {{-- ===== viewport-fixed UI ===== --}}
        {{-- wordmark --}}
        <div class="pointer-events-none absolute left-1/2 top-3 z-30 -translate-x-1/2 px-4 text-center">
            <div class="frame-wood inline-block px-4 py-2 [transform:skewX(-5deg)]">
                <h1 class="flex items-baseline justify-center font-display font-bold leading-none tracking-tight">
                    <span class="bg-gradient-to-b from-white via-[#e7edeb] to-[#7f8f89] bg-clip-text text-transparent text-[clamp(1.6rem,6vw,3.5rem)] drop-shadow-[0_2px_8px_rgba(0,0,0,0.8)]">MBLAN</span>
                    <span class="bg-gradient-to-b from-primary-200 via-primary-400 to-primary-600 bg-clip-text text-transparent text-[clamp(1.6rem,6vw,3.5rem)]">26</span>
                </h1>
            </div>
            <p class="mt-2 font-pixel text-[7px] uppercase tracking-[0.15em] text-white/70 md:text-[9px]">Bereik de schuur, maar pas op voor Arti</p>
            <p class="mt-1 font-pixel text-[6px] uppercase tracking-[0.15em] text-white/40 md:text-[7px]">Lok Arti naar een bot om langs haar te komen</p>
        </div>

        {{-- caught HUD --}}
        <div class="pointer-events-none absolute left-3 top-3 z-30">
            <span class="border-2 border-[#5a3d22] bg-forge-black/70 px-2 py-1 font-pixel text-[8px] uppercase tracking-wider text-primary-200">
                Gepakt: <span x-text="caughtCount"></span>
            </span>
        </div>

        {{-- login (wooden) --}}
        <a href="{{ route('login') }}" @click.prevent.stop="open = true"
            class="btn-wood clip-corner absolute right-3 top-3 z-30 !px-3 !py-2 text-[9px]">Inloggen</a>

        {{-- notice --}}
        <div x-show="notice" x-cloak x-transition class="pointer-events-none absolute left-1/2 top-[26%] z-30 -translate-x-1/2 px-6 text-center">
            <span class="border-2 border-[#5a3d22] bg-forge-black/90 px-3 py-1.5 font-pixel text-[9px] uppercase tracking-[0.12em] text-primary-200" x-text="notice" style="text-shadow:0 0 8px rgb(var(--c-primary-400)/0.9);"></span>
        </div>

        {{-- caught flash --}}
        <div x-show="caught" x-cloak x-transition.opacity class="caught-flash"></div>
        <div x-show="caught" x-cloak class="pointer-events-none fixed inset-0 z-40 flex items-center justify-center">
            <span class="font-pixel text-sm uppercase tracking-widest text-white md:text-lg" style="text-shadow:0 0 12px rgb(200 50 40);">Arti pakte je!</span>
        </div>

        {{-- mobile D-pad --}}
        <div class="absolute bottom-4 right-4 z-30 grid grid-cols-3 gap-1 opacity-80 md:opacity-40" style="width: 132px;">
            @php
                $dbtn = 'btn-wood flex h-10 w-10 items-center justify-center !p-0 text-sm select-none';
            @endphp
            <span></span>
            <button class="{{ $dbtn }}" @pointerdown.prevent="press('arrowup',true)" @pointerup="press('arrowup',false)" @pointerleave="press('arrowup',false)" @pointercancel="press('arrowup',false)">&#9650;</button>
            <span></span>
            <button class="{{ $dbtn }}" @pointerdown.prevent="press('arrowleft',true)" @pointerup="press('arrowleft',false)" @pointerleave="press('arrowleft',false)" @pointercancel="press('arrowleft',false)">&#9664;</button>
            <button class="{{ $dbtn }}" @pointerdown.prevent="press('arrowdown',true)" @pointerup="press('arrowdown',false)" @pointerleave="press('arrowdown',false)" @pointercancel="press('arrowdown',false)">&#9660;</button>
            <button class="{{ $dbtn }}" @pointerdown.prevent="press('arrowright',true)" @pointerup="press('arrowright',false)" @pointerleave="press('arrowright',false)" @pointercancel="press('arrowright',false)">&#9654;</button>
        </div>

        {{-- ===== Login modal ===== --}}
        <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-forge-black/40" @click="closeModal()"></div>
            <div x-show="open" x-transition class="frame-wood relative w-full max-w-md p-8">
                <button type="button" @click="closeModal()" class="absolute right-3 top-3 font-pixel text-xs text-forge-steel/60 hover:text-primary-300">X</button>
                <div class="mb-1 font-pixel text-[8px] uppercase tracking-[0.2em] text-primary-400">De schuur is open</div>
                <h2 class="mb-2 font-display text-2xl font-bold uppercase tracking-wide text-white">Welkom bij MBLAN<span class="text-primary-400">26</span></h2>
                <p class="mb-6 font-pixel text-[8px] uppercase tracking-wider text-forge-steel/60">Arti pakte je <span x-text="caughtCount"></span>x</p>

                @auth
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
