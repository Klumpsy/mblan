<div class="mx-auto max-w-2xl">
    <x-forge.card>
        <div class="mb-6">
            <p class="font-pixel text-[9px] uppercase tracking-[0.2em] text-primary-400">MBLAN26</p>
            <h3 class="mt-1 font-display text-2xl font-bold uppercase tracking-wide text-white">Pizza bestellen</h3>
            <p class="mt-1 text-xs uppercase tracking-widest text-forge-steel/60">Van De Pyramiden. Geen pizza-mens? Kies iets anders van het menu.</p>
        </div>

        @if (! $round)
            <p class="rounded-lg border border-primary-500/15 bg-forge-black/40 p-4 text-sm text-forge-steel/70">
                Er is op dit moment geen bestelronde open. Zodra de organisatie er een opent, kun je hier je keuze doorgeven.
            </p>
        @else
            <p class="mb-4 text-sm text-forge-steel/70">
                Ronde: <span class="font-display text-primary-300">{{ $round->name }}</span>
            </p>

            <form wire:submit="save" class="space-y-5">
                <div>
                    <x-label for="pizza" value="Jouw keuze" />
                    <select id="pizza" wire:model="pizza"
                        class="mt-1 w-full rounded-lg border border-primary-500/20 bg-forge-black/60 px-3 py-2 text-sm text-forge-steel focus:border-primary-400 focus:ring-0">
                        <option value="">Kies van het menu...</option>
                        @foreach ($menu as $category => $items)
                            <optgroup label="{{ $category }}">
                                @foreach ($items as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <x-input-error for="pizza" class="mt-1" />
                </div>

                <div>
                    <x-label for="notes" value="Opmerkingen (optioneel)" />
                    <textarea id="notes" wire:model="notes" rows="3"
                        placeholder="Bijv. zonder ui, extra kaas, klein formaat..."
                        class="mt-1 w-full rounded-lg border border-primary-500/20 bg-forge-black/60 px-3 py-2 text-sm text-forge-steel focus:border-primary-400 focus:ring-0"></textarea>
                    <x-input-error for="notes" class="mt-1" />
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="btn-wood clip-corner text-[10px]">
                        {{ $myOrder ? 'Bestelling bijwerken' : 'Bestelling plaatsen' }}
                    </button>
                    @if ($myOrder)
                        <span class="font-pixel text-[8px] uppercase tracking-widest text-primary-300">
                            Huidige keuze: {{ $myOrder->pizza }}
                        </span>
                    @endif
                </div>
            </form>
        @endif
    </x-forge.card>

    @if ($round && $orders->isNotEmpty())
        <div class="mt-6">
            <x-forge.card>
                <h4 class="font-display text-lg font-bold uppercase tracking-wide text-white">Bestellingen</h4>
                <p class="mt-1 mb-4 font-pixel text-[8px] uppercase tracking-widest text-forge-steel/60">
                    {{ $orders->count() }} {{ $orders->count() === 1 ? 'bestelling' : 'bestellingen' }} in deze ronde
                </p>

                <ul class="divide-y divide-primary-500/10">
                    @foreach ($orders as $order)
                        <li class="flex items-start gap-3 py-2.5 {{ $order->user_id === auth()->id() ? 'text-white' : 'text-forge-steel/80' }}">
                            <img class="mt-0.5 h-6 w-6 shrink-0 rounded-full object-cover" src="{{ $order->user->profile_photo_url }}" alt="">
                            <div class="min-w-0">
                                <span class="font-display text-sm uppercase tracking-wider">{{ $order->user->name }}</span>
                                <span class="text-sm"> — {{ $order->pizza }}</span>
                                @if ($order->notes)
                                    <div class="text-xs text-forge-steel/60">{{ $order->notes }}</div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </x-forge.card>
        </div>
    @endif
</div>
