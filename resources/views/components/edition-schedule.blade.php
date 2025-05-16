<div x-data="{
    activeDate: null,
    activeSchedule: null,
    init() {
        // Set first date as active by default if schedules exist
        if (this.$refs.dates.children.length > 0) {
            this.activeDate = this.$refs.dates.children[0].getAttribute('data-date');
            this.activeSchedule = this.$refs.schedules.querySelector(`[data-date='${this.activeDate}']`);
        }
    },
    setActiveDate(date) {
        this.activeDate = date;
        this.activeSchedule = this.$refs.schedules.querySelector(`[data-date='${date}']`);
    }
}" class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
    <h2
        class="text-xl font-bold text-gray-900 dark:text-white p-4 border-b border-gray-200 dark:border-gray-700 bg-primary-100 dark:bg-primary-900">
        <div class="flex justify-between items-center">
            <span>Schedule</span>
            @if ($edition->schedules->isNotEmpty())
                <span class="text-sm bg-primary-500 text-white px-3 py-1 rounded-full">
                    {{ $edition->schedules->count() }} Day{{ $edition->schedules->count() > 1 ? 's' : '' }}
                </span>
            @endif
        </div>
    </h2>

    @if ($edition->schedules->isEmpty())
        <div class="p-6 text-center text-gray-500 dark:text-gray-400">
            No schedules available for this edition yet.
        </div>
    @else
        <div class="border-b border-gray-200 dark:border-gray-700">
            <div x-ref="dates" class="flex overflow-x-auto scrollbar-hide space-x-1 p-2">
                @foreach ($edition->schedules->sortBy('date') as $schedule)
                    @php
                        $formattedDate = \Carbon\Carbon::parse($schedule->date)->format('D, M j');
                        $dateValue = $schedule->date;
                    @endphp
                    <button type="button" data-date="{{ $dateValue }}" @click="setActiveDate('{{ $dateValue }}')"
                        :class="activeDate === '{{ $dateValue }}' ?
                            'bg-primary-500 text-white' :
                            'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                        class="flex-shrink-0 px-4 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                        {{ $formattedDate }}
                    </button>
                @endforeach
            </div>
        </div>

        <div x-ref="schedules" class="p-4">
            @foreach ($edition->schedules->sortBy('date') as $schedule)
                <div data-date="{{ $schedule->date }}" x-show="activeDate === '{{ $schedule->date }}'"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100" class="space-y-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            {{ $schedule->name }}
                        </h3>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ \Carbon\Carbon::parse($schedule->date)->format('l, F j, Y') }}
                        </span>
                    </div>

                    @if ($schedule->games->isEmpty())
                        <div
                            class="p-4 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-center text-gray-500 dark:text-gray-400">
                            No games scheduled for this day.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($schedule->games->sortBy(function ($game) use ($schedule) {
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
                                        <x-game-image :game="$game" size="lg" />
                                    </div>

                                    <div class="flex-grow p-4">
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start">
                                            <div>
                                                <h4
                                                    class="text-base sm:text-lg font-medium text-gray-900 dark:text-white">
                                                    {{ $game->name }}
                                                </h4>
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
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Details
                                                </a>
                                            </div>
                                        </div>

                                        @if ($game->shortDescription)
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 line-clamp-2">
                                                {{ Str::limit(strip_tags($game->shortDescription), 100) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($schedule->tournaments->isNotEmpty())
                        <div class="mt-6">
                            <h4 class="text-md font-semibold text-gray-800 dark:text-white mb-3 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-yellow-500"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                                Tournaments
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach ($schedule->tournaments as $tournament)
                                    <div
                                        class="border border-yellow-200 dark:border-yellow-900 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3">
                                        <h5 class="font-medium text-gray-900 dark:text-white">{{ $tournament->name }}
                                        </h5>
                                        <div class="flex items-center mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ \Carbon\Carbon::parse($tournament->start_time)->format('H:i') }}
                                        </div>
                                        @if ($tournament->game)
                                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                Game: {{ $tournament->game->name }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
