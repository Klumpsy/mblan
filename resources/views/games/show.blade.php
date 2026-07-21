<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <h1 class="font-display text-xl font-bold uppercase tracking-wide text-white leading-tight">
                {{ $game->name }}
            </h1>
            <x-forge.badge class="self-start sm:self-auto whitespace-nowrap">
                Released: {{ $game->year_of_release }}
            </x-forge.badge>
        </div>
    </x-slot>

    <div class="relative py-8">
        <div class="absolute inset-0 bg-grid opacity-20 pointer-events-none"></div>
        <div class="relative max-w-7xl mx-auto px-6">
            <div class="flex justify-between items-center mb-6">
                <x-forge.btn href="{{ route('games') }}" variant="ghost" class="!px-5 !py-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Games
                </x-forge.btn>
                <livewire:game.like :game="$game" />
            </div>

            <div class="flex flex-wrap items-center gap-2 my-4">
                @each('components.tag', $game->tags, 'tag')
            </div>

            <div class="clip-corner metal-edge overflow-hidden mb-8" x-data x-reveal>
                <div class="relative w-full aspect-video"> <!-- aspect ratio 16:9 -->
                    @if ($game->image)
                        <img src="{{ asset('storage/' . $game->image) }}" alt="{{ $game->name }}"
                            class="w-full h-full object-cover">

                        <div class="invisible md:visible absolute bottom-0 left-0 right-0 bg-gradient-to-t from-forge-black via-forge-black/80 to-transparent p-6">
                            <h2 class="font-display text-4xl font-bold uppercase tracking-wide text-white text-glow mb-2">{{ $game->name }}</h2>
                            <span class="text-forge-steel/80 text-lg max-w-3xl">
                                {!! $game->short_description !!}
                            </span>
                        </div>
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-forge-graphite">
                            <span class="text-xs uppercase tracking-widest text-forge-steel/40">No image available</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <div class="md:col-span-2 space-y-6" x-data="{ openSection: 1 }">
                    <div class="clip-corner metal-edge p-6 md:hidden">
                        <span class="prose prose-invert max-w-none text-forge-steel/80">
                            {!! $game->short_description !!}
                        </span>
                    </div>

                    @if ($game->text_block_one)
                        <x-text-block :text="$game->text_block_one" :title="'About the Game'" :index="1" />
                    @endif

                    @if ($game->text_block_two)
                        <x-text-block :text="$game->text_block_two" :title="'Features'" :index="2" />
                    @endif

                    @if ($game->text_block_three)
                        <x-text-block :text="$game->text_block_three" :title="'Community & Updates'" :index="3" />
                    @endif
                    @if ($game->installation_instructions)
                        <x-text-block :text="$game->installation_instructions" :title="'Installation instructions'" :index="4" :index="4"
                            id="installation-instructions" />
                    @endif
                </div>

                <div class="space-y-6">
                    @if ($game->link_to_youtube)
                        <div class="clip-corner metal-edge overflow-hidden">
                            <h3 class="font-display text-lg font-bold uppercase tracking-wide text-white p-4 border-b border-primary-500/15">
                                Game Trailer</h3>
                            <div class="aspect-w-16 aspect-h-9">
                                <x-video :link="$game->link_to_youtube" width="100%" height="100%" ratio="16:9"
                                    class="w-full" />
                            </div>
                        </div>
                    @endif

                    <div class="clip-corner metal-edge p-5">
                        <h3 class="font-display text-lg font-bold uppercase tracking-wide text-white mb-4 border-b border-primary-500/15 pb-3">
                            Game Details</h3>
                        <ul class="space-y-3">
                            <li class="flex justify-between">
                                <span class="text-forge-steel/60 text-sm uppercase tracking-wide">Release Year:</span>
                                <span class="font-display font-semibold text-white">{{ $game->year_of_release }}</span>
                            </li>
                            <li class="flex justify-between">
                                <span class="text-forge-steel/60 text-sm uppercase tracking-wide">Likes:</span>
                                <span class="font-display font-semibold text-primary-300">{{ $game->getLikesCount() }}</span>
                            </li>
                            @if ($game->link_to_website)
                                <li class="pt-3 border-t border-primary-500/15">
                                    <x-forge.btn href="{{ $game->link_to_website }}" target="_blank" class="w-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        Visit Official Website
                                    </x-forge.btn>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
