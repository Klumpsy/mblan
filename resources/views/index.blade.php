<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="description" content="{{ \App\Models\Edition::currentName() }}. High tech in een houten schuur, de Martin en Bart LAN party.">

    <title>{{ \App\Models\Edition::currentName() }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=chakra-petch:400,500,600,700|montserrat:400,500,600,700|press-start-2p:400&display=swap" rel="stylesheet" />


    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-edition-theme />
    @livewireStyles
</head>

@php
    [$brandBase, $brandAccent] = \App\Models\Edition::currentBrand();
@endphp

<body class="font-sans antialiased bg-forge-black text-forge-steel overflow-hidden overscroll-none">
    <main
        x-data="spaceClassic({
            sprites: {
                ship: '{{ asset('images/game/ship.png') }}',
                invader_a1: '{{ asset('images/game/invader_a1.png') }}',
                invader_a2: '{{ asset('images/game/invader_a2.png') }}',
                invader_b1: '{{ asset('images/game/invader_b1.png') }}',
                invader_b2: '{{ asset('images/game/invader_b2.png') }}',
                ufo: '{{ asset('images/scenery/space/ufo.png') }}',
            },
            sync: {
                url: '{{ route('game.sync') }}',
                csrf: '{{ csrf_token() }}',
                authenticated: @json(auth()->check()),
            },
        })"
        x-init="@if ($errors->any()) open = true @endif"
        class="relative flex min-h-dvh items-center justify-center overflow-hidden select-none"
    >
        {{-- viewport ambiance behind the stage --}}
        <div class="pointer-events-none fixed inset-0" aria-hidden="true">
            <div class="absolute inset-0 bg-gradient-to-b from-[#070d14] via-forge-black to-[#05080c]"></div>
            <div class="absolute inset-0 bg-grid opacity-[0.08]"></div>
        </div>

        {{-- ===== STAGE: portrait playfield, fits phone and desktop ===== --}}
        <div x-ref="stage"
            class="stage relative overflow-hidden shadow-[0_0_60px_rgba(0,0,0,0.6)]"
            style="aspect-ratio: 2 / 3; width: min(100vw, calc(100svh * 2 / 3)); max-height: 100svh; touch-action: none; cursor: crosshair;">
            <canvas x-ref="canvas" class="pixel absolute inset-0 h-full w-full"></canvas>
            <div class="pointer-events-none absolute inset-0" style="box-shadow: inset 0 0 120px 20px rgba(3,6,10,0.5);"></div>
        </div>

        {{-- ===== viewport-fixed UI ===== --}}
        {{-- wordmark --}}
        <div class="pointer-events-none absolute left-1/2 top-3 z-30 flex -translate-x-1/2 flex-col items-center px-4 text-center">
            <div class="frame-wood inline-block px-4 py-2 [transform:skewX(-5deg)]">
                <h1 class="flex items-baseline justify-center font-display font-bold leading-none tracking-tight">
                    <span class="bg-gradient-to-b from-white via-[#e7edeb] to-[#7f8f89] bg-clip-text text-transparent text-[clamp(1.6rem,6vw,3.5rem)] drop-shadow-[0_2px_8px_rgba(0,0,0,0.8)]">{{ $brandBase }}</span>
                    @if ($brandAccent !== '')
                        <span class="bg-gradient-to-b from-primary-200 via-primary-400 to-primary-600 bg-clip-text text-transparent text-[clamp(1.6rem,6vw,3.5rem)]">{{ $brandAccent }}</span>
                    @endif
                </h1>
            </div>
            <div class="mt-5 rounded-lg bg-forge-black/70 px-4 py-2 backdrop-blur-sm">
                <p class="font-pixel text-[7px] uppercase tracking-[0.15em] text-white/90 md:text-[9px]">Arti en de boer, de ruimte in</p>
                <p class="mt-1 font-pixel text-[6px] uppercase tracking-[0.15em] text-white/55 md:text-[7px]">Sleep of gebruik de pijltjes, schieten gaat vanzelf</p>
            </div>
        </div>

        {{-- score HUD --}}
        <div class="pointer-events-none absolute left-3 top-3 z-30 flex flex-col gap-1">
            <span class="border-2 border-[#22384a] bg-forge-black/70 px-2 py-1 font-pixel text-[8px] uppercase tracking-wider text-primary-200">
                Score: <span x-text="score"></span>
            </span>
            <span class="border-2 border-[#22384a] bg-forge-black/70 px-2 py-1 font-pixel text-[8px] uppercase tracking-wider text-forge-steel">
                Record: <span x-text="hiscore"></span>
            </span>
            <span class="border-2 border-[#22384a] bg-forge-black/70 px-2 py-1 font-pixel text-[8px] uppercase tracking-wider text-forge-steel">
                Golf: <span x-text="wave"></span>
            </span>
            <span class="border-2 border-[#22384a] bg-forge-black/70 px-2 py-1 font-pixel text-[8px] uppercase tracking-wider text-forge-steel">
                Levens: <span x-text="'♦'.repeat(Math.max(0, lives))"></span>
            </span>
            <button type="button" @click.stop="restart()"
                class="pointer-events-auto border-2 border-[#22384a] bg-forge-black/70 px-2 py-1 text-left font-pixel text-[8px] uppercase tracking-wider text-forge-steel/70 hover:text-primary-200">
                Opnieuw
            </button>
        </div>

        {{-- login --}}
        <a href="{{ route('login') }}" @click.prevent.stop="open = true"
            class="btn-wood clip-corner absolute right-3 top-3 z-30 !px-3 !py-2 text-[9px]">Inloggen</a>

        {{-- game over flash --}}
        <div x-show="over" x-cloak x-transition.opacity class="pointer-events-none fixed inset-0 z-40 flex items-center justify-center">
            <span class="font-pixel text-sm uppercase tracking-widest text-white md:text-lg" style="text-shadow:0 0 12px rgb(200 50 40);">Game over</span>
        </div>

        {{-- ===== Login modal ===== --}}
        <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-forge-black/40" @click="open = false"></div>
            <div x-show="open" x-transition class="frame-wood relative w-full max-w-md p-8">
                <button type="button" @click="open = false" class="absolute right-3 top-3 font-pixel text-xs text-forge-steel/60 hover:text-primary-300">X</button>
                <div class="mb-1 font-pixel text-[8px] uppercase tracking-[0.2em] text-primary-400">De schuur is open</div>
                <h2 class="mb-2 font-display text-2xl font-bold uppercase tracking-wide text-white">Welkom bij {{ $brandBase }}@if ($brandAccent !== '')<span class="text-primary-400">{{ $brandAccent }}</span>@endif</h2>
                <p class="mb-6 font-pixel text-[8px] uppercase tracking-wider text-forge-steel/60"
                    x-text="'Score ' + score + '  ·  golf ' + wave + (score >= hiscore && score > 0 ? '  ·  nieuw record' : '')"></p>

                <button type="button" x-show="over" @click="restart()"
                    class="btn-wood clip-corner mb-4 w-full text-xs">Opnieuw spelen</button>

                @auth
                    <a href="{{ route('schedule') }}" class="btn-wood clip-corner w-full text-xs">Betreed De Schuur</a>
                @else
                    <div x-data="{ showEmail: {{ $errors->any() ? 'true' : 'false' }} }">
                        {{-- Primary: log in with Discord --}}
                        <a href="{{ route('discord.redirect') }}" class="btn-wood clip-corner block w-full text-center text-xs">Login met Discord</a>

                        <button type="button" @click="showEmail = !showEmail"
                            class="mt-4 w-full text-center font-pixel text-[8px] uppercase tracking-widest text-forge-steel/50 hover:text-primary-300">
                            <span x-show="!showEmail">Inloggen met e-mail</span>
                            <span x-show="showEmail" x-cloak>Verberg e-mail login</span>
                        </button>

                        {{-- Fallback: e-mail + wachtwoord (o.a. voor beheerders) --}}
                        <div x-show="showEmail" x-cloak x-transition class="mt-4 border-t border-primary-500/15 pt-4">
                            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                                @csrf
                                <x-validation-errors />
                                <div>
                                    <x-label for="email" value="E-mail" />
                                    <x-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required />
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
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </main>

    @livewireScripts
</body>

</html>
