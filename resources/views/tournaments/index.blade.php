<x-app-layout>
    <div class="relative"
        x-data="{ tab: (new URLSearchParams(window.location.search)).get('tab') === 'stats' ? 'stats' : 'toernooien' }">
        <div class="pointer-events-none absolute inset-0 bg-grid opacity-30"></div>
        <div class="relative mx-auto max-w-6xl px-6 py-12">
            <x-forge.heading eyebrow="MBLAN26">Leaderboard</x-forge.heading>

            {{-- ===== Tabs: tournaments vs. user stats ===== --}}
            <div class="mb-10 flex flex-wrap gap-3">
                <button type="button" @click="tab = 'toernooien'"
                    class="clip-corner px-5 py-2.5 font-pixel text-[9px] uppercase tracking-[0.15em] transition"
                    :class="tab === 'toernooien' ? 'bg-primary-500 text-forge-black' : 'metal-edge text-forge-steel hover:text-white'">
                    Toernooien
                </button>
                <button type="button" @click="tab = 'stats'"
                    class="clip-corner px-5 py-2.5 font-pixel text-[9px] uppercase tracking-[0.15em] transition"
                    :class="tab === 'stats' ? 'bg-primary-500 text-forge-black' : 'metal-edge text-forge-steel hover:text-white'">
                    User stats
                </button>
            </div>

            {{-- ============================ TOERNOOIEN ============================ --}}
            <div x-show="tab === 'toernooien'" x-cloak>
                {{-- ===== The Arti Game (hardcoded first tournament) ===== --}}
                <div class="relative mb-14" x-data x-reveal>
                    {{-- Arti + a chicken hang around this card --}}
                    <img src="{{ asset('images/farm/arti.png') }}" alt="" aria-hidden="true"
                        class="pixel pointer-events-none absolute -top-6 right-4 z-10 w-12" style="animation: float 5s ease-in-out infinite;" />
                    <img src="{{ asset('images/farm/tile_0122.png') }}" alt="" aria-hidden="true"
                        class="pixel pointer-events-none absolute -top-5 left-6 z-10 w-8" style="animation: float 6s ease-in-out infinite;" />

                    <x-forge.card class="overflow-hidden">
                        <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                            <div>
                                <p class="font-pixel text-[9px] uppercase tracking-[0.2em] text-primary-400">Editie-klassieker</p>
                                <h3 class="mt-1 font-display text-2xl font-bold uppercase tracking-wide text-white">The Arti Game</h3>
                                <p class="mt-1 text-xs uppercase tracking-widest text-forge-steel/60">Bereik de schuur, hoe minder keer gepakt, hoe hoger</p>
                            </div>
                            <a href="{{ url('/') }}" class="btn-wood clip-corner text-[10px]">Speel opnieuw</a>
                        </div>

                        @if ($artiLeaderboard->isEmpty())
                            <p class="text-sm text-forge-steel/60">Nog niemand heeft de schuur bereikt. Wees de eerste!</p>
                        @else
                            <ul class="divide-y divide-primary-500/10">
                                @foreach ($artiLeaderboard as $i => $row)
                                    @php $rank = $i + 1; @endphp
                                    <li @class([
                                        'flex items-center gap-4 py-3',
                                        'bg-primary-500/10' => $row->id === auth()->id(),
                                    ])>
                                        <span class="w-8 shrink-0 text-center font-pixel text-[10px] {{ $rank === 1 ? 'text-amber-300' : ($rank === 2 ? 'text-forge-steel' : ($rank === 3 ? 'text-amber-600' : 'text-primary-300')) }}">
                                            {{ $rank }}
                                        </span>
                                        <span class="min-w-0 flex-1 truncate text-sm text-forge-steel">
                                            {{ $row->name }}
                                            @if ($row->id === auth()->id())
                                                <span class="ml-1 font-pixel text-[7px] uppercase tracking-widest text-primary-400">jij</span>
                                            @endif
                                        </span>
                                        <span class="flex shrink-0 items-baseline gap-3">
                                            <span class="font-display text-sm text-primary-300">
                                                {{ $row->barn_catches }} <span class="text-[10px] uppercase tracking-wider text-forge-steel/50">x gepakt</span>
                                            </span>
                                            @if ($row->barn_time_ms)
                                                <span class="font-pixel text-[9px] uppercase tracking-wider text-forge-steel/60">
                                                    {{ intdiv((int) floor($row->barn_time_ms / 1000), 60) }}:{{ str_pad((int) floor($row->barn_time_ms / 1000) % 60, 2, '0', STR_PAD_LEFT) }}
                                                </span>
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @auth
                            @if (!auth()->user()->barn_completed)
                                <p class="mt-5 border-t border-primary-500/10 pt-4 font-pixel text-[8px] uppercase tracking-widest text-forge-steel/50">
                                    Jij staat nog niet op de ladder, bereik de schuur op de startpagina
                                </p>
                            @endif
                        @endauth
                    </x-forge.card>
                </div>

                @php
                    $live = $tournaments->filter(fn ($t) => $t->is_active);
                    $upcoming = $tournaments->filter(fn ($t) => !$t->is_active && $t->hasYetToStart());
                    $past = $tournaments->filter(fn ($t) => !$t->is_active && !$t->hasYetToStart());
                @endphp

                @if ($tournaments->isNotEmpty())
                    @if ($live->isNotEmpty())
                        <div class="mb-6 flex items-center gap-3">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary-400 opacity-75"></span>
                                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-primary-400"></span>
                            </span>
                            <span class="font-display text-sm uppercase tracking-widest text-primary-300">Nu Live</span>
                        </div>
                        <div class="mb-14 grid gap-6 lg:grid-cols-2">
                            @foreach ($live as $tournament)
                                <div x-data x-reveal>
                                    <livewire:tournament.ladder :tournament="$tournament" :key="'live-' . $tournament->id" />
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($upcoming->isNotEmpty())
                        <h3 class="mb-6 font-display text-lg uppercase tracking-widest text-forge-steel/70">Aankomende toernooien</h3>
                        <div class="mb-14 grid gap-6 lg:grid-cols-2">
                            @foreach ($upcoming as $tournament)
                                <div x-data x-reveal>
                                    <livewire:tournament.ladder :tournament="$tournament" :key="'up-' . $tournament->id" />
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($past->isNotEmpty())
                        <h3 class="mb-6 font-display text-lg uppercase tracking-widest text-forge-steel/70">Afgelopen toernooien</h3>
                        <div class="grid gap-6 lg:grid-cols-2">
                            @foreach ($past as $tournament)
                                <div x-data x-reveal>
                                    <livewire:tournament.ladder :tournament="$tournament" :key="'past-' . $tournament->id" />
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            {{-- ============================ USER STATS ============================ --}}
            <div x-show="tab === 'stats'" x-cloak>
                <p class="mb-8 max-w-2xl text-sm text-forge-steel/70">
                    Ranglijsten op basis van alles wat we bijhouden: biertjes, foto's, reacties en achievements.
                </p>

                <div class="grid gap-6 lg:grid-cols-2">
                    @foreach ($statBrackets as $bracket)
                        <div x-data x-reveal>
                            <x-forge.card class="h-full overflow-hidden">
                                <div class="mb-4 flex items-center gap-3">
                                    <img src="{{ asset('images/farm/'.$bracket['icon'].'.png') }}" alt="" aria-hidden="true" class="pixel h-8 w-8 shrink-0" />
                                    <div>
                                        <h3 class="font-display text-lg font-bold uppercase tracking-wide text-white">{{ $bracket['title'] }}</h3>
                                        @if ($bracket['caption'])
                                            <p class="mt-0.5 text-[11px] leading-tight text-forge-steel/50">{{ $bracket['caption'] }}</p>
                                        @endif
                                    </div>
                                </div>

                                @if ($bracket['rows']->isEmpty())
                                    <p class="py-4 text-sm text-forge-steel/50">Nog geen data. Wees de eerste die hier verschijnt.</p>
                                @else
                                    <ul class="divide-y divide-primary-500/10">
                                        @foreach ($bracket['rows'] as $i => $row)
                                            @php $rank = $i + 1; $u = $row['user']; @endphp
                                            <li @class([
                                                'flex items-center gap-3 py-2.5',
                                                'bg-primary-500/10' => $u->id === auth()->id(),
                                            ])>
                                                <span class="w-6 shrink-0 text-center font-pixel text-[10px] {{ $rank === 1 ? 'text-amber-300' : ($rank === 2 ? 'text-forge-steel' : ($rank === 3 ? 'text-amber-600' : 'text-primary-300')) }}">
                                                    {{ $rank }}
                                                </span>
                                                <img src="{{ $u->profile_photo_url }}" alt="" class="h-7 w-7 shrink-0 rounded-full object-cover ring-1 ring-primary-500/20" />
                                                <span class="min-w-0 flex-1 truncate text-sm text-forge-steel">
                                                    {{ $u->name }}
                                                    @if ($u->id === auth()->id())
                                                        <span class="ml-1 font-pixel text-[7px] uppercase tracking-widest text-primary-400">jij</span>
                                                    @endif
                                                </span>
                                                <span class="shrink-0 font-display text-sm text-primary-300">
                                                    {{ $row['value'] }}
                                                    <span class="text-[10px] uppercase tracking-wider text-forge-steel/50">{{ $bracket['unit'] }}</span>
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </x-forge.card>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
