<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('games') }}" class="mb-6 space-y-4 px-2 md:px-0">
                <div class="md:flex md:items-center md:space-x-4">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search games..."
                        class="border rounded px-3 py-2 text-sm w-full md:w-1/3 mb-2 md:mb-0" />
                    <button type="submit"
                        class="bg-gray-600 text-primary-400 font-bold px-4 py-2 rounded text-sm md:w-auto w-full">
                        Search
                    </button>
                </div>

                @if ($availableTags->isNotEmpty())
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Filter by Tags:</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($availableTags as $tag)
                                @php
                                    $isSelected = in_array($tag->id, (array) $selectedTags);
                                    $colorClass = $tag->color
                                        ? 'style="background-color: ' .
                                            $tag->color .
                                            '; border-color: ' .
                                            $tag->color .
                                            ';"'
                                        : '';
                                @endphp
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                        {{ $isSelected ? 'checked' : '' }} class="sr-only"
                                        onchange="this.form.submit()">
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-medium transition-all duration-200 
                                               {{ $isSelected
                                                   ? 'text-white shadow-md transform scale-105'
                                                   : 'text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
                                        @if ($tag->color && $isSelected) {!! $colorClass !!} @endif>
                                        {{ $tag->name }}
                                        @if ($isSelected)
                                            <svg class="w-3 h-3 ml-1 inline" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        @if (!empty($selectedTags) || request('search'))
                            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                                <a href="{{ route('games') }}"
                                    class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                    Clear all filters
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </form>

            @if (!empty($selectedTags) || request('search'))
                <div class="mb-4 px-2 md:px-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Active filters:</span>

                        @if (request('search'))
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                Search: "{{ request('search') }}"
                                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}"
                                    class="ml-1 hover:text-blue-600">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            </span>
                        @endif

                        @foreach ($availableTags->whereIn('id', $selectedTags) as $tag)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs text-white"
                                style="background-color: {{ $tag->color ?: '#6B7280' }};">
                                {{ $tag->name }}
                                <a href="{{ request()->fullUrlWithQuery(['tags' => array_diff((array) $selectedTags, [$tag->id])]) }}"
                                    class="ml-1 hover:text-gray-200">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <x-games :$games />
            </div>

            @if (count($games) === 0)
                <div class="bg-white dark:bg-gray-800 p-4 shadow">
                    <p class="text-primary-600 dark:text-primary-400">
                        @if (!empty($selectedTags) || request('search'))
                            No games found matching your filters. <a href="{{ route('games') }}"
                                class="underline hover:no-underline">Clear filters</a> to see all games.
                        @else
                            No games found.
                        @endif
                    </p>
                </div>
            @endif

            <div class="flex flex-col mt-4">
                {{ $games->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
