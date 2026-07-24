<x-app-layout>
    @php
        $grouped = $schedules->groupBy(fn ($s) => $s->date ? \Illuminate\Support\Carbon::parse($s->date)->format('Y-m-d') : 'tba');
        $dates = $grouped->keys()->values();
        $firstDate = $dates->first() ?? 'tba';
    @endphp

    <div class="relative">
        <div class="pointer-events-none absolute inset-0 bg-grid opacity-30"></div>
        <div class="relative mx-auto max-w-6xl px-6 py-12">

            {{-- heading + a little pasture diorama --}}
            <x-forge.heading eyebrow="MBLAN26" class="!mb-4">Speelschema</x-forge.heading>

            <div class="relative mb-10 h-14" aria-hidden="true">
                <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-primary-500/50 to-transparent"></div>
                {{-- soft shadows --}}
                <div class="absolute bottom-0 left-2 h-1.5 w-9 -translate-x-1 rounded-[50%] bg-black/40 blur-[2px]"></div>
                <div class="absolute bottom-0 left-24 h-1.5 w-12 rounded-[50%] bg-black/40 blur-[2px]"></div>
                <div class="absolute bottom-0 right-8 h-1.5 w-7 rounded-[50%] bg-black/40 blur-[2px]"></div>
                {{-- critters standing on the line --}}
                <img src="{{ asset('images/farm/tile_0109.png') }}" alt="" class="pixel absolute bottom-1 left-0 w-11" style="animation: sprite-bob .6s steps(2,end) infinite;" />
                <img src="{{ asset('images/farm/tile_0121.png') }}" alt="" class="pixel absolute bottom-1 left-20 w-14" style="animation: float 6s ease-in-out infinite;" />
                <img src="{{ asset('images/farm/tile_0122.png') }}" alt="" class="pixel absolute bottom-1 right-6 w-8" style="animation: float 5s ease-in-out infinite;" />
            </div>

            @if ($schedules->isEmpty())
                <x-forge.card><p class="text-forge-steel/60">Er is nog geen speelschema.</p></x-forge.card>
            @else
                <div x-data="{ active: '{{ $firstDate }}' }">
                    {{-- day tabs --}}
                    <div class="mb-6 flex flex-wrap gap-2">
                        @foreach ($dates as $d)
                            <button type="button" @click="active = '{{ $d }}'"
                                :class="active === '{{ $d }}' ? 'bg-primary-500 text-forge-black' : 'metal-edge text-forge-steel hover:text-white'"
                                class="clip-corner px-4 py-2 font-pixel text-[9px] uppercase tracking-wider transition">
                                {{ $d === 'tba' ? 'Nog t.b.a.' : \Illuminate\Support\Carbon::parse($d)->translatedFormat('D d M') }}
                            </button>
                        @endforeach
                    </div>

                    @foreach ($grouped as $date => $daySchedules)
                        <div x-show="active === '{{ $date }}'" x-cloak class="space-y-6">
                            @foreach ($daySchedules as $schedule)
                                <x-forge.card>
                                    <div class="mb-4 flex items-center justify-between gap-3">
                                        <h3 class="font-display text-xl font-bold uppercase tracking-wide text-white">{{ $schedule->name }}</h3>
                                        @if ($date !== 'tba')
                                            <span class="font-pixel text-[8px] uppercase tracking-widest text-forge-steel/50">{{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('l d M Y') }}</span>
                                        @endif
                                    </div>

                                    @php
                                        $items = $schedule->games->map(fn ($game) => (object) [
                                            'type' => 'game',
                                            'name' => $game->name,
                                            'image' => $game->image,
                                            'is_tournament' => (bool) $game->pivot->is_tournament,
                                            'start' => $game->pivot->start_date,
                                            'end' => $game->pivot->end_date,
                                        ])->concat($schedule->blocks->map(fn ($block) => (object) [
                                            'type' => 'block',
                                            'name' => $block->title,
                                            'image' => null,
                                            'is_tournament' => false,
                                            'start' => $block->start_date,
                                            'end' => $block->end_date,
                                        ]))->sortBy(fn ($item) => $item->start
                                            ? \Illuminate\Support\Carbon::parse($item->start)->timestamp
                                            : PHP_INT_MAX)->values();
                                    @endphp

                                    <ul class="space-y-3">
                                        @forelse ($items as $item)
                                            <li class="flex items-center gap-4 border-t border-primary-500/10 pt-3">
                                                @if ($item->type === 'game')
                                                    <div class="h-12 w-16 shrink-0 overflow-hidden clip-corner bg-forge-graphite">
                                                        @if ($item->image)
                                                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="h-full w-full object-cover" loading="lazy" />
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="flex h-12 w-16 shrink-0 items-center justify-center clip-corner bg-forge-graphite/40">
                                                        <span class="h-6 w-px bg-primary-500/40"></span>
                                                    </div>
                                                @endif
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-display text-sm uppercase tracking-wide {{ $item->type === 'game' ? 'text-white' : 'text-forge-steel' }}">{{ $item->name }}</span>
                                                        @if ($item->is_tournament)
                                                            <span class="font-pixel text-[7px] uppercase tracking-widest text-warning-400">Toernooi</span>
                                                        @elseif ($item->type === 'block')
                                                            <span class="font-pixel text-[7px] uppercase tracking-widest text-forge-steel/40">Vrij</span>
                                                        @endif
                                                    </div>
                                                    @if ($item->start)
                                                        <p class="mt-0.5 text-xs uppercase tracking-widest text-forge-steel/60">
                                                            {{ \Illuminate\Support\Carbon::parse($item->start)->format('H:i') }}
                                                            @if ($item->end) &ndash; {{ \Illuminate\Support\Carbon::parse($item->end)->format('H:i') }} @endif
                                                        </p>
                                                    @endif
                                                </div>
                                            </li>
                                        @empty
                                            <li class="text-sm text-forge-steel/50">Nog geen games ingepland.</li>
                                        @endforelse
                                    </ul>
                                </x-forge.card>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
