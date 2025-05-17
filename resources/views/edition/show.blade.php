<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="font-semibold text-xl text-gray-800 dark:text-primary-400 leading-tight">
                {{ $edition->name }}
            </h1>
            <span
                class="text-sm bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-400 px-3 py-1 rounded-full">
                {{ $edition->year }}
            </span>
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
                    @if ($edition->logo)
                        <div class="flex-shrink-0 mb-4 md:mb-0 md:mr-6">
                            <img src="{{ asset('storage/' . $edition->logo) }}" alt="{{ $edition->name }}"
                                class="w-20 h-20 object-contain">
                        </div>
                    @endif
                    <div class="flex-grow">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $edition->name }}</h2>
                        <span class="text-gray-600 dark:text-gray-400">{!! $edition->description !!}</span>
                        <div class="mt-3 flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Participants: {{ $edition->participants->count() }}
                        </div>
                    </div>
                </div>
            </div>

            <x-edition-schedule :edition="$edition" />

            @if ($edition->games()->count() > 0)
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
                                    @if ($game->image)
                                        <img src="{{ asset('storage/' . $game->image) }}" alt="{{ $game->name }}"
                                            class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="h-10 w-10 text-gray-400 dark:text-gray-500" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
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
