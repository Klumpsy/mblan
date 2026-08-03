<div class="mx-auto max-w-7xl px-4 sm:px-0">
    <x-forge.card>
        <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="font-pixel text-[9px] uppercase tracking-[0.2em] text-primary-400">{{ \App\Models\Edition::currentName() }}</p>
                <h3 class="mt-1 font-display text-2xl font-bold uppercase tracking-wide text-white">Achievements</h3>
                <p class="mt-1 text-xs uppercase tracking-widest text-forge-steel/60">
                    Verdien ze door dingen te doen op de site en in Discord
                </p>
            </div>
            <span class="font-display text-lg text-primary-300">{{ $unlockedCount }}<span class="text-forge-steel/50">/{{ $total }}</span></span>
        </div>

        @if ($cards->isEmpty())
            <p class="text-sm text-forge-steel/60">Er zijn nog geen achievements ingesteld.</p>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($cards as $card)
                    @php $a = $card['achievement']; @endphp
                    <div @class([
                        'metal-edge clip-corner flex gap-3 p-4 transition',
                        'opacity-100' => $card['unlocked'],
                        'opacity-60' => ! $card['unlocked'],
                    ])>
                        <div class="relative shrink-0">
                            <img src="{{ asset($a->icon_path ?: 'images/farm/tile_0000.png') }}" alt="" @class([
                                'pixel h-12 w-12',
                                'grayscale' => ! $card['unlocked'],
                            ]) style="{{ $card['unlocked'] ? 'filter: drop-shadow(0 0 8px '.($a->color ?: '#65e59a').'88);' : '' }}" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <h4 class="truncate font-display text-sm font-bold uppercase tracking-wide {{ $card['unlocked'] ? 'text-white' : 'text-forge-steel/70' }}">
                                    {{ $a->name }}
                                </h4>
                                @if ($card['unlocked'])
                                    <span class="shrink-0 font-pixel text-[7px] uppercase tracking-widest" style="color: {{ $a->color ?: '#65e59a' }};">behaald</span>
                                @endif
                            </div>
                            @if ($a->description)
                                <p class="mt-0.5 text-[11px] leading-tight text-forge-steel/60">{{ $a->description }}</p>
                            @endif

                            @if ($card['unlocked'])
                                @if ($card['achieved_at'])
                                    <p class="mt-2 font-pixel text-[7px] uppercase tracking-widest text-forge-steel/40">
                                        {{ \Illuminate\Support\Carbon::parse($card['achieved_at'])->translatedFormat('d M Y') }}
                                    </p>
                                @endif
                            @elseif ($a->type === 'manual')
                                <p class="mt-2 font-pixel text-[7px] uppercase tracking-widest text-forge-steel/40">Toegekend door een beheerder</p>
                            @else
                                <div class="mt-2">
                                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-forge-black/60">
                                        <div class="h-full rounded-full bg-primary-500/70" style="width: {{ $card['pct'] }}%;"></div>
                                    </div>
                                    <p class="mt-1 font-pixel text-[7px] uppercase tracking-widest text-forge-steel/50">
                                        {{ $card['progress'] }} / {{ $card['threshold'] }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-forge.card>
</div>
