<x-app-layout>

    <section class="relative py-12 md:py-16">
        <div class="pointer-events-none absolute inset-0 bg-grid opacity-15"></div>
        <div class="relative mx-auto max-w-6xl px-6">

            <div class="mb-8">
                <x-forge.btn variant="ghost" href="{{ route('blogs') }}" class="!px-5 !py-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to news
                </x-forge.btn>
            </div>

            <div class="mb-8" x-data x-reveal>
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 text-sm text-forge-steel/70">
                        <img src="{{ $blog->author->profile_photo_url ?? asset('images/default-avatar.png') }}"
                            alt="{{ $blog->author->name }}'s Avatar"
                            class="inline-block h-11 w-11 aspect-square rounded-full ring-1 ring-primary-500/30">
                        <div>
                            <div class="font-display uppercase tracking-widest text-white">{{ $blog->author->name }}</div>
                            <div class="text-xs uppercase tracking-widest text-primary-400/80">{{ $blog->published_at->format('F j, Y \a\t g:i A') }}</div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        @each('components.tag', $blog->tags, 'tag')
                    </div>
                </div>
            </div>

            @if ($blog->image)
                <div class="mb-10" x-data x-reveal.100>
                    <x-forge.card class="overflow-hidden !p-0">
                        <div class="relative w-full">
                            <img src="{{ asset('storage/' . $blog->image) }}" alt="{{ $blog->title }}"
                                class="h-full w-full object-cover">
                            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-forge-black/90 via-forge-black/20 to-transparent"></div>
                            <div class="invisible md:visible absolute bottom-0 left-0 right-0 p-8">
                                <h1 class="mb-2 font-display text-3xl font-bold uppercase tracking-wide text-white md:text-4xl text-glow">{{ $blog->title }}</h1>
                                @if ($blog->preview_text)
                                    <span class="max-w-3xl text-lg text-forge-steel/90">
                                        {{ $blog->preview_text }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </x-forge.card>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                <div class="md:col-span-3">
                    @if ($blog->preview_text)
                        <div class="mb-6 md:hidden" x-data x-reveal>
                            <x-forge.card>
                                <h1 class="mb-3 font-display text-2xl font-bold uppercase tracking-wide text-white">{{ $blog->title }}</h1>
                                <span class="text-forge-steel/80">
                                    {{ $blog->preview_text }}
                                </span>
                            </x-forge.card>
                        </div>
                    @endif
                    <div x-data x-reveal.100>
                        <x-forge.card class="overflow-hidden">
                            <div class="p-2 md:p-4">
                                <div class="prose prose-invert max-w-none prose-headings:font-display prose-headings:uppercase prose-headings:tracking-wide prose-headings:text-white prose-a:text-primary-300 hover:prose-a:text-primary-200 prose-strong:text-white prose-p:text-forge-steel/90 prose-li:text-forge-steel/90 prose-p:leading-relaxed">
                                    {!! $blog->content !!}
                                </div>
                            </div>
                        </x-forge.card>
                    </div>
                </div>

                <div class="space-y-6" x-data x-reveal.150>
                    <x-forge.card>
                        <h3 class="mb-4 border-b border-primary-500/20 pb-2 font-display text-lg font-bold uppercase tracking-wide text-white">
                            Post Details
                        </h3>
                        <ul class="space-y-3 text-sm">
                            <li class="flex justify-between gap-2">
                                <span class="text-forge-steel/60">Author</span>
                                <span class="font-medium text-white">{{ $blog->author->name }}</span>
                            </li>
                            <li class="flex justify-between gap-2">
                                <span class="text-forge-steel/60">Published</span>
                                <span class="font-medium text-white">{{ $blog->published_at->format('M j, Y') }}</span>
                            </li>
                            <li class="flex justify-between gap-2">
                                <span class="text-forge-steel/60">Comments</span>
                                <span class="font-medium text-white">{{ $blog->comments->count() }}</span>
                            </li>
                            <li class="flex justify-between gap-2">
                                <span class="text-forge-steel/60">Status</span>
                                @if ($blog->published)
                                    <span class="inline-flex items-center gap-1.5 border border-primary-500/30 bg-primary-500/15 px-2.5 py-0.5 clip-corner text-xs font-medium uppercase tracking-wider text-primary-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-primary-400 shadow-glow-sm"></span>
                                        Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 border border-amber-500/30 bg-amber-500/15 px-2.5 py-0.5 clip-corner text-xs font-medium uppercase tracking-wider text-amber-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                                        Draft
                                    </span>
                                @endif
                            </li>
                        </ul>
                    </x-forge.card>

                    <x-forge.card>
                        <div class="space-y-2">
                            <livewire:blog.comment-section :$blog />
                        </div>
                    </x-forge.card>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
