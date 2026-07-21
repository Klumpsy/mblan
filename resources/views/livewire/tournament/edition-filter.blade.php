<div>
    <form class="flex items-center justify-end mb-4 mx-2 md:mx-0" wire:submit.prevent>
        <select wire:model="year" wire:change="loadTournaments"
            class="block w-full md:w-40 mt-1 bg-forge-graphite border-primary-500/25 text-white clip-corner focus:border-primary-400 focus:ring-0">
            @foreach ($selectOptions as $option)
                <option value="{{ $option }}">{{ $option }}</option>
            @endforeach
        </select>
    </form>

    <div class="space-y-10">
        <section class="metal-edge clip-corner p-6">
            <div class="mb-3">
                <span class="font-display text-xs uppercase tracking-[0.3em] text-primary-400">The Arena</span>
            </div>
            <h2 class="font-display text-2xl font-bold uppercase tracking-wide text-white mb-4">Active tournament</h2>

            @foreach ($tournaments as $tournament)
                @if ($tournament->is_active)
                    <livewire:tournament.section :tournament="$tournament" wire:key="key-{{ $tournament->id }}" />
                @endif
            @endforeach
        </section>

        <section class="metal-edge clip-corner p-6">
            <div class="mb-3">
                <span class="font-display text-xs uppercase tracking-[0.3em] text-primary-400">Next Up</span>
            </div>
            <h2 class="font-display text-2xl font-bold uppercase tracking-wide text-white mb-4">Upcoming tournaments</h2>
            <div class="grid w-100 h-100 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($tournaments as $tournament)
                    @if (!$tournament->is_active && $tournament->hasYetToStart())
                        <livewire:tournament.section :tournament="$tournament" inactive
                            wire:key="upcoming-key-{{ $tournament->id }}" />
                    @endif
                @endforeach
            </div>
        </section>

        <section class="metal-edge clip-corner p-6">
            <div class="mb-3">
                <span class="font-display text-xs uppercase tracking-[0.3em] text-primary-400">The Legacy</span>
            </div>
            <h2 class="font-display text-2xl font-bold uppercase tracking-wide text-white mb-4">Past tournaments</h2>
            <div class="grid w-100 h-100 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($tournaments as $tournament)
                    @if (!$tournament->is_active && !$tournament->hasYetToStart())
                        <livewire:tournament.section :tournament="$tournament" inactive
                            wire:key="past-key-{{ $tournament->id }}" />
                    @endif
                @endforeach
            </div>
        </section>
    </div>
</div>
