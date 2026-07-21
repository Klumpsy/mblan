<div class="p-6 lg:p-8">
    <x-forge.heading eyebrow="Quick Access">Explore</x-forge.heading>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <x-forge.card class="block h-full">
            <a href="{{ route('games') }}" class="block">
                <h3 class="font-display text-xl font-bold uppercase tracking-wide text-white transition group-hover:text-primary-300">Games</h3>
                <p class="mt-2 text-sm text-forge-steel/70 leading-relaxed">Browse the full lineup and cast your vote for this year's selection.</p>
            </a>
        </x-forge.card>

        <x-forge.card class="block h-full">
            <a href="{{ route('editions') }}" class="block">
                <h3 class="font-display text-xl font-bold uppercase tracking-wide text-white transition group-hover:text-primary-300">Editions</h3>
                <p class="mt-2 text-sm text-forge-steel/70 leading-relaxed">Relive past editions of the barn and see what is coming next.</p>
            </a>
        </x-forge.card>

        <x-forge.card class="block h-full">
            <a href="{{ route('blogs') }}" class="block">
                <h3 class="font-display text-xl font-bold uppercase tracking-wide text-white transition group-hover:text-primary-300">News</h3>
                <p class="mt-2 text-sm text-forge-steel/70 leading-relaxed">Read the latest updates, announcements and stories from the barn.</p>
            </a>
        </x-forge.card>

        <x-forge.card class="block h-full">
            <a href="{{ route('tournaments') }}" class="block">
                <h3 class="font-display text-xl font-bold uppercase tracking-wide text-white transition group-hover:text-primary-300">Tournaments</h3>
                <p class="mt-2 text-sm text-forge-steel/70 leading-relaxed">Compete in intense matches for prizes and lasting bragging rights.</p>
            </a>
        </x-forge.card>

        <x-forge.card class="block h-full">
            <a href="{{ route('media') }}" class="block">
                <h3 class="font-display text-xl font-bold uppercase tracking-wide text-white transition group-hover:text-primary-300">Media</h3>
                <p class="mt-2 text-sm text-forge-steel/70 leading-relaxed">Explore photo galleries and captured moments from every edition.</p>
            </a>
        </x-forge.card>

        <x-forge.card class="block h-full">
            <a href="{{ route('achievements') }}" class="block">
                <h3 class="font-display text-xl font-bold uppercase tracking-wide text-white transition group-hover:text-primary-300">Achievements</h3>
                <p class="mt-2 text-sm text-forge-steel/70 leading-relaxed">Track the milestones and honours you have earned at the barn.</p>
            </a>
        </x-forge.card>
    </div>
</div>
