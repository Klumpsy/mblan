<div class="p-6 lg:p-10 clip-corner metal-edge">
    <div x-data x-reveal>
        <x-forge.heading eyebrow="The Arsenal">Discover the Games</x-forge.heading>
    </div>

    <p class="-mt-4 mb-8 max-w-3xl text-forge-steel/80 leading-relaxed">
        Browse through our collection of multiplayer games perfect for LAN party madness! From competitive shooters and
        strategy games to cooperative adventures and indie gems, we've curated a selection that guarantees hours of fun
        with your friends. Vote for your favorites by giving them a thumbs up - the most popular games will be featured
        in our official tournament schedule and gaming sessions. Whether you're into intense esports titles, nostalgic
        classics, or the latest multiplayer hits, you'll find something here to fuel your next all-night gaming
        marathon. The community decides what we play, so make your voice heard!
    </p>

    <div class="space-y-4 mt-6">
        @foreach ($games as $i => $game)
            <div x-data x-reveal.{{ ($i % 3) * 100 }}>
                <x-game-card :$game :key="$game->id" />
            </div>
        @endforeach
    </div>
</div>
