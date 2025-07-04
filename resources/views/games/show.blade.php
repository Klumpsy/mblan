<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <h1 class="font-semibold text-xl text-gray-800 dark:text-primary-400 leading-tight">
                {{ $game->name }}
            </h1>
            <span
                class="text-sm bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-400 px-3 py-1 rounded-full whitespace-nowrap self-start sm:self-auto">
                Released: {{ $game->year_of_release }}
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
            <div class="flex justify-between mb-6 ">
                <a href="{{ route('games') }}"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Games
                </a>
                <livewire:game.like :game="$game" />
            </div>
            <div class="flex items-center space-x-2 my-4 ">
                @each('components.tag', $game->tags, 'tag')
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-8">
                <div class="relative w-full aspect-video"> <!-- aspect ratio 16:9 -->
                    @if ($game->image)
                        <img src="{{ asset('storage/' . $game->image) }}" alt="{{ $game->name }}"
                            class="w-full h-full object-cover">

                        <div class="invisible md:visible absolute bottom-0 left-0 right-0 bg-black/70 p-6">
                            <h2 class="text-4xl font-bold text-primary-400 mb-2">{{ $game->name }}</h2>
                            <span class="text-gray-200 dark:text-white text-lg max-w-3xl">
                                {!! $game->short_description !!}
                            </span>
                        </div>
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gray-200 dark:bg-gray-700">
                            <span class="text-gray-500 dark:text-gray-400">No image available</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <div class="md:col-span-2 space-y-6" x-data="{ openSection: 1 }">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 md:hidden">
                        <span
                            class="text-gray-200 text-md prose dark:prose-invert max-w-none dark:text-gray-400 text-md">
                            {!! $game->short_description !!}
                        </span>
                    </div>

                    @if ($game->text_block_one)
                        <x-text-block :text="$game->text_block_one" :title="'About the Game'" :index="1" />
                    @endif

                    @if ($game->text_block_two)
                        <x-text-block :text="$game->text_block_two" :title="'Features'" :index="2" />
                    @endif

                    @if ($game->text_block_three)
                        <x-text-block :text="$game->text_block_three" :title="'Community & Updates'" :index="3" />
                    @endif
                </div>

                <div class="space-y-6">
                    @if ($game->link_to_youtube)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                            <h3
                                class="text-lg font-bold text-gray-900 dark:text-white p-4 border-b border-gray-200 dark:border-gray-700">
                                Game Trailer</h3>
                            <div class="aspect-w-16 aspect-h-9">
                                <x-video :link="$game->link_to_youtube" width="100%" height="100%" ratio="16:9"
                                    class="w-full" />
                            </div>
                        </div>
                    @endif

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
                        <h3
                            class="text-lg font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                            Game Details</h3>
                        <ul class="space-y-3">
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Release Year:</span>
                                <span
                                    class="font-medium text-gray-900 dark:text-white">{{ $game->year_of_release }}</span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Likes:</span>
                                <span
                                    class="font-medium text-gray-900 dark:text-white">{{ $game->getLikesCount() }}</span>
                            </li>
                            @if ($game->link_to_website)
                                <li class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <a href="{{ $game->link_to_website }}" target="_blank"
                                        class="inline-flex items-center w-full px-4 py-2 bg-primary-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-600 active:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        Visit Official Website
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
