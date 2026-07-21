<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="MBLAN26 — The Forge III. Forged in the barn. High tech in a wooden barn — the Martin & Bart LAN party.">

    <title>MBLAN26 — The Forge</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=chakra-petch:400,500,600,700|montserrat:400,500,600,700&display=swap" rel="stylesheet" />

    <x-theme-vars :color="$activeEdition?->color" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased bg-forge-black text-forge-steel overflow-x-hidden">
    <x-flash-message />

    {{-- ================= NAV ================= --}}
    <header
        x-data="{ scrolled: false }"
        @scroll.window="scrolled = window.scrollY > 40"
        :class="scrolled ? 'bg-forge-black/85 backdrop-blur border-b border-primary-500/20' : 'border-b border-transparent'"
        class="fixed inset-x-0 top-0 z-50 transition-all duration-300"
    >
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <a href="#top" class="font-display text-xl font-bold uppercase tracking-widest text-white">
                MBLAN<span class="text-primary-400">26</span>
            </a>

            <div class="hidden items-center gap-8 md:flex">
                <a href="#story" class="text-sm uppercase tracking-widest text-forge-steel transition hover:text-primary-300">The Forge</a>
                <a href="#games" class="text-sm uppercase tracking-widest text-forge-steel transition hover:text-primary-300">Games</a>
                <a href="#editions" class="text-sm uppercase tracking-widest text-forge-steel transition hover:text-primary-300">Editions</a>
                @if ($latestBlogs->isNotEmpty())
                    <a href="#news" class="text-sm uppercase tracking-widest text-forge-steel transition hover:text-primary-300">News</a>
                @endif
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <x-forge.btn href="{{ url('/dashboard') }}" class="!px-5 !py-2.5">Dashboard</x-forge.btn>
                @else
                    <a href="{{ route('login') }}" class="hidden text-sm uppercase tracking-widest text-forge-steel transition hover:text-primary-300 sm:inline">Login</a>
                    @if (Route::has('register'))
                        <x-forge.btn href="{{ route('register') }}" class="!px-5 !py-2.5">Join</x-forge.btn>
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
                <x-forge.badge>Editie III &bull; 2026</x-forge.badge>
            </div>

            <img src="{{ asset('images/mblan26-logo.jpg') }}" alt="MBLAN26 — The Forge"
                class="mb-8 w-full max-w-2xl mix-blend-screen drop-shadow-[0_0_45px_rgb(var(--c-primary-500)/0.35)] transition-transform duration-700 hover:scale-[1.02]" />

            <p class="mb-3 font-display text-lg uppercase tracking-[0.4em] text-primary-300 text-glow md:text-2xl">
                Forged in the Barn
            </p>
            <p class="mb-10 max-w-xl text-sm text-forge-steel/80 md:text-base">
                High tech in a wooden barn. Once a year, friends gather to forge friendships,
                inside jokes, and legendary memories. This isn't just a LAN party — this is MBLAN.
            </p>

            {{-- Event info chips --}}
            <div class="mb-10 flex flex-wrap items-center justify-center gap-3 text-xs uppercase tracking-widest text-forge-steel/80">
                <span class="metal-edge clip-corner px-4 py-2">📅 Editie III &middot; 2026</span>
                <span class="metal-edge clip-corner px-4 py-2">📍 The Barn &middot; Location Secret</span>
                <span class="metal-edge clip-corner px-4 py-2">⏳ Coming Soon</span>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-4">
                @auth
                    <x-forge.btn href="{{ url('/dashboard') }}">Enter The Forge</x-forge.btn>
                @else
                    <x-forge.btn href="{{ route('login') }}">Login</x-forge.btn>
                    @if (Route::has('register'))
                        <x-forge.btn variant="ghost" href="{{ route('register') }}">Register</x-forge.btn>
                    @endif
                @endauth
            </div>
        </div>

        <a href="#story" class="absolute bottom-8 left-1/2 -translate-x-1/2 text-primary-400/70 animate-float" aria-label="Scroll down">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
        </a>
    </section>

    {{-- ================= STORY / STATS ================= --}}
    <section id="story" class="relative border-y border-primary-500/10 bg-forge-forest/40 py-24">
        <div class="mx-auto max-w-6xl px-6">
            <div class="grid items-center gap-14 md:grid-cols-2">
                <div x-data x-reveal>
                    <x-forge.heading eyebrow="The Brand Story">This isn't just a<br>LAN party</x-forge.heading>
                    <div class="space-y-4 text-forge-steel/80">
                        <p>Every year, a group of friends turns a barn into a digital forge for one weekend.
                            No swords are hammered here — but friendships, inside jokes, victories and
                            legendary memories are.</p>
                        <p class="font-display uppercase tracking-widest text-primary-300">
                            This isn't just a LAN party. This is MBLAN.
                        </p>
                    </div>
                    <div class="mt-10 grid grid-cols-3 gap-6">
                        <x-forge.stat value="{{ $stats['editions'] }}" label="Editions" x-data x-reveal.100 />
                        <x-forge.stat value="{{ $stats['games'] }}" label="Games" x-data x-reveal.200 />
                        <x-forge.stat value="{{ $stats['players'] }}" label="Players" x-data x-reveal.300 />
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

    {{-- ================= FEATURED GAMES ================= --}}
    @if ($featuredGames->isNotEmpty())
        <section id="games" class="relative py-24">
            <div class="mx-auto max-w-7xl px-6">
                <div x-data x-reveal>
                    <x-forge.heading eyebrow="On The Big Screens">Featured Games</x-forge.heading>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredGames as $i => $game)
                        <div x-data x-reveal.{{ ($i % 3) * 100 }}>
                            <x-forge.card tilt class="h-full overflow-hidden !p-0">
                                <div class="aspect-video w-full overflow-hidden">
                                    @if ($game->image)
                                        <img src="{{ asset('storage/' . $game->image) }}" alt="{{ $game->name }}"
                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" />
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-forge-graphite text-forge-steel/40">No image</div>
                                    @endif
                                </div>
                                <div class="p-5">
                                    <h3 class="font-display text-lg font-semibold uppercase tracking-wide text-white">{{ $game->name }}</h3>
                                    <p class="mt-1 text-xs uppercase tracking-widest text-primary-400/80">{{ $game->year_of_release ?? '' }}</p>
                                </div>
                            </x-forge.card>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <x-forge.divider class="mx-auto max-w-5xl" />
    @endif

    {{-- ================= EDITIONS ================= --}}
    <section id="editions" class="relative py-24">
        <div class="mx-auto max-w-6xl px-6">
            <div x-data x-reveal>
                <x-forge.heading eyebrow="The Legacy">Past Editions</x-forge.heading>
            </div>

            @if ($pastEditions->isNotEmpty())
                <div class="grid gap-6 md:grid-cols-3">
                    @foreach ($pastEditions as $i => $edition)
                        <div x-data x-reveal.{{ $i * 120 }}>
                            <x-forge.card class="h-full">
                                <div class="mb-4 flex items-center gap-3">
                                    <span class="h-3 w-3 rounded-full" style="background: {{ $edition->color ?? '#65E59A' }}; box-shadow: 0 0 12px {{ $edition->color ?? '#65E59A' }};"></span>
                                    <span class="font-display text-xs uppercase tracking-widest text-forge-steel/60">{{ $edition->year }}</span>
                                </div>
                                <h3 class="mb-2 font-display text-2xl font-bold uppercase tracking-wide text-white">{{ $edition->name }}</h3>
                                <div class="line-clamp-3 text-sm text-forge-steel/70">{!! strip_tags($edition->description) !!}</div>
                            </x-forge.card>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-forge-steel/60">The legacy begins with MBLAN26.</p>
            @endif
        </div>
    </section>

    {{-- ================= NEWS ================= --}}
    @if ($latestBlogs->isNotEmpty())
        <x-forge.divider class="mx-auto max-w-5xl" />
        <section id="news" class="relative py-24">
            <div class="mx-auto max-w-6xl px-6">
                <div x-data x-reveal>
                    <x-forge.heading eyebrow="From The Barn">Latest News</x-forge.heading>
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
                                        <a href="{{ route('blogs.show', $blog->slug) }}" class="mt-4 inline-block text-xs uppercase tracking-widest text-primary-300 hover:text-primary-200">Read more →</a>
                                    @endauth
                                </div>
                            </x-forge.card>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ================= CTA ================= --}}
    <section class="relative overflow-hidden border-t border-primary-500/10 wood-panel py-28">
        <div class="absolute inset-0 bg-grid opacity-30"></div>
        <div class="relative mx-auto max-w-3xl px-6 text-center" x-data x-reveal>
            <h2 class="font-display text-4xl font-bold uppercase tracking-wide text-white md:text-6xl">Forged in the Barn</h2>
            <p class="mx-auto mt-4 max-w-lg text-forge-steel/70">Ready to join the next chapter? Claim your spot at MBLAN26 — The Forge III.</p>
            <div class="mt-10 flex flex-wrap justify-center gap-4">
                @auth
                    <x-forge.btn href="{{ url('/dashboard') }}">Enter The Forge</x-forge.btn>
                @else
                    <x-forge.btn href="{{ route('login') }}">Login</x-forge.btn>
                    @if (Route::has('register'))
                        <x-forge.btn variant="ghost" href="{{ route('register') }}">Register</x-forge.btn>
                    @endif
                @endauth
            </div>
        </div>
    </section>

    {{-- ================= FOOTER ================= --}}
    <footer class="border-t border-primary-500/10 bg-forge-black py-10">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 md:flex-row">
            <span class="font-display text-lg font-bold uppercase tracking-widest text-white">MBLAN<span class="text-primary-400">26</span></span>
            <p class="text-xs uppercase tracking-widest text-forge-steel/50">Martin &amp; Bart LAN Party &middot; The Forge</p>
        </div>
    </footer>

    @livewireScripts
</body>

</html>
