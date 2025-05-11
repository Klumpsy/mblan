<div
    class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
    <div class="flex justify-between">
        <x-application-logo class="block h-12 w-auto" />
        <x-protected-button role="admin" route="games.create" class="btn btn-success">
            Add new game
        </x-protected-button>
    </div>

    <h1 class="mt-8 text-2xl font-medium text-gray-900 dark:text-white">
        Hier ga je de games vinden die tot de mogelijkheid van spelen behoren.
    </h1>

    <p class="mt-6 text-gray-500 dark:text-gray-400 leading-relaxed">
        De games met de meeste likes zullen in het speelschema worden opgenomen.
    </p>

    <div class="space-y-4 mt-6">
        @foreach ($games as $game)
            <x-game-card :game="$game" />
        @endforeach
    </div>
</div>
