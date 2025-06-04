<div>
    <form class="flex items-center justify-end mb-4 mx-2 md:mx-0" wire:submit.prevent>
        <select wire:model="year" wire:change="loadTournaments"
            class="block w-full md:w-40 bg:white dark:text-white dark:bg-gray-800 mt-1 rounded-md shadow-sm border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
            @foreach ($selectOptions as $option)
                <option value="{{ $option }}">{{ $option }}</option>
            @endforeach
        </select>
    </form>

    <div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4 my-2">
            <h2 class="text-2xl text-primary-400 mb-3">Active tournament</h2>

            @foreach ($tournaments as $tournament)
                @if ($tournament->is_active)
                    <livewire:tournament-section :tournament="$tournament" wire:key="key-{{ $tournament->id }}" />
                @endif
            @endforeach
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4 my-2">
            <h2 class="text-2xl text-gray-600">Upcoming tournaments</h2>
            <div class="grid w-100 h-100 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($tournaments as $tournament)
                    @if (!$tournament->is_active && $tournament->hasYetToStart())
                        <livewire:tournament-section :tournament="$tournament" inactive
                            wire:key="upcoming-key-{{ $tournament->id }}" />
                    @endif
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-4">
            <h2 class="text-2xl text-gray-600">Past tournaments</h2>
            <div class="grid w-100 h-100 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($tournaments as $tournament)
                    @if (!$tournament->is_active && !$tournament->hasYetToStart())
                        <livewire:tournament-section :tournament="$tournament" inactive
                            wire:key="past-key-{{ $tournament->id }}" />
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
