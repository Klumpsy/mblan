<div>
    <form class="flex items-center justify-end mb-4" wire:submit.prevent>
        <select wire:model="year" wire:change="loadTournaments"
            class="block w-full mt-1 rounded-md shadow-sm border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
            <option value="">All Years</option>
            <option value="2024">2024</option>
            <option value="2025">2025</option>
        </select>
    </form>

    <div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4 my-2">
            <h2 class="text-2xl text-primary-400 mb-3">Active tournament</h2>
            @foreach ($tournaments as $tournament)
                @if ($tournament->is_active)
                    <x-tournament-section :tournament="$tournament" />
                @endif
            @endforeach
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
            <h2 class="text-2xl text-gray-600">Upcoming tournaments</h2>
            <div class="grid w-100 h-100 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($tournaments as $tournament)
                    @if (!$tournament->is_active)
                        <x-tournament-section-inactive :tournament="$tournament" />
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
