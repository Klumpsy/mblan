<div class="clip-corner metal-edge overflow-hidden">
    <h2 class="font-display text-xl font-bold uppercase tracking-wide text-white p-5 border-b border-primary-500/15 bg-primary-500/10">
        <div class="flex justify-between items-center">
            <span>Schedule</span>
            @if ($dates->isNotEmpty())
                <span class="font-display text-xs uppercase tracking-widest bg-primary-500 text-forge-black px-3 py-1 clip-corner">
                    {{ $dates->count() }} {{ Str::plural('Day', $dates->count()) }}
                </span>
            @endif
        </div>
    </h2>

    @if ($dates->isEmpty())
        <div class="p-6 text-center text-sm uppercase tracking-widest text-forge-steel/60">
            No schedules available for this edition yet.
        </div>
    @else
        <div class="border-b border-primary-500/15">
            <div class="flex overflow-x-auto scrollbar-hide space-x-2 p-3">
                @foreach ($dates as $date)
                    @php
                        $carbonDate = \Carbon\Carbon::parse($date);
                        $formattedDate = $carbonDate->format('D, M j');
                    @endphp
                    <button type="button" wire:click="setActiveDate('{{ $date }}')"
                        class="flex-shrink-0 px-4 py-2 clip-corner font-display text-xs uppercase tracking-widest transition duration-150 ease-in-out
                            {{ $activeDate === $date
                                ? 'bg-primary-500 text-forge-black'
                                : 'metal-edge text-forge-steel hover:text-white' }}">
                        {{ $formattedDate }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="p-5">
            @if ($schedulesForDate->isEmpty())
                <div class="p-4 border border-dashed border-primary-500/20 clip-corner text-center text-sm uppercase tracking-widest text-forge-steel/60">
                    No games scheduled for this day.
                </div>
            @else
                @foreach ($schedulesForDate as $schedule)
                    <div class="space-y-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-display text-lg font-semibold uppercase tracking-wide text-white">
                                {{ $schedule->name }}
                            </h3>
                            <span class="text-xs uppercase tracking-widest text-forge-steel/60">
                                {{ \Carbon\Carbon::parse($activeDate)->format('l, F j, Y') }}
                            </span>
                        </div>

                        <div class="space-y-3">

                            @foreach ($schedule->gamesForDate as $game)
                                @php
                                    $startTime = \Carbon\Carbon::parse($game->pivot->start_date);
                                    $endTime = \Carbon\Carbon::parse($game->pivot->end_date);
                                    $duration = $startTime->diffInMinutes($endTime);
                                    $durationHours = floor($duration / 60);
                                    $durationMinutes = $duration % 60;
                                    $durationText = '';

                                    if ($durationHours > 0) {
                                        $durationText .= $durationHours . 'h ';
                                    }
                                    if ($durationMinutes > 0 || $durationText === '') {
                                        $durationText .= $durationMinutes . 'm';
                                    }
                                @endphp

                                <div class="group flex flex-col sm:flex-row clip-corner metal-edge overflow-hidden transition-shadow hover:shadow-glow-sm">
                                    <div class="flex justify-center sm:justify-start p-2 sm:p-3">
                                        <x-game-image :game="$game" />
                                    </div>

                                    <div class="flex-grow p-4">
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start">
                                            <div>
                                                <h4 class="font-display text-base sm:text-lg font-semibold uppercase tracking-wide text-white">
                                                    {{ $game->name }}
                                                </h4>
                                                <div class="flex items-center space-x-2 my-4 ">
                                                    @each('components.tag', $game->tags, 'tag')
                                                </div>
                                                <p class="text-sm text-forge-steel/70 mt-1">
                                                    {{ $startTime->format('H:i') }} - {{ $endTime->format('H:i') }}
                                                    <span class="ml-2 px-2 py-0.5 border border-primary-500/20 bg-primary-500/10 text-primary-300 clip-corner text-xs uppercase tracking-widest">
                                                        {{ $durationText }}
                                                    </span>
                                                </p>
                                            </div>

                                            <div class="mt-2 sm:mt-0">
                                                @if ($game->pivot->is_tournament)
                                                    <span class="inline-flex items-center px-3 py-2 metal-edge font-display text-xs uppercase tracking-widest text-warning-400 clip-corner">
                                                        Tournament
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($game->short_description)
                                            <p class="text-sm text-forge-steel/60 mt-2 line-clamp-2">
                                                {{ Str::limit(strip_tags($game->short_description), 100) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    @endif
</div>
