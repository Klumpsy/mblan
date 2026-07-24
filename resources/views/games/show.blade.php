<x-app-layout>
    @php
        $textBlocks = collect([
            $game->text_block_one,
            $game->text_block_two,
            $game->text_block_three,
        ])->filter(fn ($b) => filled($b))->values();
    @endphp

    <div class="relative">
        <div class="pointer-events-none absolute inset-0 bg-grid opacity-30"></div>
        <div class="relative mx-auto max-w-6xl px-6 py-12">

            {{-- back to the schedule --}}
            <div class="mb-8">
                <x-forge.btn variant="ghost" :href="route('schedule')" class="!px-4 !py-2.5">
                    <span aria-hidden="true">&larr;</span> Terug naar speelschema
                </x-forge.btn>
            </div>

            {{-- hero: cover art + title --}}
            <div class="grid gap-8 md:grid-cols-2 md:items-start">
                <div class="w-full overflow-hidden clip-corner metal-edge">
                    @if ($game->image)
                        <img src="{{ asset('storage/' . $game->image) }}" alt="{{ $game->name }}"
                            class="aspect-video w-full object-cover" />
                    @else
                        <div class="flex aspect-video w-full items-center justify-center bg-forge-graphite text-forge-steel/40">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                </div>

                <div>
                    <div class="mb-3 flex items-center gap-3">
                        <x-forge.badge>MBLAN26</x-forge.badge>
                        @if ($game->year_of_release)
                            <span class="font-pixel text-[9px] uppercase tracking-[0.2em] text-forge-steel/50">{{ $game->year_of_release }}</span>
                        @endif
                    </div>

                    <h1 class="font-display text-3xl font-bold uppercase tracking-wide text-white md:text-5xl">{{ $game->name }}</h1>

                    @if ($game->short_description)
                        <div class="prose prose-invert mt-4 max-w-none prose-p:text-forge-steel/80 prose-a:text-primary-400 prose-strong:text-white prose-li:text-forge-steel/80">
                            {!! $game->short_description !!}
                        </div>
                    @endif

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <livewire:game.like :game="$game" />
                        @if ($game->link_to_website)
                            <x-forge.btn variant="anvil" :href="$game->link_to_website" target="_blank" rel="noopener noreferrer" class="!px-4 !py-2.5">
                                Officiële website
                            </x-forge.btn>
                        @endif
                    </div>
                </div>
            </div>

            {{-- trailer, framed like a screen on the workbench --}}
            @if ($game->link_to_youtube)
                <div class="mt-12">
                    <div class="mb-4">
                        <span class="font-pixel text-[9px] uppercase tracking-[0.2em] text-primary-400 md:text-[10px]">Trailer</span>
                    </div>
                    <div class="metal-edge p-2">
                        <x-video :link="$game->link_to_youtube" class="!rounded-none clip-corner" />
                    </div>
                </div>
            @endif

            {{-- longer story blocks --}}
            @if ($textBlocks->isNotEmpty())
                <div class="mt-12 space-y-6">
                    @foreach ($textBlocks as $block)
                        <x-forge.card>
                            <div class="prose prose-invert max-w-none prose-headings:font-display prose-headings:uppercase prose-headings:tracking-wide prose-a:text-primary-400 prose-strong:text-white prose-p:text-forge-steel/80 prose-li:text-forge-steel/80">
                                {!! $block !!}
                            </div>
                        </x-forge.card>
                    @endforeach
                </div>
            @endif

            {{-- how to install --}}
            @if ($game->installation_instructions)
                <div class="mt-12">
                    <div class="mb-4">
                        <span class="font-pixel text-[9px] uppercase tracking-[0.2em] text-primary-400 md:text-[10px]">Installatie</span>
                    </div>
                    <x-forge.card>
                        <div class="prose prose-invert max-w-none prose-headings:font-display prose-headings:uppercase prose-headings:tracking-wide prose-a:text-primary-400 prose-strong:text-white prose-p:text-forge-steel/80 prose-li:text-forge-steel/80">
                            {!! $game->installation_instructions !!}
                        </div>
                    </x-forge.card>
                </div>
            @endif

            {{-- tournaments running on this game --}}
            @if ($game->tournaments->isNotEmpty())
                <div class="mt-12">
                    <div class="mb-4">
                        <span class="font-pixel text-[9px] uppercase tracking-[0.2em] text-primary-400 md:text-[10px]">Toernooien</span>
                    </div>
                    <x-forge.card>
                        <ul class="space-y-3">
                            @foreach ($game->tournaments as $tournament)
                                <li class="flex items-center gap-4 border-t border-primary-500/10 pt-3 first:border-t-0 first:pt-0">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-display text-sm uppercase tracking-wide text-white">{{ $tournament->name }}</span>
                                            @if ($tournament->is_active)
                                                <span class="font-pixel text-[7px] uppercase tracking-widest text-warning-400">Actief</span>
                                            @elseif ($tournament->concluded)
                                                <span class="font-pixel text-[7px] uppercase tracking-widest text-forge-steel/40">Afgelopen</span>
                                            @endif
                                        </div>
                                        @if ($tournament->schedule?->date)
                                            <p class="mt-0.5 text-xs uppercase tracking-widest text-forge-steel/60">
                                                {{ \Illuminate\Support\Carbon::parse($tournament->schedule->date)->translatedFormat('D d M') }}
                                                @if ($tournament->time_start) &middot; {{ \Illuminate\Support\Carbon::parse($tournament->time_start)->format('H:i') }} @endif
                                            </p>
                                        @endif
                                    </div>
                                    <a href="{{ route('tournaments') }}" class="font-pixel text-[8px] uppercase tracking-widest text-primary-300 transition hover:text-white">
                                        Klassement &rarr;
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </x-forge.card>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
