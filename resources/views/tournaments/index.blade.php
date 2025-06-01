<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-primary-400 leading-tight">
            {{ __('Tournaments') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4 my-2">
                <h2  class="text-2xl text-primary-400">Active tournament</h2>
                @foreach($tournaments as $tournament)
                    <div class="p-4 mb-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                        <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ $tournament->name }}</h3>
                        <p class="text-gray-600 dark:text-gray-400">{{ $tournament->description }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Start Date: {{ $tournament->start_date }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">End Date: {{ $tournament->end_date }}</p>
                    </div>
                    <div class="flex">
                        {{ dump($tournament->getLeaderboard()) }}
                    </div>
                @endforeach
            </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
                <h2  class="text-2xl text-gray-600">Upcoming tournaments</h2>
            </div>
        </div>
    </div>
</x-app-layout>
