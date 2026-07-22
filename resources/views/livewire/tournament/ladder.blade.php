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
    </div>

    @if ($rows->isEmpty())
        <div class="p-10 text-center text-sm text-forge-steel/50">Nog geen deelnemers.</div>
    @else
        {{-- Podium (top 3) --}}
        @if ($podium->count() >= 2)
            <div class="grid grid-cols-3 gap-3 bg-forge-forest/30 p-6">
                @php
                    $order = [1 => 'order-1', 0 => 'order-2', 2 => 'order-3'];
                    $heights = [0 => 'h-28', 1 => 'h-20', 2 => 'h-16'];
                    $medals = [0 => 'text-amber-300', 1 => 'text-forge-steel', 2 => 'text-amber-600'];
                @endphp
                @foreach ($podium as $i => $row)
                    <div class="flex flex-col items-center justify-end {{ $order[$i] ?? 'order-2' }}">
                        <div class="mb-2 text-center">
                            <div class="font-display text-sm font-bold uppercase tracking-wide text-white truncate max-w-[9rem]">{{ $row['name'] }}</div>
                            <div class="font-display text-lg {{ $medals[$i] ?? 'text-primary-300' }}">{{ $row['score'] }}</div>
                        </div>
                        <div class="flex w-full items-end justify-center {{ $heights[$i] ?? 'h-16' }} clip-corner bg-gradient-to-t from-primary-500/10 to-primary-500/40 border border-primary-500/30 transition-all duration-700">
                            <span class="pb-2 font-display text-2xl font-bold {{ $medals[$i] ?? 'text-primary-300' }} text-glow">{{ $row['ranking'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Ranked list with animated score bars --}}
        <ul class="divide-y divide-primary-500/10">
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
