<div x-data="{
    activeDate: null,
    activeSchedule: null,
    init() {
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
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
