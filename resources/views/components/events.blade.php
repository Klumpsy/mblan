<div class="p-6 lg:p-8">
    <div class="mb-3">
        <span class="font-display text-xs uppercase tracking-[0.3em] text-primary-400">Jouw Evenementen</span>
    </div>
    <h2 class="font-display text-2xl font-bold uppercase tracking-wide text-white">
        Jouw Evenementen
    </h2>

    <p class="mt-4 mb-6 text-sm text-forge-steel/80 leading-relaxed">
        Je aankomende evenementen staan hieronder. Klik op een evenement voor meer details of om je aanmelding te beheren.
    </p>

    {{-- Show signup button if there's a latest edition, user hasn't signed up for it, and has access --}}
    @if (
        $latestEdition &&
            !$user->signups->contains('edition_id', $latestEdition->id) &&
            $latestEdition->hasExclusiveAccess($user))
        <div class="mb-6">
            <x-forge.btn href="{{ route('editions.signup', $latestEdition->slug) }}">
                Aanmelden voor {{ $latestEdition->name }}
            </x-forge.btn>
        </div>
    @endif

    {{-- Show message if no latest edition is available --}}
    @if (!$latestEdition)
        <div class="mb-6">
            <p class="text-sm text-forge-steel/70">
                Er zijn op dit moment geen actieve edities om je voor aan te melden. Kom later terug voor aankomende
                evenementen.
            </p>
        </div>
    @endif

    {{-- Show user's signups only for the latest edition if it exists --}}
    @if ($latestEdition && $user->signups->where('edition_id', $latestEdition->id)->isNotEmpty())
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($user->signups->where('edition_id', $latestEdition->id) as $signup)
                <div class="clip-corner metal-edge overflow-hidden transition-shadow duration-300 hover:shadow-glow-sm">
                    @if (!$signup->confirmed)
                        {{-- Unconfirmed signup card --}}
                        <div class="p-6 text-center">
                            <h3 class="mb-2 font-display text-lg font-bold uppercase tracking-wide text-white">
                                In Afwachting
                            </h3>
                            <p class="mb-4 text-sm text-forge-steel/70">
                                Je aanmelding wordt verwerkt. We laten het je weten zodra deze bevestigd is.
                            </p>
                            <div class="inline-flex items-center font-display text-xs uppercase tracking-[0.2em] text-warning-400">
                                <span class="mr-2 h-2 w-2 rounded-full bg-warning-400 animate-pulse"></span>
                                Verwerken
                            </div>
                        </div>
                    @else
                        {{-- Confirmed signup card --}}
                        <div class="relative h-48 bg-gradient-to-br from-forge-graphite to-forge-panel">
                            @if ($signup->edition->logo)
                                <img src="{{ asset('storage/' . $signup->edition->logo) }}"
                                    alt="{{ $signup->edition->name }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-forge-graphite font-display text-xs uppercase tracking-[0.2em] text-forge-steel/40">
                                    {{ $signup->edition->name }}
                                </div>
                            @endif
                        </div>

                        <div class="p-6">
                            <div class="mb-3 text-sm text-primary-300">
                                @if ($signup->has_paid)
                                    Je hebt voor dit evenement betaald.
                                @else
                                    Je gemiddelde kosten: €{{ number_format($signup->calculateCost(), 2) }}
                                    <span class="text-xs text-forge-steel/60">(Dit kan nog wijzigen en wordt berekend op basis van je
                                        keuzes)</span>
                                @endif
                            </div>

                            <div class="mb-3 flex items-start justify-between">
                                <h3 class="font-display text-lg font-bold uppercase tracking-wide text-white">
                                    {{ $signup->edition->name }}
                                </h3>
                                <span class="text-sm text-forge-steel/60">
                                    {{ $signup->edition->year }}
                                </span>
                            </div>

                            <div class="mb-4 space-y-3">
                                {{-- Schedule dates --}}
                                <div class="flex flex-wrap items-center text-sm">
                                    @foreach ($signup->schedules as $schedule)
                                        <span
                                            class="me-2 inline-flex flex-col items-center border-r border-primary-500/20 pr-2 text-primary-400 last:border-r-0">
                                            <span class="text-xs font-medium uppercase">
                                                {{ \Carbon\Carbon::parse($schedule->date)->format('D') }}
                                            </span>
                                            <span class="mt-1 text-xs">
                                                {{ \Carbon\Carbon::parse($schedule->date)->format('d-m-Y') }}
                                            </span>
                                        </span>
                                    @endforeach
                                </div>

                                {{-- Event options --}}
                                <div class="flex items-center text-sm">
                                    <div class="text-xs text-forge-steel/80">
                                        @if ($signup->stays_on_campsite)
                                            <span class="inline-block border-r border-primary-500/20 px-2 py-2 font-display uppercase tracking-wide text-primary-300">
                                                KAMPEREN
                                            </span>
                                        @endif
                                        @if ($signup->joins_barbecue)
                                            <span class="inline-block border-r border-primary-500/20 px-2 py-2 font-display uppercase tracking-wide text-primary-300">
                                                BBQ
                                            </span>
                                        @endif
                                        @if ($signup->joins_pizza)
                                            <span class="inline-block px-2 py-2 font-display uppercase tracking-wide text-primary-300">
                                                PIZZA
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- T-shirt info --}}
                                @if ($signup->wants_tshirt)
                                    <div class="flex items-center text-sm">
                                        <div class="text-xs text-forge-steel/80">
                                            <span class="inline-block border-r border-primary-500/20 px-2 py-2 font-display uppercase tracking-wide text-primary-300">
                                                T-shirt
                                            </span>
                                            @if ($signup->tshirt_size)
                                                <span class="inline-block border-r border-primary-500/20 px-2 py-2 font-display uppercase tracking-wide text-primary-300">
                                                    {{ $signup->tshirt_size }}
                                                </span>
                                            @endif
                                            @if ($signup->tshirt_text)
                                                <span class="inline-block px-2 py-2 font-display uppercase tracking-wide text-primary-300">
                                                    {{ $signup->tshirt_text }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Beverages --}}
                                <div class="flex items-start text-sm">
                                    @if ($signup->beverages->isEmpty())
                                        <span class="text-forge-steel/70">
                                            Je hebt geen voorkeursdranken opgegeven.
                                        </span>
                                    @else
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($signup->beverages as $beverage)
                                                <span class="inline-block font-display text-xs uppercase tracking-wide text-primary-300">
                                                    {{ $beverage->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <x-forge.btn variant="ghost" href="/editions/{{ $signup->edition->slug }}" class="w-full">
                                Bekijk Details
                            </x-forge.btn>

                            <div class="mt-4 flex w-full justify-center">
                                <x-edition-signout-button :edition="$signup->edition" />
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
