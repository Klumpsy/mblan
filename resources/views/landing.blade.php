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
    $edition = \App\Models\Edition::current();
    [$brandBase, $brandAccent] = \App\Models\Edition::currentBrand();
    $tagline = $edition?->tagline ?: 'Arti en de boer, de ruimte in';
    $phase = $edition?->countdownPhase() ?? 'none';
@endphp

<body class="font-sans antialiased bg-forge-black text-forge-steel overflow-hidden overscroll-none">
    <main x-data="{ open: false }"
          x-init="@if ($errors->any()) open = true @endif"
          class="relative min-h-dvh w-full overflow-hidden select-none">

        @include('landing._backdrop')

        {{-- ===== bottom-anchored content panel ===== --}}
        <div class="absolute inset-x-0 bottom-0 z-20 flex flex-col items-center gap-5 px-6 pb-10 pt-24 text-center sm:pb-14">

            {{-- wordmark / logo --}}
            @if ($edition?->logo_path)
                <img src="{{ asset('storage/'.$edition->logo_path) }}" alt="{{ \App\Models\Edition::currentName() }}"
                     class="pixel max-h-28 w-auto drop-shadow-[0_2px_10px_rgba(0,0,0,0.8)]" />
            @else
                <h1 class="flex items-baseline justify-center font-display font-bold leading-none tracking-tight">
                    <span class="bg-gradient-to-b from-white via-[#e7edeb] to-[#7f8f89] bg-clip-text text-transparent text-[clamp(2.2rem,10vw,5rem)] drop-shadow-[0_2px_8px_rgba(0,0,0,0.8)]">{{ $brandBase }}</span>
                    @if ($brandAccent !== '')
                        <span class="bg-gradient-to-b from-primary-200 via-primary-400 to-primary-600 bg-clip-text text-transparent text-[clamp(2.2rem,10vw,5rem)]">{{ $brandAccent }}</span>
                    @endif
                </h1>
            @endif

            <p class="font-pixel text-[9px] uppercase tracking-[0.18em] text-white/85 sm:text-[11px]">{{ $tagline }}</p>

            {{-- ===== full-lifecycle countdown ===== --}}
            <div class="min-h-[3.5rem]">
                @if ($phase === 'upcoming')
                    <div x-data="editionCountdown({ target: '{{ $edition->starts_at->toIso8601String() }}' })"
                         class="flex items-stretch gap-2 font-pixel">
                        @foreach ([['days','dg'], ['hours','u'], ['minutes','m'], ['seconds','s']] as [$unit, $label])
                            <div class="min-w-[3rem] rounded border border-primary-500/30 bg-forge-black/60 px-2 py-2">
                                <div class="text-lg font-bold leading-none text-primary-200"
                                     x-text="'{{ $unit }}' === 'days' ? String(days) : String({{ $unit }}).padStart(2, '0')"></div>
                                <div class="mt-1 text-[7px] uppercase tracking-widest text-forge-steel/60">{{ $label }}</div>
                            </div>
                        @endforeach
                    </div>
                @elseif ($phase === 'live')
                    <div class="inline-flex items-center gap-2 rounded border border-primary-500/40 bg-primary-500/10 px-4 py-3 font-pixel text-sm uppercase tracking-[0.2em] text-primary-200">
                        <span class="inline-block h-2 w-2 animate-pulse rounded-full bg-primary-400"></span> NU BEZIG
                    </div>
                @elseif ($phase === 'over')
                    <p class="font-pixel text-[9px] uppercase tracking-widest text-forge-steel/70">
                        Tot ziens ·
                        <a href="{{ route('editions.show', $edition) }}" class="text-primary-300 hover:text-primary-200">bekijk de recap</a>
                    </p>
                @else
                    <p class="font-pixel text-[9px] uppercase tracking-widest text-forge-steel/60">Datum volgt</p>
                @endif
            </div>

            {{-- ===== single CTA ===== --}}
            @auth
                <a href="{{ route('schedule') }}" class="btn-wood clip-corner text-sm">Doe mee</a>
            @else
                <button type="button" @click="open = true" class="btn-wood clip-corner text-sm">Doe mee</button>
            @endauth

            <a href="{{ route('spel') }}" class="font-pixel text-[7px] uppercase tracking-widest text-forge-steel/45 hover:text-primary-300">Speel Arti in Space</a>
        </div>

        @include('landing._auth-modal')
    </main>

    @livewireScripts
</body>

</html>
