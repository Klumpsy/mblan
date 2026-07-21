<x-app-layout>
    <div class="relative py-12">
        <div class="absolute inset-0 bg-grid opacity-20 pointer-events-none"></div>
        <div class="relative max-w-7xl mx-auto px-6">
            <form method="GET" action="{{ route('games') }}" class="mb-8 space-y-4">
                <div class="md:flex md:items-center md:gap-4">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search games..."
                        class="clip-corner metal-edge bg-forge-panel/60 border border-primary-500/25 text-forge-steel placeholder-forge-steel/40 px-4 py-2.5 text-sm w-full md:w-1/3 mb-2 md:mb-0 focus:border-primary-400 focus:ring-1 focus:ring-primary-400 focus:outline-none" />
                    <x-forge.btn type="submit" class="w-full md:w-auto !px-6 !py-2.5">Search</x-forge.btn>
                </div>

                @if ($availableTags->isNotEmpty())
                    <div class="clip-corner metal-edge p-5">
                        <h3 class="font-display text-xs uppercase tracking-[0.3em] text-primary-400 mb-4">Filter by Tags</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($availableTags as $tag)
                                @php
                                    $isSelected = in_array($tag->id, (array) $selectedTags);
                                    $colorClass = $tag->color
                                        ? 'style="background-color: ' .
                                            $tag->color .
                                            '; border-color: ' .
                                            $tag->color .
                                            ';"'
                                        : '';
                                @endphp
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                        {{ $isSelected ? 'checked' : '' }} class="sr-only"
                                        onchange="this.form.submit()">
                                    <span
                                        class="inline-flex items-center px-3 py-1 clip-corner font-display text-xs uppercase tracking-wider transition-all duration-200
                                               {{ $isSelected
                                                   ? 'text-white shadow-glow-sm border border-primary-400 scale-105'
                                                   : 'text-forge-steel border border-primary-500/25 bg-primary-500/10 hover:bg-primary-500/20 hover:text-white' }}"
                                        @if ($tag->color && $isSelected) {!! $colorClass !!} @endif>
                                        {{ $tag->name }}
                                        @if ($isSelected)
                                            <svg class="w-3 h-3 ml-1 inline" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        @if (!empty($selectedTags) || request('search'))
                            <div class="mt-4 pt-4 border-t border-primary-500/15">
                                <a href="{{ route('games') }}"
                                    class="font-display text-xs uppercase tracking-widest text-forge-steel/60 hover:text-primary-300 transition">
                                    Clear all filters
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </form>

            @if (!empty($selectedTags) || request('search'))
                <div class="mb-6">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-display text-xs uppercase tracking-widest text-forge-steel/60">Active filters:</span>

                        @if (request('search'))
                            <span
                                class="inline-flex items-center px-2.5 py-1 clip-corner text-xs border border-primary-500/30 bg-primary-500/15 text-primary-300">
                                Search: "{{ request('search') }}"
                                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}"
                                    class="ml-1 hover:text-white">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            </span>
                        @endif

                        @foreach ($availableTags->whereIn('id', $selectedTags) as $tag)
                            <span class="inline-flex items-center px-2.5 py-1 clip-corner text-xs text-white"
                                style="background-color: {{ $tag->color ?: 'rgb(var(--c-primary-600))' }};">
                                {{ $tag->name }}
                                <a href="{{ request()->fullUrlWithQuery(['tags' => array_diff((array) $selectedTags, [$tag->id])]) }}"
                                    class="ml-1 hover:text-forge-black">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <x-games :$games />

            @if (count($games) === 0)
                <div class="mt-6 clip-corner metal-edge p-6">
                    <p class="text-forge-steel/80">
                        @if (!empty($selectedTags) || request('search'))
                            No games found matching your filters. <a href="{{ route('games') }}"
                                class="text-primary-300 underline hover:no-underline">Clear filters</a> to see all games.
                        @else
                            No games found.
                        @endif
                    </p>
                </div>
            @endif

            <div class="flex flex-col mt-8">
                {{ $games->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
