<div>
    <h3 class="mb-4 border-b border-primary-500/20 pb-2 font-display text-lg font-bold uppercase tracking-wide text-white">
        Comments ({{ count($comments) }})
    </h3>
    <div class="flex h-96 flex-col text-forge-steel">
        <div class="mb-4 flex-1 space-y-4 overflow-y-auto overflow-x-hidden pr-2">
            @forelse ($comments as $blogComment)
                <div class="flex-col break-words border-b border-primary-500/10 pb-3">
                    <div class="flex items-center gap-2 text-sm text-forge-steel/70">
                        <img src="{{ $blogComment->author->profile_photo_url ?? asset('images/default-avatar.png') }}"
                            alt="{{ $blogComment->author->name }}'s Avatar"
                            class="inline-block h-10 w-10 aspect-square rounded-full ring-1 ring-primary-500/30">
                        <div>
                            <div class="flex items-center gap-1.5 font-display uppercase tracking-widest text-white">
                                {{ $blogComment->author->name }}
                                @can('delete-blog-comment', $blogComment)
                                    <span class="text-xs text-primary-300">(author)</span>
                                    <span wire:click="deleteComment({{ $blogComment->id }})"
                                        class="cursor-pointer text-xs font-display uppercase tracking-widest text-danger-400 transition-colors hover:text-danger-500">
                                        Delete
                                    </span>
                                @endcan
                            </div>
                            <div class="text-xs uppercase tracking-widest text-primary-400/70">{{ $blogComment->created_at->format('F j, Y \a\t g:i A') }}</div>
                        </div>
                    </div>
                    <div class="my-2 text-sm leading-relaxed text-forge-steel/90">
                        {{ $blogComment->comment }}
                    </div>
                </div>
            @empty
                <span class="text-forge-steel/50">It's empty in here..</span>
            @endforelse
        </div>
    </div>
    <form wire:submit="addComment" class="w-full">
        @csrf
        <div class="mt-4">
            <textarea id="comment" wire:model="comment" rows="4"
                class="w-full resize-none clip-corner border border-primary-500/25 bg-forge-graphite/60 p-4 text-forge-steel placeholder-forge-steel/40 transition-colors duration-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/40 focus:outline-none"
                placeholder="Write your comment here..."></textarea>
        </div>
        @error('comment')
            <span class="my-2 block text-sm text-red-400">{{ $message }}
            </span>
        @enderror
        <div class="mt-3 flex justify-end">
            <x-forge.btn type="submit">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                    </path>
                </svg>
                Add Comment
            </x-forge.btn>
        </div>

    </form>
</div>
