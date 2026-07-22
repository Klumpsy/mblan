@props(['edition' => null])

<div x-data="{ open: false }" class="relative inline-block">
    <button @click="open = true"
        class="flex items-center gap-1.5 font-display text-xs uppercase tracking-widest text-danger-400 transition-colors hover:text-danger-300 ml-2"
        x-tooltip="'Annuleer je deelname. Je verliest toegang en je toernooiaanmeldingen.'">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
        Deelname Annuleren
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-forge-black/80 backdrop-blur-sm p-6">
        <div class="glass metal-edge clip-corner-lg p-6 w-full max-w-md">
            <h2 class="mb-3 font-display text-lg font-bold uppercase tracking-wide text-white">
                Annulering Bevestigen
            </h2>
            <p class="text-sm text-forge-steel/80 mb-4">
                Weet je zeker dat je je deelname aan deze editie van MBLAN wilt annuleren?
                <br><br>
                <strong class="text-white">Deze actie is definitief:</strong>
            <ul class="list-disc list-inside mt-2 space-y-1 text-forge-steel/70">
                <li>Je toegang tot het dashboard wordt ingetrokken.</li>
                <li>Al je toernooiaanmeldingen worden verwijderd.</li>
                <li>Je kunt niet meer deelnemen aan games of evenementen.</li>
            </ul>
            </p>

            <div class="flex justify-end space-x-3 mt-6">
                <button @click="open = false"
                    class="font-display text-xs uppercase tracking-widest metal-edge clip-corner px-4 py-2.5 text-forge-steel transition-colors hover:text-white">
                    Annuleren
                </button>

                <form method="POST" action="{{ route('editions.signout', $edition->slug) }}">
                    @csrf()
                    <button
                        class="font-display text-xs uppercase tracking-widest clip-corner px-4 py-2.5 text-white bg-danger-600 transition-colors hover:bg-danger-500">
                        Bevestig annulering
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
