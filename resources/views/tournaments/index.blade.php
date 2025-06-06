<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($tournaments->isEmpty())
                <p class="text-gray-600 dark:text-gray-400 text-center">There are currently no tournaments available.</p>
            @else
                <livewire:tournament.edition-filter />
            @endif
        </div>
    </div>
</x-app-layout>
