<x-app-layout>

    <div class="mx-auto max-w-7xl px-6 pt-12">
        <x-forge.heading eyebrow="{{ $latestEdition?->name ?? 'The Barn' }}">
            Welcome, {{ $user->name }}
        </x-forge.heading>
    </div>

    @if ($user->hasSignedUpForLatestEdition() && $latestBlog)
        <div class="pt-4">
            <div class="mx-auto max-w-7xl px-6">
                <div class="clip-corner metal-edge overflow-hidden">
                    <x-news :blog="$latestBlog" />
                </div>
            </div>
        </div>
    @endif

    <div class="pt-8">
        <div class="mx-auto max-w-7xl px-6">
            <div class="clip-corner metal-edge overflow-hidden">
                <x-events :user="$user" :latestEdition="$latestEdition" />
            </div>
        </div>
    </div>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-6">
            <div class="clip-corner metal-edge overflow-hidden">
                <x-welcome />
            </div>
        </div>
    </div>
</x-app-layout>
