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

                                    <ul class="space-y-3">
                                        @forelse ($schedule->games as $game)
                                            <li class="flex items-center gap-4 border-t border-primary-500/10 pt-3">
                                                <div class="h-12 w-16 shrink-0 overflow-hidden clip-corner bg-forge-graphite">
                                                    @if ($game->image)
                                                        <img src="{{ asset('storage/' . $game->image) }}" alt="{{ $game->name }}" class="h-full w-full object-cover" loading="lazy" />
                                                    @endif
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-display text-sm uppercase tracking-wide text-white">{{ $game->name }}</span>
                                                        @if ($game->pivot->is_tournament)
                                                            <span class="font-pixel text-[7px] uppercase tracking-widest text-warning-400">Toernooi</span>
                                                        @endif
                                                    </div>
                                                    @if ($game->pivot->start_date)
                                                        <p class="mt-0.5 text-xs uppercase tracking-widest text-forge-steel/60">
                                                            {{ \Illuminate\Support\Carbon::parse($game->pivot->start_date)->format('H:i') }}
                                                            @if ($game->pivot->end_date) &ndash; {{ \Illuminate\Support\Carbon::parse($game->pivot->end_date)->format('H:i') }} @endif
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
