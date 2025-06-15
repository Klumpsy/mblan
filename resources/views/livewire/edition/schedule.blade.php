<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
    <h2
        class="text-xl font-bold text-gray-900 dark:text-white p-4 border-b border-gray-200 dark:border-gray-700 bg-primary-100 dark:bg-gray-700">
        <div class="flex justify-between items-center">
            <span>Schedule</span>
            @if ($dates->isNotEmpty())
                <span class="text-sm bg-primary-500 text-white px-3 py-1 rounded-full">
                    {{ $dates->count() }} {{ Str::plural('Day', $dates->count()) }}
                </span>
            @endif
        </div>
    </h2>

    @if ($dates->isEmpty())
        <div class="p-6 text-center text-gray-500 dark:text-gray-400">
            No schedules available for this edition yet.
        </div>
    @else
        <div class="border-b border-gray-200 dark:border-gray-700">
            <div class="flex overflow-x-auto scrollbar-hide space-x-1 p-2">
                @foreach ($dates as $date)
                    @php
                        $carbonDate = \Carbon\Carbon::parse($date);
                        $formattedDate = $carbonDate->format('D, M j');
                    @endphp
                    <button type="button" wire:click="setActiveDate('{{ $date }}')"
                        class="flex-shrink-0 px-4 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out 
                            {{ $activeDate === $date
                                ? 'bg-primary-500 text-white'
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        {{ $formattedDate }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="p-4">
            @if ($schedulesForDate->isEmpty())
                <div
                    class="p-4 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-center text-gray-500 dark:text-gray-400">
                    No games scheduled for this day.
                </div>
            @else
                @foreach ($schedulesForDate as $schedule)
                    <div class="space-y-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                                {{ $schedule->name }}
                            </h3>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($activeDate)->format('l, F j, Y') }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            @foreach ($schedule->gamesForDate->sortBy(function ($game) {
        return $game->pivot->start_date;
    }) as $game)
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

                                <div
                                    class="flex flex-col sm:flex-row border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                                    <div class="flex justify-center sm:justify-start p-2 sm:p-3">
                                        <x-game-image :game="$game" />
                                    </div>

                                    <div class="flex-grow p-4">
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start">
                                            <div>
                                                <h4
                                                    class="text-base sm:text-lg font-medium text-gray-900 dark:text-white">
                                                    {{ $game->name }}
                                                </h4>
                                                <div class="flex items-center space-x-2 my-4 ">
                                                    @each('components.tag', $game->tags, 'tag')
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                    {{ $startTime->format('H:i') }} - {{ $endTime->format('H:i') }}
                                                    <span
                                                        class="ml-2 px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full text-xs">
                                                        {{ $durationText }}
                                                    </span>
                                                </p>
                                            </div>

                                            <div class="mt-2 sm:mt-0">
                                                <a href="{{ route('games.show', $game) }}"
                                                    class="inline-flex items-center px-3 py-1 bg-primary-100 dark:bg-primary-800 text-primary-800 dark:text-primary-200 text-sm rounded-md hover:bg-primary-200 dark:hover:bg-primary-700 transition-colors">
                                                    Details
                                                </a>
                                            </div>
                                        </div>

                                        @if ($game->short_description)
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 line-clamp-2">
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
