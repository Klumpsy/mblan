<x-app-layout>

    <section class="relative py-16 md:py-24">
        <div class="pointer-events-none absolute inset-0 bg-grid opacity-20"></div>
        <div class="relative mx-auto max-w-6xl px-6">

            <div x-data x-reveal>
                <x-forge.heading eyebrow="From The Barn">Latest News</x-forge.heading>
            </div>

            @if ($blogs->count() > 0)
                <div class="space-y-6">
                    @foreach ($blogs as $i => $blog)
                        <div x-data x-reveal.{{ ($i % 4) * 100 }}>
                            <x-forge.card class="overflow-hidden !p-0">
                                <div class="md:flex">
                                    @if ($blog->image)
                                        <div class="md:w-1/3 aspect-[4/3] overflow-hidden">
                                            <img src="{{ asset('storage/' . $blog->image) }}" alt="{{ $blog->title }}"
                                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                                loading="lazy" />
                                        </div>
                                    @endif
                                    <div class="{{ $blog->image ? 'md:w-2/3' : 'w-full' }} p-6">
                                        <div class="mb-3 flex items-center justify-between gap-4">
                                            <div class="flex w-full items-center gap-2">
                                                <div class="flex items-center gap-2 text-sm text-forge-steel/70">
                                                    <img src="{{ $blog->author->profile_photo_url ?? asset('images/default-avatar.png') }}"
                                                        alt="{{ $blog->author->name }}'s Avatar"
                                                        class="inline-block h-10 w-10 aspect-square rounded-full ring-1 ring-primary-500/30">
                                                    <span class="font-display uppercase tracking-widest text-xs text-forge-steel/80">{{ $blog->author->name }}</span>
                                                </div>
                                                <span class="text-forge-steel/30">&bull;</span>
                                                <span class="text-xs uppercase tracking-widest text-primary-400/80">
                                                    {{ $blog->published_at->format('M j, Y') }}
                                                </span>
                                            </div>
                                            <div class="flex shrink-0 items-center gap-2">
                                                @if ($blog->published)
                                                    <span class="inline-block h-2 w-2 rounded-full bg-primary-400 shadow-glow-sm"></span>
                                                    <span class="text-xs uppercase tracking-widest text-primary-300">Published</span>
                                                @else
                                                    <span class="inline-block h-2 w-2 rounded-full bg-amber-400"></span>
                                                    <span class="text-xs uppercase tracking-widest text-amber-300">Draft</span>
                                                @endif
                                            </div>
                                        </div>

                                        <h2 class="mb-3 font-display text-2xl font-bold uppercase tracking-wide text-white transition-colors hover:text-primary-300">
                                            <a href="{{ route('blogs.show', $blog->slug) }}">
                                                {{ $blog->title }}
                                            </a>
                                        </h2>

                                        <div class="mb-3 flex flex-wrap items-center gap-2">
                                            @each('components.tag', $blog->tags, 'tag')
                                        </div>

                                        @if ($blog->preview_text)
                                            <p class="mb-4 leading-relaxed text-forge-steel/80">
                                                {{ $blog->preview_text }}
                                            </p>
                                        @endif

                                        <div class="flex items-center justify-between">
                                            <a href="{{ route('blogs.show', $blog->slug) }}"
                                                class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-primary-300 transition-colors hover:text-primary-200">
                                                <span>Read more</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                                </svg>
                                            </a>

                                            <div class="flex items-center gap-4 text-sm text-forge-steel/60">
                                                <span class="flex items-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="h-4 w-4">
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
                            </x-forge.card>
                        </div>
                    @endforeach
                </div>
            @else
                <div x-data x-reveal>
                    <x-forge.card class="text-center">
                        <div class="mx-auto max-w-md py-8">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="mx-auto mb-4 h-16 w-16 text-primary-400/40">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <h3 class="mb-2 font-display text-lg font-semibold uppercase tracking-wide text-white">No blog posts found</h3>
                            <p class="text-forge-steel/60">
                                {{ request('search') ? 'Try adjusting your search terms.' : 'Check back later for updates!' }}
                            </p>
                        </div>
                    </x-forge.card>
                </div>
            @endif

        </div>
    </section>
</x-app-layout>
