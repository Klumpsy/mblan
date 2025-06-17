<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h1 class="font-semibold text-xl text-gray-800 dark:text-primary-400 leading-tight">
                {{ $blog->title }}
            </h1>
            @if ($blog->published)
                <span
                    class="text-sm bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-400 px-3 py-1 rounded-full">
                    Published
                </span>
            @else
                <span
                    class="text-sm bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-400 px-3 py-1 rounded-full">
                    Draft
                </span>
            @endif
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
            <div class="flex justify-between mb-6">
                <a href="{{ route('blogs') }}"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Blog
                </a>
            </div>
            <div class="mb-8">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                        <img src="{{ $blog->author->profile_photo_url ?? asset('images/default-avatar.png') }}"
                            alt="{{ $blog->author->name }}'s Avatar"
                            class="inline-block w-10 h-10 aspect-square rounded-full mr-2">
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $blog->author->name }}</div>
                            <div class="text-sm">{{ $blog->published_at->format('F j, Y \a\t g:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @if ($blog->image)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-8">
                    <div class="relative w-full">
                        <img src="{{ asset('storage/' . $blog->image) }}" alt="{{ $blog->title }}"
                            class="w-full h-full object-fit">
                        <div class="invisible md:visible absolute bottom-0 left-0 right-0 bg-black/70 p-6">
                            <h2 class="text-2xl font-bold text-primary-400 mb-2">{{ $blog->title }}</h2>
                            @if ($blog->preview_text)
                                <span class="text-gray-200 dark:text-white text-lg max-w-3xl">
                                    {{ $blog->preview_text }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-3">
                    @if ($blog->preview_text)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 md:hidden mb-6">
                            <span class="text-gray-600 dark:text-gray-300 text-md">
                                {{ $blog->preview_text }}
                            </span>
                        </div>
                    @endif
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                        <div class="p-8">
                            <div class="prose prose-md dark:prose-invert max-w-none">
                                {!! $blog->content !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
                        <h3
                            class="text-lg font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
                            Post Details
                        </h3>
                        <ul class="space-y-3">
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Author:</span>
                                <span
                                    class="font-medium text-gray-900 dark:text-white">{{ $blog->author->name }}</span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Published:</span>
                                <span
                                    class="font-medium text-gray-900 dark:text-white">{{ $blog->published_at->format('M j, Y') }}</span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Comments:</span>
                                <span
                                    class="font-medium text-gray-900 dark:text-white">{{ $blog->comments->count() }}</span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Status:</span>
                                @if ($blog->published)
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-400 rounded-full">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1"></span>
                                        Published
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-400 rounded-full">
                                        <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-1"></span>
                                        Draft
                                    </span>
                                @endif
                            </li>
                        </ul>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4">
                        <div class="space-y-2 max-h-90">
                            <livewire:blog.comment-section :$blog />
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
