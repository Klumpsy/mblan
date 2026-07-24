<x-app-layout>
    <div class="relative">
        <div class="pointer-events-none absolute inset-0 bg-grid opacity-30"></div>
        <div class="relative mx-auto max-w-6xl px-6 py-12">

            <x-forge.heading eyebrow="MBLAN26">Nieuws</x-forge.heading>

            @if ($items->isEmpty())
                <x-forge.card><p class="text-forge-steel/60">Er is nog geen nieuws.</p></x-forge.card>
            @else
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($items as $item)
                        <a href="{{ route('news.show', $item) }}" class="group block frame-wood overflow-hidden transition hover:-translate-y-0.5">
                            <div class="aspect-video w-full overflow-hidden bg-forge-graphite">
                                @if ($item->image)
                                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->title }}"
                                        class="h-full w-full object-cover transition group-hover:scale-105" loading="lazy" />
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-forge-steel/30">
                                        <span class="font-pixel text-[9px] uppercase tracking-widest">MBLAN26</span>
                                    </div>
                                @endif
                            </div>
                            <div class="p-5">
                                <div class="mb-2 flex flex-wrap items-center gap-x-2 gap-y-1 font-pixel text-[8px] uppercase tracking-widest text-forge-steel/50">
                                    @if ($item->published_at)
                                        <span>{{ $item->published_at->translatedFormat('d M Y') }}</span>
                                    @endif
                                    @if ($item->author)
                                        <span class="text-primary-400/70">&middot; {{ $item->author->name }}</span>
                                    @endif
                                </div>
                                <h3 class="font-display text-lg font-bold uppercase tracking-wide text-white transition group-hover:text-primary-300">{{ $item->title }}</h3>
                                <p class="mt-2 line-clamp-3 text-sm text-forge-steel/70">
                                    {{ $item->preview_text ?: \Illuminate\Support\Str::limit(strip_tags($item->content), 140) }}
                                </p>
                                <span class="mt-4 inline-block font-pixel text-[8px] uppercase tracking-widest text-primary-300">Lees meer &rarr;</span>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
