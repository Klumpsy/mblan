@props(['blog'])

<div class="p-6 lg:p-8">
    <div class="mb-3">
        <span class="font-display text-xs uppercase tracking-[0.3em] text-primary-400">Laatste Nieuws</span>
    </div>

    <div class="flex items-center space-x-2">
        @each('components.tag', $blog->tags, 'tag')
    </div>

    <h2 class="mt-4 font-display text-2xl font-bold uppercase tracking-wide text-white">
        {{ $blog->title }}
    </h2>

    <article class="mt-3 text-sm text-forge-steel/80 leading-relaxed">
        {!! $blog->preview_text !!}
    </article>

    <div class="mt-6">
        <x-forge.btn variant="ghost" href="{{ route('blogs.show', $blog->slug) }}">Lees meer</x-forge.btn>
    </div>
</div>
