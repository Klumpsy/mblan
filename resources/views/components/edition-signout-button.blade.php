@props(['edition' => null])

<div x-data="{ open: false }" class="relative inline-block">
    <button @click="open = true"
        class="flex items-center gap-1.5 font-display text-xs uppercase tracking-widest text-danger-400 transition-colors hover:text-danger-300 ml-2"
        x-tooltip="'Cancel your participation. You will lose access and tournament signups.'">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
        Cancel Participation
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-forge-black/80 backdrop-blur-sm p-6">
        <div class="glass metal-edge clip-corner-lg p-6 w-full max-w-md">
            <h2 class="mb-3 font-display text-lg font-bold uppercase tracking-wide text-white">
                Confirm Cancellation
            </h2>
            <p class="text-sm text-forge-steel/80 mb-4">
                Are you sure you want to cancel your participation in this edition of MBLAN?
                <br><br>
                <strong class="text-white">This action is permanent:</strong>
            <ul class="list-disc list-inside mt-2 space-y-1 text-forge-steel/70">
                <li>Your access to the dashboard will be revoked.</li>
                <li>All your tournament signups will be deleted.</li>
                <li>You won’t be able to join any games or events.</li>
            </ul>
            </p>

            <div class="flex justify-end space-x-3 mt-6">
                <button @click="open = false"
                    class="font-display text-xs uppercase tracking-widest metal-edge clip-corner px-4 py-2.5 text-forge-steel transition-colors hover:text-white">
                    Cancel
                </button>

                <form method="POST" action="{{ route('editions.signout', $edition->slug) }}">
                    @csrf()
                    <button
                        class="font-display text-xs uppercase tracking-widest clip-corner px-4 py-2.5 text-white bg-danger-600 transition-colors hover:bg-danger-500">
                        Confirm leave
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
