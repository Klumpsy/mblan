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
                                                @if ($game->installation_instructions)
                                                    @if ($game->installation_instructions)
                                                        <a href="{{ route('games.show', $game) }}#installation-instructions"
                                                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-semibold text-sm rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 ease-out group">
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="h-4 w-4 group-hover:rotate-12 transition-transform duration-200"
                                                                fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                            Installation Guide
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="h-3 w-3 group-hover:translate-x-1 transition-transform duration-200"
                                                                fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M9 5l7 7-7 7" />
                                                            </svg>
                                                        </a>
                                                    @endif
                                                @endif
                                                @if ($game->pivot->is_tournament)
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-200 text-xs rounded-full">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor"
                                                            viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                clip-rule="evenodd"></path>
                                                        </svg>
                                                        Tournament
                                                    </span>
                                                @endif
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
