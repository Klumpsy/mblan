<div
    class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
    <h1 class="mt-8 text-2xl font-medium text-gray-900 dark:text-white">
        Discover the Games for MBLAN editions
    </h1>

    <p class="mt-6 text-gray-500 dark:text-gray-400 leading-relaxed">
        Browse through our collection of multiplayer games perfect for LAN party madness! From competitive shooters and
        strategy games to cooperative adventures and indie gems, we've curated a selection that guarantees hours of fun
        with your friends. Vote for your favorites by giving them a thumbs up – the most popular games will be featured
        in our official tournament schedule and gaming sessions. Whether you're into intense esports titles, nostalgic
        classics, or the latest multiplayer hits, you'll find something here to fuel your next all-night gaming
        marathon. The community decides what we play, so make your voice heard!
    </p>

    <div class="space-y-4 mt-6">
        @foreach ($games as $game)
            <x-game-card :$game :key="$game->id" />
        @endforeach
    </div>
</div>
