<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-primary-400 leading-tight">
            {{ __('Media') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @forelse ($media as $item)
                    <div class="overflow-hidden rounded-lg shadow bg-white dark:bg-gray-800">
                        <img src="{{ Storage::disk('public')->url($item->file_path) }}" alt="Media Image"
                            class="w-full h-48 object-cover transition-transform duration-300 hover:scale-105">
                    </div>
                @empty
                    <p class="col-span-full text-center text-gray-500 dark:text-gray-400">
                        No media available.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
