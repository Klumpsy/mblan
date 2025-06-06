<x-app-layout>
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
