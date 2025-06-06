<x-app-layout>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('games') }}" class="mb-4 md:flex md:items-center px-2 md:px-0">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search games..."
                    class="border rounded px-3 py-2 text-sm w-full md:w-1/3 mb-2 md:mb-0" />
                <button type="submit"
                    class="bg-gray-600 text-primary-400 font-bold px-4 py-2 rounded text-sm md:w-auto md:ml-2 w-full">
                    Search
                </button>
            </form>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <x-games :games="$games" />
            </div>
            @if (count($games) === 0)
                <div class="bg-white dark:bg-gray-800 p-4 shadow">
                    <p class="text-primary-600 dark:text-primary-400">No games found..</p>
                </div>
            @endif
            <div class="flex flex-col mt-4">
                {{ $games->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
