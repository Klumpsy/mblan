@props(['edition' => null])

<div x-data="{ open: false }" class="relative inline-block">
    <button @click="open = true"
        class="flex items-center text-red-500 hover:text-red-700 dark:hover:text-red-400 ml-2 text-sm"
        x-tooltip="'Cancel your participation. You will lose access and tournament signups.'">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
        Cancel Participation
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                Confirm Cancellation
            </h2>
            <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">
                Are you sure you want to cancel your participation in this edition of MBLAN?
                <br><br>
                <strong>This action is permanent:</strong>
            <ul class="list-disc list-inside mt-2 space-y-1 text-primary-200">
                <li>Your access to the dashboard will be revoked.</li>
                <li>All your tournament signups will be deleted.</li>
                <li>You won’t be able to join any games or events.</li>
            </ul>
            </p>

            <div class="flex justify-end space-x-2 mt-4">
                <button @click="open = false"
                    class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
                    Cancel
                </button>

                <a href="{{ route('editions.signout', $edition->slug) }}"
                    class="px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded">
                    Confirm Leave
                </a>
            </div>
        </div>
    </div>
</div>
