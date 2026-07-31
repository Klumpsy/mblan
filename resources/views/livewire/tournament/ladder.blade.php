<div wire:poll.{{ $this->pollInterval() }} class="clip-corner metal-edge overflow-hidden">
    {{-- Header --}}
    <div class="relative border-b border-primary-500/15 bg-forge-graphite/60 p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="font-display text-2xl font-bold uppercase tracking-wide text-white">{{ $t->name }}</h3>
                <p class="mt-1 text-xs uppercase tracking-widest text-primary-400/80">
                    {{ $t->game?->name }} &middot; {{ $scoreLabel }}
                    @if (!$t->higher_is_better) &middot; laagste wint @endif
                </p>
            </div>
            @if ($t->is_active)
                <span class="inline-flex items-center gap-2 font-display text-xs uppercase tracking-widest text-primary-300">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-primary-400"></span>
                    </span>
                    Live
                </span>
            @elseif ($t->concluded)
                <span class="font-display text-xs uppercase tracking-widest text-forge-steel/50">Afgerond</span>
            @endif
        </div>

        {{-- Sign-up: reserve a spot to play. Closes once the tournament is concluded. --}}
        <div class="mt-5 border-t border-primary-500/10 pt-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <span class="font-pixel text-[9px] uppercase tracking-[0.2em] text-forge-steel/60">
                    {{ $registrationCount }} {{ $registrationCount === 1 ? 'aanmelding' : 'aanmeldingen' }}
                </span>

                @if (! $t->concluded)
                    <button type="button" wire:click="toggleRegister" wire:loading.attr="disabled"
                        @class([
                            'clip-corner px-4 py-2 font-display text-xs uppercase tracking-widest transition disabled:opacity-50',
                            'metal-edge text-forge-steel hover:text-white' => $isRegistered,
                            'btn-wood' => ! $isRegistered,
                        ])>
                        {{ $isRegistered ? 'Afmelden' : 'Meld je aan' }}
                    </button>
                @else
                    <span class="font-pixel text-[8px] uppercase tracking-widest text-forge-steel/40">Aanmelden gesloten</span>
                @endif
            </div>

            {{-- Wie hebben zich ingeschreven --}}
            @if ($registrationCount > 0)
                @php $shown = $registrants->take(18); @endphp
                <div class="mt-4 flex flex-wrap gap-x-4 gap-y-3">
                    @foreach ($shown as $registrant)
                        <div class="flex w-14 flex-col items-center gap-1.5 text-center">
                            <img src="{{ $registrant->profile_photo_url }}" alt="{{ $registrant->name }}"
                                class="h-10 w-10 rounded-full object-cover ring-2 {{ $registrant->id === auth()->id() ? 'ring-primary-400' : 'ring-forge-graphite' }}" />
                            <span class="w-full truncate text-[10px] leading-tight text-forge-steel/80">
                                {{ $registrant->id === auth()->id() ? 'Jij' : \Illuminate\Support\Str::of($registrant->name)->explode(' ')->first() }}
                            </span>
                        </div>
                    @endforeach
                    @if ($registrationCount > $shown->count())
                        <div class="flex w-14 flex-col items-center gap-1.5 text-center">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-forge-graphite ring-2 ring-forge-graphite font-display text-[11px] text-forge-steel/80">
                                +{{ $registrationCount - $shown->count() }}
                            </span>
                            <span class="text-[10px] leading-tight text-forge-steel/50">meer</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if ($rows->isEmpty())
        <div class="p-10 text-center text-sm text-forge-steel/50">Nog geen deelnemers.</div>
    @else
        {{-- Podium (top 3) --}}
        @if ($podium->count() >= 2)
            <div class="grid grid-cols-3 gap-3 bg-forge-forest/30 p-6">
                @php
                    // Visual arrangement stays 2-1-3. Every step is one RANK:
                    // tied players stand together on the same step, with all
                    // their names stacked above it.
                    $order = [1 => 'order-1', 0 => 'order-2', 2 => 'order-3'];
                    $heights = [1 => 'h-28', 2 => 'h-20', 3 => 'h-16'];
                    $medals = [1 => 'text-amber-300', 2 => 'text-forge-steel', 3 => 'text-amber-600'];
                @endphp
                @foreach ($podium->values() as $i => $group)
                    @php $rank = $group->first()['ranking']; @endphp
                    <div class="flex flex-col items-center justify-end {{ $order[$i] ?? 'order-2' }}">
                        <div class="mb-2 text-center">
                            @foreach ($group as $row)
                                <div class="font-display text-sm font-bold uppercase tracking-wide text-white truncate max-w-[9rem]">{{ $row['name'] }}</div>
                            @endforeach
                            <div class="font-display text-lg {{ $medals[$rank] ?? 'text-primary-300' }}">{{ $group->first()['score'] }}</div>
                        </div>
                        <div class="flex w-full items-end justify-center {{ $heights[$rank] ?? 'h-16' }} clip-corner bg-gradient-to-t from-primary-500/10 to-primary-500/40 border border-primary-500/30 transition-all duration-700">
                            <span class="pb-2 font-display text-2xl font-bold {{ $medals[$rank] ?? 'text-primary-300' }} text-glow">{{ $rank }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Ranked list with animated score bars (scrolls tidily for large fields) --}}
        <ul class="max-h-96 divide-y divide-primary-500/10 overflow-y-auto">
            @foreach (($podium->count() >= 2 ? $rest : $rows) as $row)
                @php $pct = max(4, min(100, round(($row['score'] / $topScore) * 100))); @endphp
                <li @class([
                    'relative flex items-center gap-4 px-6 py-3',
                    'bg-primary-500/10' => auth()->check() && $row['name'] === auth()->user()->name,
                ])>
                    <span class="w-8 shrink-0 font-display text-sm {{ $row['ranking'] === 1 ? 'text-amber-300' : ($row['ranking'] === 2 ? 'text-forge-steel' : ($row['ranking'] === 3 ? 'text-amber-600' : 'text-primary-300')) }}">
                        {{ $row['ranking'] }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="mb-1 flex items-center justify-between gap-3">
                            <span class="truncate text-sm text-forge-steel">
                                {{ $row['name'] }}
                                @if ($t->is_team_based && $row['team_name']) <span class="text-forge-steel/40">({{ $row['team_name'] }})</span> @endif
                            </span>
                            <span class="shrink-0 font-display text-sm text-primary-300">{{ $row['score'] }}</span>
                        </div>
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-forge-graphite">
                            <div class="h-full rounded-full bg-gradient-to-r from-primary-600 to-primary-400 transition-all duration-700 ease-out" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
