<x-app-layout>
    {{-- Recolor the whole page in this edition's palette (overrides the head style). --}}
    <x-edition-theme :edition="$edition" />

    <div class="relative">
        <div class="pointer-events-none absolute inset-0 bg-grid opacity-30"></div>
        <div class="relative mx-auto max-w-6xl px-6 py-12">

            {{-- Hero --}}
            <div class="mb-12 text-center">
                @if ($edition->logo_path)
                    <img src="{{ asset('storage/' . $edition->logo_path) }}" alt="{{ $edition->name }}"
                        class="mx-auto mb-6 h-24 w-auto" />
                @endif
                <x-forge.heading :eyebrow="'Editie ' . $edition->year" class="!mb-2 text-center">{{ $edition->name }}</x-forge.heading>
                @if ($edition->tagline)
                    <p class="text-forge-steel/70">{{ $edition->tagline }}</p>
                @endif

                <div class="mx-auto mt-8 grid max-w-xl grid-cols-3 gap-6">
                    <x-forge.stat :value="$participantCount" label="Deelnemers" />
                    <x-forge.stat :value="$tournaments->count()" label="Toernooien" />
                    <x-forge.stat :value="$photos->count()" label="Foto's" />
                </div>
            </div>

            @if ($edition->hero_image_path)
                <div class="frame-wood mb-12 overflow-hidden">
                    <img src="{{ asset('storage/' . $edition->hero_image_path) }}" alt="{{ $edition->name }}"
                        class="w-full object-cover" />
                </div>
            @endif

            {{-- Deelnemers: wie waren erbij --}}
            @if ($participants->isNotEmpty())
                <x-forge.heading eyebrow="Wie waren erbij" class="!mb-6">Deelnemers</x-forge.heading>
                <div class="mb-12 flex flex-wrap gap-3">
                    @foreach ($participants as $participant)
                        <div class="clip-corner flex items-center gap-2.5 bg-forge-graphite/70 py-2 pl-2.5 pr-4">
                            <img src="{{ $participant->profile_photo_url }}" alt="{{ $participant->name }}"
                                class="h-8 w-8 rounded-full border border-primary-500/30 object-cover" />
                            <span class="font-pixel text-[9px] uppercase tracking-wider text-forge-steel">{{ $participant->name }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Speelschema: wat speelden we wanneer --}}
            @if ($schedules->isNotEmpty())
                <x-forge.heading eyebrow="Wat speelden we" class="!mb-6">Speelschema</x-forge.heading>
                <div class="mb-12 grid gap-6 {{ $schedules->count() >= 3 ? 'lg:grid-cols-3' : ($schedules->count() === 2 ? 'lg:grid-cols-2' : '') }}">
                    @foreach ($schedules as $day)
                        @php
                            // Games en blokken door elkaar, gesorteerd op starttijd.
                            $dayItems = $day->games
                                ->map(fn ($game) => [
                                    'start' => $game->pivot->start_date,
                                    'end' => $game->pivot->end_date,
                                    'title' => $game->name,
                                    'is_tournament' => (bool) $game->pivot->is_tournament,
                                    'is_block' => false,
                                ])
                                ->concat($day->blocks->map(fn ($block) => [
                                    'start' => $block->start_date,
                                    'end' => $block->end_date,
                                    'title' => $block->title,
                                    'is_tournament' => false,
                                    'is_block' => true,
                                ]))
                                ->sortBy('start')
                                ->values();
                        @endphp
                        <x-forge.card>
                            <div class="flex items-baseline justify-between gap-2">
                                <h3 class="font-display text-lg font-bold uppercase tracking-wide text-white">{{ $day->name }}</h3>
                                @if ($day->date)
                                    <span class="font-pixel text-[8px] uppercase tracking-widest text-forge-steel/50">{{ \Illuminate\Support\Carbon::parse($day->date)->translatedFormat('d M Y') }}</span>
                                @endif
                            </div>

                            @if ($dayItems->isEmpty())
                                <p class="mt-4 text-sm text-forge-steel/60">Geen programma bekend.</p>
                            @else
                                <ul class="mt-4 space-y-2">
                                    @foreach ($dayItems as $item)
                                        <li class="flex items-baseline gap-3 text-sm">
                                            <span class="w-24 shrink-0 font-pixel text-[8px] uppercase tracking-widest text-forge-steel/50">
                                                @if ($item['start'])
                                                    {{ \Illuminate\Support\Carbon::parse($item['start'])->format('H:i') }}@if ($item['end'])&ndash;{{ \Illuminate\Support\Carbon::parse($item['end'])->format('H:i') }}@endif
                                                @endif
                                            </span>
                                            <span class="{{ $item['is_block'] ? 'text-forge-steel/70' : 'text-forge-steel' }}">
                                                {{ $item['title'] }}
                                                @if ($item['is_tournament'])
                                                    <span class="ml-1 font-pixel text-[8px] uppercase tracking-widest text-primary-300">Toernooi</span>
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </x-forge.card>
                    @endforeach
                </div>
            @endif

            {{-- Toernooien --}}
            @if ($tournaments->isNotEmpty())
                <x-forge.heading eyebrow="Uitslagen" class="!mb-6">Toernooien</x-forge.heading>
                <div class="mb-12 grid gap-6 md:grid-cols-2">
                    @foreach ($tournaments as $tournament)
                        @php($rows = $tournament->getLeaderboard())
                        <x-forge.card>
                            <h3 class="font-display text-lg font-bold uppercase tracking-wide text-white">{{ $tournament->name }}</h3>
                            @if ($tournament->game)
                                <p class="mt-1 font-pixel text-[8px] uppercase tracking-widest text-forge-steel/50">{{ $tournament->game->name }}</p>
                            @endif

                            @if ($rows->isEmpty())
                                <p class="mt-4 text-sm text-forge-steel/60">Geen uitslagen bekend.</p>
                            @else
                                <ol class="mt-4 space-y-2">
                                    @foreach ($rows->take(3) as $row)
                                        <li class="flex items-center justify-between text-sm">
                                            <span class="text-forge-steel">
                                                <span class="mr-2 inline-block w-5 text-right font-display font-bold {{ $row['ranking'] === 1 ? 'text-primary-300' : 'text-forge-steel/50' }}">{{ $row['ranking'] }}</span>
                                                {{ $row['name'] }}
                                                @if ($row['team_name'])
                                                    <span class="text-forge-steel/50">({{ $row['team_name'] }})</span>
                                                @endif
                                            </span>
                                            <span class="font-display text-primary-300">{{ $row['score'] }}</span>
                                        </li>
                                    @endforeach
                                </ol>
                                @if ($rows->count() > 3)
                                    <details class="mt-3">
                                        <summary class="cursor-pointer font-pixel text-[8px] uppercase tracking-widest text-primary-300">Volledige uitslag</summary>
                                        <ol class="mt-2 space-y-1">
                                            @foreach ($rows->slice(3) as $row)
                                                <li class="flex items-center justify-between text-sm text-forge-steel/70">
                                                    <span>
                                                        <span class="mr-2 inline-block w-5 text-right text-forge-steel/40">{{ $row['ranking'] }}</span>
                                                        {{ $row['name'] }}
                                                    </span>
                                                    <span>{{ $row['score'] }}</span>
                                                </li>
                                            @endforeach
                                        </ol>
                                    </details>
                                @endif
                            @endif
                        </x-forge.card>
                    @endforeach
                </div>
            @endif

            {{-- Arti-spel --}}
            @if ($gameResults->isNotEmpty())
                <x-forge.heading eyebrow="Entreespel" class="!mb-6">Editie-klassieker</x-forge.heading>
                <x-forge.card class="mb-12">
                    <ol class="space-y-2">
                        @foreach ($gameResults as $result)
                            <li class="flex items-center justify-between text-sm">
                                <span class="text-forge-steel">
                                    <span class="mr-2 inline-block w-5 text-right font-display font-bold {{ $loop->first ? 'text-primary-300' : 'text-forge-steel/50' }}">{{ $loop->iteration }}</span>
                                    {{ $result->user->name }}
                                </span>
                                <span class="font-pixel text-[9px] uppercase tracking-widest text-forge-steel/70">
                                    @if ($result->score !== null)
                                        {{ $result->score }} punten
                                    @else
                                        {{ $result->catches }}x gepakt
                                        @if ($result->time_ms)
                                            &middot; {{ intdiv(intdiv($result->time_ms, 1000), 60) }}:{{ str_pad((string) (intdiv($result->time_ms, 1000) % 60), 2, '0', STR_PAD_LEFT) }}
                                        @endif
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </x-forge.card>
            @endif

            {{-- Tijdlijn --}}
            @if ($photos->isNotEmpty())
                <x-forge.heading eyebrow="Herinneringen" class="!mb-6">Tijdlijn</x-forge.heading>
                <div class="mb-12 -mx-6 overflow-x-auto px-6 pb-4">
                    <div class="flex snap-x snap-mandatory gap-4">
                        @foreach ($photos as $photo)
                            <figure class="frame-wood w-72 flex-none snap-start overflow-hidden">
                                <img src="{{ asset('storage/' . $photo->image) }}" alt=""
                                    class="aspect-square w-full object-cover" loading="lazy" />
                                <figcaption class="p-4">
                                    <p class="line-clamp-3 text-sm text-forge-steel/80">{{ $photo->story }}</p>
                                    <p class="mt-2 font-pixel text-[8px] uppercase tracking-widest text-primary-400/70">{{ $photo->user?->name }}</p>
                                </figcaption>
                            </figure>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Nieuws --}}
            @if ($news->isNotEmpty())
                <x-forge.heading eyebrow="Terugleesbaar" class="!mb-6">Nieuws</x-forge.heading>
                <div class="-mx-6 overflow-x-auto px-6 pb-4">
                    <div class="flex snap-x snap-mandatory gap-4">
                        @foreach ($news as $item)
                            <a href="{{ route('news.show', $item) }}"
                                class="group frame-wood w-72 flex-none snap-start p-5 transition hover:-translate-y-0.5">
                                @if ($item->published_at)
                                    <p class="font-pixel text-[8px] uppercase tracking-widest text-forge-steel/50">{{ $item->published_at->translatedFormat('d M Y') }}</p>
                                @endif
                                <h3 class="mt-2 font-display text-base font-bold uppercase tracking-wide text-white transition group-hover:text-primary-300">{{ $item->title }}</h3>
                                <p class="mt-2 line-clamp-3 text-sm text-forge-steel/70">
                                    {{ $item->preview_text ?: \Illuminate\Support\Str::limit(strip_tags($item->content), 120) }}
                                </p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
