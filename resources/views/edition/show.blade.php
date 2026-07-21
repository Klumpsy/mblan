<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center md:flex-row md:justify-between w-full">

            <h1 class="font-display text-xl font-bold uppercase tracking-wide text-white leading-tight md:mb-0">
                {{ $edition->name }}
            </h1>

            <div class="flex items-center w-full md:w-auto flex-wrap mt-2 justify-end gap-2">
                <span
                    class="font-display text-xs uppercase tracking-widest metal-edge clip-corner px-3 py-1.5 text-forge-steel/80">
                    {{ $edition->year }}
                </span>

                @if ($edition->year >= idate('Y') && !auth()->user()->hasSignedUpFor($edition))
                    <a href="{{ route('editions.signup', $edition->slug) }}"
                        class="whitespace-nowrap font-display text-xs uppercase tracking-widest border border-primary-500/30 bg-primary-500/15 text-primary-300 px-3 py-1.5 clip-corner transition-colors hover:bg-primary-500/25 hover:text-white cursor-pointer">
                        Sign up for {{ $edition->name }}
                    </a>
                @elseif(auth()->user()->hasSignedUpFor($edition) && auth()->user()->can('accessWithConfirmedSignup', $edition))
                    <span
                        class="font-display text-xs uppercase tracking-widest border border-primary-500/40 bg-primary-500/20 text-primary-200 px-3 py-1.5 clip-corner">
                        Participating
                    </span>
                @elseif($edition->year < idate('Y'))
                    <span class="font-display text-xs uppercase tracking-widest border border-danger-500/30 bg-danger-500/10 text-danger-400 px-3 py-1.5 clip-corner">
                        Closed
                    </span>
                @else
                    <span
                        class="font-display text-xs uppercase tracking-widest metal-edge clip-corner px-3 py-1.5 text-forge-steel/80">
                        Signup pending
                    </span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-6">
            <div class="flex justify-between mb-8">
                <x-forge.btn href="{{ route('editions') }}" variant="ghost" class="!px-5 !py-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Editions
                </x-forge.btn>
            </div>

            <div x-data x-reveal>
                <x-forge.card class="mb-8">
                    <div class="flex flex-col md:flex-row md:items-center">
                        <div class="flex-grow">
                            <div class="mb-3 flex items-center gap-3">
                                <span class="h-3 w-3 rounded-full"
                                    style="background: {{ $edition->color ?? '#65E59A' }}; box-shadow: 0 0 12px {{ $edition->color ?? '#65E59A' }};"></span>
                                <span class="font-display text-xs uppercase tracking-widest text-forge-steel/60">{{ $edition->year }}</span>
                            </div>
                            <h2 class="mb-3 font-display text-3xl font-bold uppercase tracking-wide text-white">{{ $edition->name }}</h2>
                            <div class="text-forge-steel/80">{!! $edition->description !!}</div>
                            <div class="mt-5 flex items-center justify-between">
                                <div class="flex items-center text-sm uppercase tracking-widest text-forge-steel/70">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-400" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Participants: <span class="ml-1 font-display text-primary-300">{{ $edition->confirmedSignups->count() }}</span>
                                </div>
                                @if ($edition->year >= idate('Y') && auth()->user()->hasSignedUpFor($edition))
                                    <x-edition-signout-button :edition="$edition" />
                                @endif
                            </div>
                        </div>
                    </div>
                </x-forge.card>
            </div>

            @can('accessWithConfirmedSignup', $edition)
                <div x-data="carousel()" x-init="init()" class="relative w-full overflow-x-hidden mb-8">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="h-px w-10 bg-primary-500"></span>
                        <h2 class="font-display text-sm uppercase tracking-[0.3em] text-primary-400">
                            Your fellow gamers for this edition
                        </h2>
                    </div>
                    <div x-ref="scrollContainer" class="flex space-x-6 whitespace-nowrap overflow-x-auto py-3 no-scrollbar"
                        style="scroll-behavior: smooth;">
                        @foreach ($edition->confirmedSignups as $signup)
                            <x-user-badge-info-modal :user="$signup->user" class="inline-block w-16 h-16" />
                        @endforeach

                    </div>
                </div>
            @endcan

            <livewire:edition.schedule :edition="$edition" />

            @if ($edition->hasGames())
                <div x-data x-reveal>
                    <x-forge.card class="mt-8 overflow-hidden !p-0">
                        <h3 class="font-display text-lg font-bold uppercase tracking-wide text-white p-5 border-b border-primary-500/15">
                            Featured Games
                        </h3>
                        <div class="p-5 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            @foreach ($featuredGames as $game)
                                <a href="{{ route('games.show', $game) }}" class="group">
                                    <div class="aspect-w-1 aspect-h-1 clip-corner metal-edge overflow-hidden">
                                        <img src="{{ asset('storage/' . $game->image) }}" alt="{{ $game->name }}"
                                            class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
                                    </div>
                                    <div class="mt-2 text-center">
                                        <span class="font-display text-sm uppercase tracking-wide text-white transition-colors group-hover:text-primary-300">{{ $game->name }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </x-forge.card>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
