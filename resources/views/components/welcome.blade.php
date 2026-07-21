<div class="p-6 lg:p-8">
    <x-forge.heading eyebrow="Quick Access">Explore</x-forge.heading>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <x-forge.card class="block h-full">
            <a href="{{ route('schedule') }}" class="block">
                <h3 class="font-display text-xl font-bold uppercase tracking-wide text-white transition group-hover:text-primary-300">Schedule</h3>
                <p class="mt-2 text-sm text-forge-steel/70 leading-relaxed">See the game roster: which games run when across the weekend.</p>
            </a>
        </x-forge.card>

        <x-forge.card class="block h-full">
            <a href="{{ route('tournaments') }}" class="block">
                <h3 class="font-display text-xl font-bold uppercase tracking-wide text-white transition group-hover:text-primary-300">Tournaments</h3>
                <p class="mt-2 text-sm text-forge-steel/70 leading-relaxed">Follow the brackets, rosters and live scores for every tournament.</p>
            </a>
        </x-forge.card>

        <x-forge.card class="block h-full">
            <a href="{{ route('blogs') }}" class="block">
                <h3 class="font-display text-xl font-bold uppercase tracking-wide text-white transition group-hover:text-primary-300">News</h3>
                <p class="mt-2 text-sm text-forge-steel/70 leading-relaxed">Read the latest updates, announcements and stories from the barn.</p>
            </a>
        </x-forge.card>
    </div>
</div>
