<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-primary-400 leading-tight">
            {{ __('Edition') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
                @foreach ($editions as $edition)
                    <x-edition-card :edition="$edition" />
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
