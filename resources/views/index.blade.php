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

<body class="font-sans antialiased bg-forge-black text-forge-steel overflow-x-hidden">
    <x-flash-message />

    {{-- ================= NAV ================= --}}
    <header x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 40"
        :class="scrolled ? 'bg-forge-black/85 backdrop-blur border-b border-primary-500/20' : 'border-b border-transparent'"
        class="fixed inset-x-0 top-0 z-50 transition-all duration-300">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <a href="#top" class="font-display text-xl font-bold uppercase tracking-widest text-white">
                MBLAN<span class="text-primary-400">26</span>
            </a>
            <div class="hidden items-center gap-8 md:flex">
                <a href="#schedule" class="text-sm uppercase tracking-widest text-forge-steel transition hover:text-primary-300">Schema</a>
                <a href="#tournaments" class="text-sm uppercase tracking-widest text-forge-steel transition hover:text-primary-300">Toernooien</a>
                @if ($latestBlogs->isNotEmpty())
                    <a href="#news" class="text-sm uppercase tracking-widest text-forge-steel transition hover:text-primary-300">Nieuws</a>
                @endif
                <a href="#signup" class="text-sm uppercase tracking-widest text-forge-steel transition hover:text-primary-300">Aanmelden</a>
            </div>
            <div class="flex items-center gap-3">
                @auth
                    <x-forge.btn href="{{ url('/dashboard') }}" class="!px-5 !py-2.5">Dashboard</x-forge.btn>
                @else
                    <a href="{{ route('login') }}" class="hidden text-sm uppercase tracking-widest text-forge-steel transition hover:text-primary-300 sm:inline">Inloggen</a>
                    @if (Route::has('register'))
                        <x-forge.btn href="{{ route('register') }}" class="!px-5 !py-2.5">Registreren</x-forge.btn>
                    @endif
                @endauth
            </div>
        </nav>
    </header>

    {{-- ================= HERO ================= --}}
    <section id="top" class="relative flex min-h-screen items-center justify-center overflow-hidden wood-panel">
        <div class="absolute inset-0 bg-grid opacity-40"></div>
        <x-forge.embers />
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-forge-black/40 via-transparent to-forge-black"></div>

        <div class="relative z-10 mx-auto flex max-w-5xl flex-col items-center px-6 text-center">
            <div class="mb-6 animate-glow-pulse" x-data x-reveal>
                <x-forge.badge>{{ $activeEdition?->name ?? 'MBLAN26' }}</x-forge.badge>
            </div>

            <img src="{{ asset('images/mblan26-logo.jpg') }}" alt="MBLAN26"
                class="mb-8 w-full max-w-2xl mix-blend-screen drop-shadow-[0_0_45px_rgb(var(--c-primary-500)/0.35)] transition-transform duration-700 hover:scale-[1.02]" />

            <p class="mb-3 font-display text-lg uppercase tracking-[0.4em] text-primary-300 text-glow md:text-2xl">
                Gesmeed in de Schuur
            </p>
            <p class="mb-10 max-w-xl text-sm text-forge-steel/80 md:text-base">
                High tech in een houten schuur. Eén keer per jaar komen vrienden samen om
                vriendschappen, inside jokes en legendarische herinneringen te smeden. Dit is niet zomaar een LAN party, dit is MBLAN.
            </p>

            <div class="mb-10 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 font-display text-xs uppercase tracking-[0.25em] text-forge-steel/70">
                <span>{{ $activeEdition?->year ?? '2026' }}</span>
                <span class="text-primary-400/50">/</span>
                <span>The Barn, Locatie Geheim</span>
                <span class="text-primary-400/50">/</span>
                <span>Binnenkort</span>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-4">
                <x-forge.btn href="#signup">Aanmelden</x-forge.btn>
                @auth
                    <x-forge.btn variant="ghost" href="{{ url('/dashboard') }}">Betreed De Schuur</x-forge.btn>
                @else
                    <x-forge.btn variant="ghost" href="{{ route('login') }}">Inloggen</x-forge.btn>
                @endauth
            </div>
        </div>

        <a href="#story" class="absolute bottom-8 left-1/2 -translate-x-1/2 text-primary-400/70 animate-float" aria-label="Scroll omlaag">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
        </a>
    </section>

    {{-- ================= STORY / STATS ================= --}}
    <section id="story" class="relative border-y border-primary-500/10 bg-forge-forest/40 py-24">
        <div class="mx-auto max-w-6xl px-6">
            <div class="grid items-center gap-14 md:grid-cols-2">
                <div x-data x-reveal>
                    <x-forge.heading eyebrow="Het Verhaal">Dit is niet zomaar<br>een LAN party</x-forge.heading>
                    <div class="space-y-4 text-forge-steel/80">
                        <p>Elk jaar verandert een groep vrienden een schuur voor één weekend in een digitale arena.
                            Hier worden geen zwaarden gesmeed, maar wel vriendschappen, inside jokes, overwinningen en
                            legendarische herinneringen.</p>
                        <p class="font-display uppercase tracking-widest text-primary-300">
                            Dit is niet zomaar een LAN party. Dit is MBLAN.
                        </p>
                    </div>
                    <div class="mt-10 grid grid-cols-3 gap-6">
                        <x-forge.stat value="{{ $stats['editions'] }}" label="Edities" x-data x-reveal.100 />
                        <x-forge.stat value="{{ $stats['games'] }}" label="Games" x-data x-reveal.200 />
                        <x-forge.stat value="{{ $stats['players'] }}" label="Spelers" x-data x-reveal.300 />
                    </div>
                </div>
                <div class="relative" x-data x-reveal.150>
                    <div class="clip-corner-lg metal-edge overflow-hidden">
                        <img src="{{ asset('images/mblan26-brandboard.jpg') }}" alt="MBLAN26 brand" class="w-full opacity-90" loading="lazy" />
                    </div>
                    <div class="pointer-events-none absolute -inset-4 -z-10 bg-primary-500/10 blur-3xl"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= SCHEDULE ================= --}}
    @if ($activeEdition && $activeEdition->schedules->isNotEmpty())
        <section id="schedule" class="relative py-24">
            <div class="mx-auto max-w-6xl px-6">
                <div x-data x-reveal>
                    <x-forge.heading eyebrow="Speelschema">Speelschema</x-forge.heading>
                </div>
                <div class="grid gap-6 md:grid-cols-3">
                    @foreach ($activeEdition->schedules as $i => $day)
                        <div x-data x-reveal.{{ ($i % 3) * 100 }}>
                            <x-forge.card class="h-full">
                                <p class="font-display text-xs uppercase tracking-[0.3em] text-primary-400">
                                    {{ \Illuminate\Support\Carbon::parse($day->date)->format('D d M') }}
                                </p>
                                <h3 class="mb-4 mt-1 font-display text-xl font-bold uppercase tracking-wide text-white">{{ $day->name }}</h3>
                                <ul class="space-y-3">
                                    @forelse ($day->games as $game)
                                        <li class="flex items-center justify-between gap-3 border-t border-primary-500/10 pt-3">
                                            <span class="text-sm text-forge-steel">{{ $game->name }}</span>
                                            <span class="whitespace-nowrap font-display text-xs uppercase tracking-widest text-forge-steel/60">
                                                {{ \Illuminate\Support\Carbon::parse($game->pivot->start_date)->format('H:i') }}
                                            </span>
                                        </li>
                                    @empty
                                        <li class="text-sm text-forge-steel/50">Wordt nog bekendgemaakt</li>
                                    @endforelse
                                </ul>
                            </x-forge.card>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ================= TOURNAMENTS ================= --}}
    @if ($tournaments->isNotEmpty())
        @php
            // Show the live tournament(s) first, then fill up to four ladders.
            $ladderTournaments = $tournaments->sortByDesc('is_active')->take(4)->values();
        @endphp
        <section id="tournaments" class="relative border-y border-primary-500/10 bg-forge-forest/40 py-24">
            <div class="mx-auto max-w-6xl px-6">
                <div x-data x-reveal>
                    <x-forge.heading eyebrow="Deelnemers en Scores">Toernooien</x-forge.heading>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    @foreach ($ladderTournaments as $i => $tournament)
                        <div x-data x-reveal.{{ ($i % 2) * 120 }}>
                            <livewire:tournament.ladder :tournament="$tournament" :key="'home-' . $tournament->id" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ================= NEWS ================= --}}
    @if ($latestBlogs->isNotEmpty())
        <section id="news" class="relative py-24">
            <div class="mx-auto max-w-6xl px-6">
                <div x-data x-reveal>
                    <x-forge.heading eyebrow="Uit De Schuur">Laatste Nieuws</x-forge.heading>
                </div>
                <div class="grid gap-6 md:grid-cols-3">
                    @foreach ($latestBlogs as $i => $blog)
                        <div x-data x-reveal.{{ $i * 120 }}>
                            <x-forge.card class="flex h-full flex-col overflow-hidden !p-0">
                                @if ($blog->image)
                                    <div class="aspect-video w-full overflow-hidden">
                                        <img src="{{ asset('storage/' . $blog->image) }}" alt="{{ $blog->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" />
                                    </div>
                                @endif
                                <div class="flex flex-1 flex-col p-5">
                                    <p class="mb-2 text-xs uppercase tracking-widest text-primary-400/80">{{ optional($blog->published_at)->format('d M Y') }}</p>
                                    <h3 class="mb-2 font-display text-lg font-semibold uppercase tracking-wide text-white">{{ $blog->title }}</h3>
                                    <p class="line-clamp-3 flex-1 text-sm text-forge-steel/70">{{ $blog->preview_text }}</p>
                                    @auth
                                        <a href="{{ route('blogs.show', $blog->slug) }}" class="mt-4 inline-block text-xs uppercase tracking-widest text-primary-300 hover:text-primary-200">Lees meer</a>
                                    @endauth
                                </div>
                            </x-forge.card>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ================= SIGNUP CTA ================= --}}
    <section id="signup" class="relative overflow-hidden border-t border-primary-500/10 wood-panel py-28">
        <div class="absolute inset-0 bg-grid opacity-30"></div>
        <div class="relative mx-auto max-w-3xl px-6 text-center" x-data x-reveal>
            <h2 class="font-display text-4xl font-bold uppercase tracking-wide text-white md:text-6xl">Claim Je Plek</h2>
            <p class="mx-auto mt-4 max-w-lg text-forge-steel/70">
                {{ $activeEdition?->name ?? 'MBLAN26' }} komt eraan. Meld je aan, kies je games en laat je smeden in de schuur.
            </p>
            <div class="mt-10 flex flex-wrap justify-center gap-4">
                @auth
                    @if ($activeEdition && !auth()->user()->hasSignedUpFor($activeEdition) && $activeEdition->hasExclusiveAccess(auth()->user()))
                        <x-forge.btn href="{{ route('editions.signup', $activeEdition->slug) }}">Aanmelden voor {{ $activeEdition->name }}</x-forge.btn>
                    @else
                        <x-forge.btn href="{{ url('/dashboard') }}">Betreed De Schuur</x-forge.btn>
                    @endif
                @else
                    @if (Route::has('register'))
                        <x-forge.btn href="{{ route('register') }}">Account Aanmaken</x-forge.btn>
                    @endif
                    <x-forge.btn variant="ghost" href="{{ route('login') }}">Inloggen</x-forge.btn>
                @endauth
            </div>
        </div>
    </section>

    {{-- ================= FOOTER ================= --}}
    <footer class="border-t border-primary-500/10 bg-forge-black py-10">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 md:flex-row">
            <span class="font-display text-lg font-bold uppercase tracking-widest text-white">MBLAN<span class="text-primary-400">26</span></span>
            <p class="text-xs uppercase tracking-widest text-forge-steel/50">Martin en Bart LAN Party</p>
        </div>
    </footer>

    @livewireScripts
</body>

</html>
