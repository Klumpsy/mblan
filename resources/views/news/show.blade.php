<x-app-layout>
    <div class="relative">
        <div class="pointer-events-none absolute inset-0 bg-grid opacity-30"></div>
        <div class="relative mx-auto max-w-3xl px-6 py-12">

            <div class="mb-8">
                <x-forge.btn variant="ghost" :href="route('news.index')" class="!px-4 !py-2.5">
                    <span aria-hidden="true">&larr;</span> Terug naar nieuws
                </x-forge.btn>
            </div>

            <div class="mb-3 flex flex-wrap items-center gap-x-2 gap-y-1 font-pixel text-[9px] uppercase tracking-[0.2em] text-forge-steel/50">
                @if ($news->published_at)
                    <span>{{ $news->published_at->translatedFormat('l d F Y') }}</span>
                @endif
                @if ($news->author)
                    <span class="text-primary-400">&middot; {{ $news->author->name }}</span>
                @endif
            </div>

            <h1 class="font-display text-3xl font-bold uppercase tracking-wide text-white md:text-5xl">{{ $news->title }}</h1>

            @if ($news->image)
                <div class="mt-8 overflow-hidden clip-corner metal-edge">
                    <img src="{{ asset('storage/' . $news->image) }}" alt="{{ $news->title }}" class="aspect-video w-full object-cover" />
                </div>
            @endif

            <div class="prose prose-invert mt-8 max-w-none prose-headings:font-display prose-headings:uppercase prose-headings:tracking-wide prose-a:text-primary-400 prose-strong:text-white prose-p:text-forge-steel/80 prose-li:text-forge-steel/80">
                {!! $news->content !!}
            </div>

            <div class="mt-8 border-t border-primary-500/10 pt-5">
                <livewire:reactions :model="$news" :key="'react-news-'.$news->id" />
            </div>
        </div>
    </div>
</x-app-layout>
