<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @forelse ($media as $item)
                    <div x-data
                        @click="$dispatch('open-modal', { 
                            image: '{{ Storage::disk('public')->url($item->file_path) }}',
                            tags: {{ json_encode($item->tags ?? []) }}
                        })"
                        class="overflow-hidden rounded-lg shadow bg-white dark:bg-gray-800 cursor-pointer transition-opacity duration-500 ease-in-out"
                        x-intersect.once="$el.classList.remove('opacity-0')">
                        <template x-if="$el.classList.contains('opacity-0') === false">
                            <img src="{{ Storage::disk('public')->url($item->file_path) }}" alt="Media Image"
                                class="w-full h-48 object-cover transform transition-transform duration-300 hover:scale-105 opacity-0"
                                x-init="$el.onload = () => $el.classList.remove('opacity-0')">
                        </template>
                    </div>
                @empty
                    <p class="col-span-full text-center text-gray-500 dark:text-gray-400">
                        No media available.
                    </p>
                @endforelse
            </div>
        </div>
    </div>

    <div x-data="{ open: false, image: '', tags: [] }"
        @open-modal.window="open = true; image = $event.detail.image; tags = $event.detail.tags" x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
        <div class="bg-white dark:bg-gray-900 p-3 rounded-lg max-w-3xl w-full relative" @click.outside="open = false">
            <button @click="open = false"
                class="absolute top-2 right-2 text-gray-700 dark:text-gray-300 hover:text-red-500"
                aria-label="Close Modal">
                ✕
            </button>

            <div class="flex flex-col items-center">
                <img :src="image" alt="Full Image" class="max-h-[70vh] w-auto mb-4 rounded shadow-lg">
                <div class="flex justify-center space-x-4 mb-4">
                    <template x-for="tag in tags" :key="tag">
                        <span
                            class="inline-block bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-1 rounded-full text-sm"
                            x-text="tag"></span>
                    </template>
                </div>
                <div class="flex justify-center">
                    <a :href="image" download
                        class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Download Image
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
