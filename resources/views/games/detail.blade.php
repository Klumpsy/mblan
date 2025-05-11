<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $game->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between">
                <a href="{{ route('games') }}" class="btn btn-default mb-4" style="width: fit-content;">
                    back to games
                </a>
                <livewire:game-like :game="$game" />
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                @if ($game->image)
                    <img src="{{ Storage::url($game->image) }}" alt="{{ $game->name }}"
                        style="width: 100%; height: 100%; object-fit: cover;">
                @else
                    <div
                        style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background-color: #e5e7eb;">
                        <span style="color: #6b7280;">No image available</span>
                    </div>
                @endif
            </div>

            <p class="mt-6 mb-6 text-white">
                {{ $game->description }}
            </p>
        </div>
        <div class="max-w-7xl mx-auto sm:px-6 lg: px-8 h-400">
            <x-video :link="$game->linkToYoutube" width="640" height="360" ratio="16:9" class="w-full max-w-3xl mx-auto" />
        </div>
    </div>
</x-app-layout>
