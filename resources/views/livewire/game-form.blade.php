<div class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">

    @if (session()->has('success'))
        <div class="text-green-500 mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="save">
        <div class="mb-4">
            <label class="text-white">Name</label>
            <input wire:model="name" type="text" class="w-full rounded dark:bg-gray-700 dark:text-white" />
            @error('name')
                <span class="text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="text-white">Description</label>
            <textarea wire:model="description" rows="3" class="w-full rounded dark:bg-gray-700 dark:text-white"></textarea>
        </div>

        <div class="mb-4">
            <label class="text-white">Year of Release</label>
            <input wire:model="year_of_release" type="number"
                class="w-full rounded dark:bg-gray-700 dark:text-white" />
        </div>

        <div class="mb-4">
            <label class="text-white">Website</label>
            <input wire:model="linkToWebsite" type="url" class="w-full rounded dark:bg-gray-700 dark:text-white" />
        </div>

        <div class="mb-4">
            <label class="text-white">YouTube</label>
            <input wire:model="linkToYoutube" type="url" class="w-full rounded dark:bg-gray-700 dark:text-white" />
        </div>

        <div class="mb-4">
            <label class="text-white">Upload Image</label>
            <input wire:model="image" type="file" class="w-full text-white" />
            @error('image')
                <span class="text-red-500">{{ $message }}</span>
            @enderror

            @if ($image)
                <div class="mt-4">
                    <p class="text-sm text-gray-300">Image Preview:</p>
                    <img src="{{ $image->temporaryUrl() }}" class="h-32 rounded shadow mt-2" />
                </div>
            @endif
        </div>

        <div class="flex mt-4">
            <button type="submit" class="btn btn-success me-2">
                Create Game
            </button>
            <a href="/games" class="btn btn-default">
                back to games
            </a>
        </div>
    </form>
</div>
