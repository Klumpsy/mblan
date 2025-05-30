<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-primary-400 leading-tight">
            {{ __('Games') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('games') }}" class="mb-4 flex items-center gap-2">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search games..."
                    class="border rounded px-3 py-2 w-1/3 text-sm"
                />
                <button type="submit" class="bg-gray-600 text-primary-400 font-bold  px-4 py-2 rounded text-sm">
                    Search
                </button>
            </form>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <x-games :games="$games" />
            </div>
            @if(count($games) === 0)
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
