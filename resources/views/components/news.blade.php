@props(['blog'])

<div
    class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-medium text-primary-40 text-primary-400">
            Latest News
        </h2>
        <a href="{{ route('blogs.show', $blog->slug) }}"
            class="text-md text-primary-200 space-x-3 flex justify-between items-center">
            <span>Read more</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </a>
    </div>

    <div class="my-2">
        <div class="flex items-center space-x-2">
            @each('components.tag', $blog->tags, 'tag')
        </div>
        <h2 class="text-lg text-primary-200 my-2">
            {{ $blog->title }}
        </h2>
        <article class="text-md text-gray-400">
            {!! $blog->preview_text !!}
        </article>
    </div>
</div>
