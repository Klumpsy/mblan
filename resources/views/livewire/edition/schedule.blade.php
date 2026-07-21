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
                                                @if ($game->installation_instructions)
                                                    <a href="{{ route('games.show', $game) }}#installation-instructions"
                                                        class="inline-flex items-center gap-2 px-3 py-2 metal-edge text-forge-steel hover:text-primary-300 font-display text-xs uppercase tracking-widest clip-corner transition-all duration-200 ease-out group">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-4 w-4 text-primary-400 group-hover:rotate-12 transition-transform duration-200"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                        Installation Guide
                                                    </a>
                                                @endif

                                                @if ($game->pivot->is_tournament)
                                                    <span class="inline-flex items-center gap-2 px-3 py-2 metal-edge text-forge-steel font-display text-xs uppercase tracking-widest clip-corner">
                                                        <svg class="w-4 h-4 text-warning-400"
                                                            fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                clip-rule="evenodd"></path>
                                                        </svg>
                                                        <span class="text-warning-400">Tournament</span>
                                                    </span>
                                                @endif

                                                <a href="{{ route('games.show', $game) }}"
                                                    class="inline-flex items-center gap-2 px-3 py-2 border border-primary-500/30 bg-primary-500/15 text-primary-300 hover:text-white font-display text-xs uppercase tracking-widest clip-corner transition-all duration-200 ease-out group">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="h-4 w-4 group-hover:scale-110 transition-transform duration-200"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    View Details
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="h-3 w-3 opacity-60 group-hover:translate-x-1 group-hover:opacity-100 transition-all duration-200"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </a>
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
