<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            🍺 Live Beer Activity
        </x-slot>

        <x-slot name="description">
            Real-time updates of recent beer consumption
        </x-slot>

        <div class="space-y-4">
            @if ($recentActivity->isEmpty())
                <div class="text-center py-12">
                    <div class="mx-auto h-12 w-12 text-gray-400 mb-4">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 48 48">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4v12.9c0 5.8 3.9 10.7 9.4 12.1l.6.1.6-.1c5.5-1.4 9.4-6.3 9.4-12.1V3l-8 4-3 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">No recent activity</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Beer activity will appear here as it happens</p>
                </div>
            @else
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @foreach ($recentActivity as $index => $activity)
                            <li>
                                <div class="relative pb-8">
                                    @if (!$loop->last)
                                        <span
                                            class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700"
                                            aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <img class="h-8 w-8 rounded-full ring-2 ring-white dark:ring-gray-900"
                                                src="{{ $activity['avatar'] }}" alt="{{ $activity['user_name'] }}">
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5 ml-3">
                                            <div>
                                                <p class="text-sm text-gray-900 dark:text-white">
                                                    <span class="font-medium">{{ $activity['user_name'] }}</span>
                                                    had a beer
                                                    <span
                                                        class="inline-flex items-center rounded-full bg-yellow-100 dark:bg-yellow-900/30 px-2 py-1 text-xs font-medium text-yellow-800 dark:text-yellow-300">
                                                        🍺 Total: {{ $activity['beer_count'] }}
                                                    </span>
                                                </p>
                                            </div>
                                            <div
                                                class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                                                <time datetime="{{ $activity['last_beer_at']->toISOString() }}">
                                                    {{ $activity['time_ago'] }}
                                                </time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Fun Facts Section -->
                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                        <div class="text-2xl font-bold">{{ $recentActivity->count() }}</div>
                        <div class="text-blue-100 text-sm">Recent activities</div>
                    </div>
                    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
                        <div class="text-2xl font-bold">{{ $recentActivity->sum('beer_count') }}</div>
                        <div class="text-green-100 text-sm">Total beers shown</div>
                    </div>
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
                        <div class="text-2xl font-bold">{{ $recentActivity->unique('user_name')->count() }}</div>
                        <div class="text-purple-100 text-sm">Active drinkers</div>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
