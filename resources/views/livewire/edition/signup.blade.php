<div class="max-w-2xl mx-auto w-full p-2 sm:p-6">
    <div class="text-center mb-8">
        <h2 class="mb-2 font-display text-2xl font-bold uppercase tracking-wide text-white">
            Sign up for {{ $edition->name }}
        </h2>
        <p class="text-forge-steel/80">
            Hello {{ $user->name }}, let's get you registered!
        </p>
    </div>

    <div class="mb-8">
        <div class="flex items-center justify-between">
            @for ($i = 1; $i <= $totalSteps; $i++)
                <div class="flex items-center {{ $i < $totalSteps ? 'flex-1' : '' }}">
                    <div class="relative">
                        <div
                            class="w-10 h-10 rounded-full border-2 flex items-center justify-center font-display text-sm font-medium transition-all duration-200
                            {{ $currentStep >= $i
                                ? ($this->isStepCompleted($i)
                                    ? 'bg-primary-500 border-primary-500 text-forge-black shadow-glow-sm'
                                    : 'bg-primary-500 border-primary-500 text-forge-black')
                                : 'bg-forge-panel border-primary-500/25 text-forge-steel/60' }}">
                            @if ($this->isStepCompleted($i) && $currentStep > $i)
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            @else
                                {{ $i }}
                            @endif
                        </div>
                        <div
                            class="absolute top-12 left-1/2 transform -translate-x-1/2 font-display text-xs uppercase tracking-wider text-forge-steel/70 whitespace-nowrap">
                            {{ $this->getStepTitle($i) }}
                        </div>
                    </div>

                    @if ($i < $totalSteps)
                        <div
                            class="flex-1 h-0.5 mx-4 transition-all duration-200
                            {{ $currentStep > $i ? 'bg-primary-500' : 'bg-primary-500/20' }}">
                        </div>
                    @endif
                </div>
            @endfor
        </div>
    </div>

    <div class="clip-corner metal-edge p-6 min-h-[400px] mt-10">

        @if ($currentStep === 1)
            <div class="space-y-6">
                <div>
                    <h3 class="mb-4 font-display text-lg font-semibold uppercase tracking-wide text-white">
                        Which days would you like to participate?
                    </h3>
                    <p class="text-forge-steel/70 mb-6">
                        Select all the days you plan to attend {{ $edition->name }}.
                    </p>
                </div>

                <div class="grid gap-3">
                    @forelse ($schedules as $schedule)
                        <label
                            class="flex items-center space-x-3 p-4 clip-corner border cursor-pointer transition-colors duration-200
                            {{ in_array($schedule->id, $selectedSchedules) ? 'border-primary-500 bg-primary-500/15' : 'border-primary-500/20 bg-forge-panel/40 hover:bg-forge-panel/70' }}">
                            <input type="checkbox" wire:model="selectedSchedules" value="{{ $schedule->id }}"
                                class="h-4 w-4 text-primary-500 focus:ring-primary-500 border-primary-500/40 bg-forge-panel rounded">
                            <div class="flex-1">
                                <div class="font-display uppercase tracking-wide text-white">{{ $schedule->name }}</div>
                                @php
                                    $carbonDate = \Carbon\Carbon::parse($schedule->date);
                                    $formattedDate = $carbonDate->format('D, M j');
                                @endphp

                                <div class="text-sm text-forge-steel/60">{{ $formattedDate }}
                                    (€{{ number_format(\App\Models\Signup::COSTS_PER_DAY, 2) }})
                                </div>
                            </div>
                        </label>
                    @empty
                        <div class="text-center py-8 text-sm uppercase tracking-widest text-forge-steel/60">
                            No schedules available for this edition.
                        </div>
                    @endforelse
                </div>

                @error('selectedSchedules')
                    <div class="text-danger-400 text-sm mt-2">{{ $message }}</div>
                @enderror
            </div>
        @endif

        @if ($currentStep === 2)
            <div class="space-y-6">
                <div>
                    <h3 class="mb-4 font-display text-lg font-semibold uppercase tracking-wide text-white">
                        Your Preferences
                    </h3>
                    <p class="text-forge-steel/70 mb-6">
                        Let us know about your accommodation and dining preferences.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-start space-x-3 p-4 clip-corner border border-primary-500/20 bg-forge-panel/40">
                        <input type="checkbox" wire:model="staysOnCampsite" id="campsite"
                            class="h-4 w-4 text-primary-500 focus:ring-primary-500 border-primary-500/40 bg-forge-panel rounded mt-1">
                        <div class="flex-1">
                            <label for="campsite" class="block font-display uppercase tracking-wide text-white cursor-pointer">
                                Stay on campsite
                            </label>
                            <p class="text-sm text-forge-steel/70 mt-1">
                                I would like to stay overnight at the event campsite during {{ $edition->name }}.
                            </p>
                        </div>
                    </div>

                    @if ($joinsOnFriday)
                        <div class="flex items-start space-x-3 p-4 clip-corner border border-primary-500/20 bg-forge-panel/40">
                            <input type="checkbox" wire:model.live="joinsPizza" id="pizza"
                                class="h-4 w-4 text-primary-500 focus:ring-primary-500 border-primary-500/40 bg-forge-panel rounded mt-1">
                            <div class="flex-1">
                                <label for="pizza" class="block font-display uppercase tracking-wide text-white cursor-pointer">
                                    Join the pizza on Friday (€{{ number_format(\App\Models\Signup::PIZZA_COST, 2) }})
                                </label>
                                <p class="text-sm text-forge-steel/70 mt-1">
                                    I would like to have a pizza ordered on Friday.
                                </p>
                            </div>
                        </div>
                    @endif


                    @if ($joinsOnSaturday)
                        <div class="flex items-start space-x-3 p-4 clip-corner border border-primary-500/20 bg-forge-panel/40">
                            <input type="checkbox" wire:model.live="joinsBarbecue" id="bbq"
                                class="h-4 w-4 text-primary-500 focus:ring-primary-500 border-primary-500/40 bg-forge-panel rounded mt-1">
                            <div class="flex-1">
                                <label for="bbq" class="block font-display uppercase tracking-wide text-white cursor-pointer">
                                    Join the barbecue on Saturday
                                    (€{{ number_format(\App\Models\Signup::BBQ_COST, 2) }})
                                    <span />
                                </label>
                                <p class="text-sm text-forge-steel/70 mt-1">
                                    I would like to participate in the group barbecue event.
                                </p>
                            </div>
                        </div>
                    @endif

                </div>

                @if ($joinsBarbecue)
                    <div>
                        <div class="flex items-start space-x-3 p-4 clip-corner">
                            <input type="checkbox" wire:model="isVegan" id="vegan"
                                class="h-4 w-4 text-primary-500 focus:ring-primary-500 border-primary-500/40 bg-forge-panel rounded">
                            <div class="flex-1">
                                <label for="vegan" class="block font-display uppercase tracking-wide text-white cursor-pointer">
                                    Are you vegan?
                                </label>
                                <p class="text-sm text-forge-steel/70 mt-1">
                                    I would like vegan options for the BBQ
                                </p>
                                @error('isVegan')
                                    <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        @endif

        @if ($currentStep === 3)
            <div class="space-y-6">
                <div>
                    <h3 class="mb-4 font-display text-lg font-semibold uppercase tracking-wide text-white">
                        Beverage Preferences
                    </h3>
                    <p class="text-forge-steel/70 mb-6">
                        Select your preferred beverages. This helps us plan better for the event.
                    </p>
                </div>

                <div class="grid gap-3">
                    @forelse ($beverages as $beverage)
                        <label
                            class="flex items-center space-x-3 p-4 clip-corner border cursor-pointer transition-colors duration-200
                            {{ in_array($beverage->id, $selectedBeverages) ? 'border-primary-500 bg-primary-500/15' : 'border-primary-500/20 bg-forge-panel/40 hover:bg-forge-panel/70' }}">
                            <input type="checkbox" wire:model="selectedBeverages" value="{{ $beverage->id }}"
                                class="h-4 w-4 text-primary-500 focus:ring-primary-500 border-primary-500/40 bg-forge-panel rounded">
                            <div class="flex-1">
                                <div class="font-display uppercase tracking-wide text-white">{{ $beverage->name }}</div>
                                @if ($beverage->description)
                                    <div class="text-sm text-forge-steel/60">{!! $beverage->description !!}</div>
                                @endif
                                @if ($beverage->contains_alcohol)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 clip-corner text-xs font-display uppercase tracking-wider border border-warning-500/30 bg-warning-500/10 text-warning-400 mt-1">
                                        Contains Alcohol
                                    </span>
                                @endif
                            </div>
                        </label>
                    @empty
                        <div class="text-center py-8 text-sm uppercase tracking-widest text-forge-steel/60">
                            No beverages available to select.
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        @if ($currentStep === 4)
            <div class="space-y-6">
                <div>
                    <h3 class="mb-4 font-display text-lg font-semibold uppercase tracking-wide text-white">
                        Get your {{ $edition->name }} T-Shirt
                    </h3>
                    <p class="text-forge-steel/70 mb-6">
                        If you would like to wear {{ $edition->name }} merch, select this
                        option. A T-Shirt costs €25 and you can customize the text to give yourself a cool LAN title!
                    </p>
                </div>

                <div class="space-y-4">
                    <label
                        class="flex items-center space-x-3 p-4 clip-corner border border-primary-500/20 bg-forge-panel/40 cursor-pointer transition-colors duration-200 hover:bg-forge-panel/70">
                        <input type="checkbox" wire:model.live="wantsTshirt"
                            class="h-4 w-4 text-primary-500 focus:ring-primary-500 border-primary-500/40 bg-forge-panel rounded">
                        <div class="flex-1">
                            <div class="font-display uppercase tracking-wide text-white">Yes, I want a T-Shirt! (€25)</div>
                            <div class="text-sm text-forge-steel/60">Get your personalized {{ $edition->name }} T-Shirt</div>
                        </div>
                    </label>
                </div>

                @if ($wantsTshirt)
                    <div class="space-y-4 p-4 clip-corner border border-primary-500/20 bg-forge-panel/40">

                        <div>
                            <label class="block font-display text-sm uppercase tracking-wide text-forge-steel mb-2">
                                T-Shirt Size
                            </label>
                            <select wire:model="tshirtSize"
                                class="w-full px-3 py-2 clip-corner border border-primary-500/25 bg-forge-panel text-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                @foreach (App\Enums\TshirtSizeType::cases() as $size)
                                    <option value="{{ $size->value }}">{{ $size->value }}</option>
                                @endforeach
                            </select>
                            @error('tshirtSize')
                                <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block font-display text-sm uppercase tracking-wide text-forge-steel mb-2">
                                Custom Text (Optional)
                            </label>
                            <input type="text" wire:model.live="tshirtText"
                                placeholder="Enter your LAN title (max 20 characters)" maxlength="20"
                                class="w-full px-3 py-2 clip-corner border border-primary-500/25 bg-forge-panel text-white placeholder-forge-steel/40 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <p class="mt-1 text-xs text-forge-steel/50">
                                Characters: {{ strlen($tshirtText) }}/20
                            </p>
                            @error('tshirtText')
                                <p class="mt-1 text-sm text-danger-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="flex justify-between items-center mt-6">
        <button wire:click="previousStep" @if ($currentStep === 1) disabled @endif
            class="px-4 py-2.5 clip-corner metal-edge font-display text-xs uppercase tracking-widest text-forge-steel transition-colors hover:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:opacity-40 disabled:cursor-not-allowed">
            Previous
        </button>

        <div class="font-display text-xs uppercase tracking-widest text-forge-steel/60">
            Step {{ $currentStep }} of {{ $totalSteps }}
        </div>

        @if ($currentStep < $totalSteps)
            <button wire:key="next-step-{{ $currentStep }}" wire:click="nextStep"
                class="px-6 py-2.5 clip-corner bg-primary-500 font-display text-xs uppercase tracking-widest text-forge-black transition-all hover:bg-primary-400 hover:shadow-glow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                Next Step
            </button>
        @else
            <button wire:key="complete-signup-{{ $currentStep }}" wire:click="signup"
                class="px-6 py-2.5 clip-corner bg-primary-500 font-display text-xs uppercase tracking-widest text-forge-black transition-all hover:bg-primary-400 hover:shadow-glow focus:outline-none focus:ring-2 focus:ring-primary-500">
                Complete Signup
            </button>
        @endif
    </div>

    <div class="mt-6 p-4 clip-corner metal-edge">
        <h4 class="mb-2 font-display text-sm uppercase tracking-wide text-white">Summary:</h4>
        <div class="text-sm text-forge-steel/70 space-y-1">
            @if (!empty($selectedSchedules))
                <div>📅 {{ implode(', ', $this->selectedScheduleNames) }}</div>
            @endif
            @if ($joinsPizza)
                <div>🍕 Eating pizza on Friday</div>
            @endif
            @if ($staysOnCampsite)
                <div>🏕️ Staying on campsite</div>
            @endif
            @if ($joinsBarbecue)
                <div>🍖 Joining barbecue on Saturday @if ($isVegan)
                        (vegan)
                    @endif
                </div>
            @endif
            @if (!empty($selectedBeverages))
                <div>🥤 {{ implode(', ', $this->selectedBeverageNames) }}</div>
            @endif
            @if ($wantsTshirt)
                <div>👕 Rocking the {{ $edition->name }} T-Shirt</div>
            @endif
        </div>
    </div>
</div>
