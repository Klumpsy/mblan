<div>
    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">
        Comments ({{ count($comments) }})
    </h3>
    <div class="flex flex-col text-md text-white dark:text-gray-400 h-96">
        <div class="flex-1 overflow-y-auto overflow-x-hidden pr-2 space-y-4 mb-4">
            @forelse ($comments as $blogComment)
                <div class="flex-col border-b border-gray-700 my-2 break-words ">
                    <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                        <img src="{{ $blogComment->author->profile_photo_url ?? asset('images/default-avatar.png') }}"
                            alt="{{ $blogComment->author->name }}'s Avatar"
                            class="inline-block w-10 h-10 aspect-square rounded-full mr-2">
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ $blogComment->author->name }}
                                @can('delete-blog-comment', $blogComment)
                                    )
                                    <span class="text-xs text-primary-300">(author)</span>
                                    <span wire:click="deleteComment({{ $blogComment->id }})"
                                        class="text-red-400 cursor-pointer hover:text-red-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4 inline-block">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    </span>
                                @endcan
                            </div>
                            <div class="text-sm">{{ $blogComment->created_at->format('F j, Y \a\t g:i A') }}</div>
                        </div>
                    </div>
                    <div class="my-2">
                        {{ $blogComment->comment }}
                    </div>
                </div>
            @empty
                <span class="border-bottom border-gray-100">It's empty in here..</span>
            @endforelse
        </div>
    </div>
    <form wire:submit="addComment" class="w-full">
        @csrf
        <div class="mt-4">
            <textarea id="comment" wire:model="comment" rows="4"
                class="w-full rounded-lg border border-gray-300 p-4 text-gray-900 placeholder-gray-500 focus:border-primary-500 focus:ring-2 focus:ring-primary-500 focus:ring-opacity-50 transition-colors duration-200 resize-none shadow-sm"
                placeholder="Write your comment here..."></textarea>
        </div>
        @error('comment')
            <span class="my-2 text-red-400 text-sm">{{ $message }}
            </span>
        @enderror
        <div class="flex justify-end">
            <button type="submit"
                class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200 shadow-sm hover:shadow-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                    </path>
                </svg>
                Add Comment
            </button>
        </div>

    </form>
</div>
