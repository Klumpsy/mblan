<x-app-layout>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if ($blogs->count() > 0)
                <div class="space-y-6">
                    @foreach ($blogs as $blog)
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg transition-all duration-300 hover:shadow-2xl">
                            <div class="md:flex">
                                <div class="md:w-1/3 aspect-[4/3] overflow-hidden">
                                    <img src="{{ asset('storage/' . $blog->image) }}" alt="{{ $blog->title }}"
                                        class="w-full h-full object-cover" />
                                </div>
                                <div class="{{ $blog->image ? 'md:w-2/3' : 'w-full' }} p-6">
                                    <div class="flex items-center justify-between space-x-4 mb-3">
                                        <div class="w-full flex items-center space-x-2">
                                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                <img src="{{ $blog->author->profile_photo_url ?? asset('images/default-avatar.png') }}"
                                                    alt="{{ $blog->author->name }}'s Avatar"
                                                    class="inline-block w-10 h-10 aspect-square rounded-full mr-2">
                                                <span>{{ $blog->author->name }}</span>
                                            </div>
                                            <span class="text-gray-300 dark:text-gray-600">•</span>
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $blog->published_at->format('M j, Y') }}
                                            </span>
                                        </div>
                                        <div class="flex space-x-2 items-center">
                                            @if ($blog->published)
                                                <span class="inline-block w-2 h-2 bg-green-500 rounded-full"></span>
                                                <span
                                                    class="text-xs text-green-600 dark:text-green-400">Published</span>
                                            @else
                                                <span class="inline-block w-2 h-2 bg-yellow-500 rounded-full"></span>
                                                <span class="text-xs text-yellow-600 dark:text-yellow-400">Draft</span>
                                            @endif
                                        </div>
                                    </div>

                                    <h2
                                        class="text-2xl font-bold text-gray-900 dark:text-white mb-3 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                        <a href="{{ route('blogs.show', $blog->slug) }}">
                                            {{ $blog->title }}
                                        </a>
                                    </h2>
                                    <div class="flex items-center space-x-2 mb-2">
                                        @each('components.tag', $blog->tags, 'tag')
                                    </div>

                                    @if ($blog->preview_text)
                                        <p class="text-gray-600 dark:text-gray-300 mb-4 leading-relaxed">
                                            {{ $blog->preview_text }}
                                        </p>
                                    @endif

                                    <div class="flex items-center justify-between">
                                        <a href="{{ route('blogs.show', $blog->slug) }}"
                                            class="inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium transition-colors">
                                            <span>Read more</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="w-4 h-4 ml-2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                            </svg>
                                        </a>

                                        <div
                                            class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                            <span class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-4 h-4 mr-1">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                                                </svg>
                                                {{ $blog->comments->count() }}
                                                {{ Str::plural('comment', $blog->comments->count()) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 p-6 shadow-xl sm:rounded-lg text-center">
                    <div class="max-w-md mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500 mb-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0-1.125-.504-1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No blog posts found</h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            {{ request('search') ? 'Try adjusting your search terms.' : 'Check back later for updates!' }}
                        </p>
                    </div>
                </div>
            @endif


        </div>
    </div>
</x-app-layout>
