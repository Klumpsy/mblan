<div
    class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
    <h2 class="mt-2 text-2xl font-medium text-primary-40 text-primary-400">
        Your events
    </h2>

    <p class="mt-6 mb-6 text-gray-500 dark:text-white leading-relaxed">
        Your upcoming events are listed below. Click on an event to view more details or manage your signup.
    </p>
    <div class="flex justify-between mb-6">
        @if ($latestEdition && $user->signups->isEmpty())
            <a href="{{ route('editions.signup', $latestEdition->slug) }}"
                class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-primary-300 dark:text-primary-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                Sign up for {{ $latestEdition->name }}
            </a>
        @endif
    </div>

    @foreach ($user->signups as $signup)
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow duration-200">
                @if (!$signup->confirmed)
                    <div class="p-6 text-center">
                        <div class="flex justify-center mb-4">
                            <div
                                class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-primary-500 dark:text-primary-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Pending Confirmation
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                            Your signup is being processed. We'll notify you once it's confirmed.
                        </p>
                        <div
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400">
                            <div class="w-2 h-2 bg-primary-400 rounded-full mr-2 animate-pulse"></div>
                            Processing
                        </div>
                    </div>
                @else
                    <div class="relative h-48 bg-gradient-to-br from-blue-400 to-purple-500">
                        <img src="{{ asset('storage/' . $signup->edition->logo) }}" alt="{{ $signup->edition->name }}"
                            alt="Summer Gaming Festival 2024" class="w-full h-full object-cover">
                    </div>

                    <div class="p-6">
                        <div class="text-green-300 text-sm mb-2">
                            Your average costs: €{{ number_format($signup->calculateCost(), 2) }} <span
                                class="text-xs text-white">(This may change and is calculated based on your
                                choices)</span>
                        </div>
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $signup->edition->name }}
                            </h3>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $signup->edition->year }}
                            </span>
                        </div>

                        <div class="space-y-3 mb-4">
                            <div class="flex items-center text-sm">
                                <x-heroicon-o-calendar class="w-5 h-15 text-gray-400 mr-3" />
                                @foreach ($signup->schedules as $schedule)
                                    <span
                                        class="inline-flex flex-col items-center  text-primary-400 me-2 border-r-2 pr-2 last:border-r-0 border-gray-100 dark:border-gray-700">
                                        <span class="text-xs font-medium uppercase">
                                            {{ \Carbon\Carbon::parse($schedule->date)->format('D') }}
                                        </span>
                                        <span class="text-xs mt-1">
                                            {{ \Carbon\Carbon::parse($schedule->date)->format('d-m-j') }}
                                        </span>
                                    </span>
                                @endforeach
                            </div>

                            <div class="flex items-center text-sm">
                                <x-heroicon-o-fire class="w-5 h-15 text-gray-400 mr-1" />
                                <div class="text-gray-600 dark:text-gray-300 text-xs">
                                    @if ($signup->stays_on_campsite)
                                        <span
                                            class="inline-block text-violet-400 py-2 px-2 border-gray-100 dark:border-gray-700 border-r-2">
                                            CAMPSITE
                                        </span>
                                    @endif
                                    @if ($signup->joins_barbecue)
                                        <span
                                            class="inline-block  text-violet-400 py-2 px-2  border-gray-100 dark:border-gray-700 border-r-2">
                                            BBQ
                                        </span>
                                    @endif
                                    @if ($signup->joins_pizza)
                                        <span class="inline-block  text-violet-400 py-2 px-2">
                                            PIZZA
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if ($signup->wants_tshirt)
                                <div class="flex items-center text-sm">
                                    <x-heroicon-o-paper-airplane class="w-5 h-15 text-gray-400 mr-1" />
                                    <div class="text-gray-600 dark:text-gray-300 text-xs">
                                        @if ($signup->wants_tshirt)
                                            <span
                                                class="inline-block text-pink-400 py-2 px-2 border-gray-100 dark:border-gray-700 border-r-2">
                                                T-shirt
                                            </span>
                                        @endif
                                        @if ($signup->tshirt_size)
                                            <span
                                                class="inline-block  text-pink-400 py-2 px-2  border-gray-100 dark:border-gray-700 border-r-2">
                                                {{ $signup->tshirt_size }}
                                            </span>
                                        @endif
                                        @if ($signup->tshirt_text)
                                            <span class="inline-block  text-pink-400 py-2 px-2">
                                                {{ $signup->tshirt_text }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="flex items-center text-sm">
                                <x-heroicon-o-shopping-cart class="w-5 h-5 text-gray-400 mr-1 shrink-0" />

                                @if ($signup->beverages->isEmpty())
                                    <span class="text-gray-600 dark:text-gray-300">
                                        You have no prevered beverages.
                                    </span>
                                @else
                                    <div class="flex flex-wrap gap-2 ms-2">
                                        @foreach ($signup->beverages as $beverage)
                                            <span
                                                class="inline-block text-xs text-primary-200 px-2 py-1 rounded-full uppercase font-semibold tracking-wide bg-primary-800/20 border border-primary-700/30">
                                                {{ $beverage->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <a href="/editions/{{ $signup->edition->slug }}"
                            class="block w-full text-center dark:bg-gray-700 dark:hover:bg-gray-600 hover:bg-primary-100 text-primary-400 font-medium py-2 px-4 rounded-lg transition-colors duration-200 text-sm">
                            View Details
                        </a>
                        <div
                            class="mt-4 flex  justify-center w-full dark:hover:bg-gray-600 hover:bg-primary-100 text-primary-400 font-medium py-2 px-4 rounded-lg transition-colors duration-200 text-sm">
                            <x-edition-signout-button :edition="$signup->edition" />
                        </div>
                    </div>
                @endif
            </div>
    @endforeach
</div>
