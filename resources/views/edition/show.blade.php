<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center md:flex-row md:justify-between w-full">

            <h1 class="font-semibold text-xl text-gray-800 dark:text-primary-400 leading-tight md:mb-0">
                {{ $edition->name }}
            </h1>

            <div class="flex items-center w-full md:w-auto flex-wrap mt-2 justify-end">
                <span
                    class="text-sm bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-400 px-3 py-1 rounded-full mr-2">
                    {{ $edition->year }}
                </span>

                @if ($edition->year >= idate('Y') && !auth()->user()->hasSignedUpFor($edition))
                    <a href="{{ route('editions.signup', $edition->slug) }}"
                        class="whitespace-nowrap md:mt-0 text-sm bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-400 px-3 py-1 rounded-full  hover:bg-green-200 dark:hover:bg-green-700 transition-colors cursor-pointer">
                        Sign up for {{ $edition->name }}
                    </a>
                @elseif(auth()->user()->hasSignedUpFor($edition) && auth()->user()->can('accessWithConfirmedSignup', $edition))
                    <span
                        class="bg-green-800 text-green-100 dark:text-green-400 dark:bg-green-800 px-3 py-1 rounded-full">
                        Participating
                    </span>
                @elseif($edition->year < idate('Y'))
                    <span class="bg-gray-700 text-red-500 dark:text-red-500 dark:bg-gray-700 px-3 py-1 rounded-full">
                        Closed
                    </span>
                @else
                    <span
                        class="bg-primary-300 text-primary-800 dark:text-primary-800 dark:bg-primary-300 px-3 py-1 rounded-full">
                        Signup pending
                    </span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between mb-6">
                <a href="{{ route('editions') }}"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Editions
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="flex flex-col md:flex-row md:items-center p-4 md:p-6">
                    <div class="flex-grow">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $edition->name }}</h2>
                        <span class="text-gray-600 dark:text-gray-400">{!! $edition->description !!}</span>
                        <div class="mt-3 flex items-center justify-between">
                            <div class="mt-3 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Participants: {{ $edition->confirmedSignups->count() }}
                            </div>
                            @if ($edition->year >= idate('Y') && auth()->user()->hasSignedUpFor($edition))
                                <x-edition-signout-button :edition="$edition" />
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @can('accessWithConfirmedSignup', $edition)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="flex flex-col md:flex-row md:items-center p-4 md:p-6">
                        <div class="flex-grow">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Your fellow gamers for
                                this
                                edition:</h2>
                            <div class="mt-3 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                @foreach ($edition->confirmedSignups as $signup)
                                    <span class="inline-block bg-gray-600 text-primary-200 py-2 px-2 rounded-full me-2">
                                        {{ $signup->user->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

            <livewire:edition.schedule :edition="$edition" />

            @if ($edition->hasGames())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden mt-6">
                    <h3
                        class="text-lg font-bold text-gray-900 dark:text-white p-4 border-b border-gray-200 dark:border-gray-700">
                        Featured Games
                    </h3>
                    <div class="p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        @foreach ($edition->games()->take(10)->get() as $game)
                            <a href="{{ route('games.show', $game) }}" class="group">
                                <div
                                    class="aspect-w-1 aspect-h-1 bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden">

                                    <img src="{{ asset('storage/' . $game->image) }}" alt="{{ $game->name }}"
                                        class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">

                                </div>
                                <div class="mt-2 text-sm text-center">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $game->name }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
