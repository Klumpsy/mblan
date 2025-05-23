<div class="max-w-2xl mx-auto p-6">
    <!-- Header -->
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">
            Sign up for {{ $edition->name }}
        </h2>
        <p class="text-gray-600">
            Hello {{ $user->name }}, let's get you registered!
        </p>
    </div>

    <!-- Step Indicator -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            @for ($i = 1; $i <= $totalSteps; $i++)
                <div class="flex items-center {{ $i < $totalSteps ? 'flex-1' : '' }}">
                    <!-- Step Circle -->
                    <div class="relative">
                        <div
                            class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-sm font-medium transition-all duration-200
                            {{ $currentStep >= $i
                                ? ($this->isStepCompleted($i)
                                    ? 'bg-green-500 border-green-500 text-white'
                                    : 'bg-blue-500 border-blue-500 text-white')
                                : 'bg-white border-gray-300 text-gray-500' }}">
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
                        <!-- Step Label -->
                        <div
                            class="absolute top-12 left-1/2 transform -translate-x-1/2 text-xs font-medium text-gray-600 whitespace-nowrap">
                            {{ $this->getStepTitle($i) }}
                        </div>
                    </div>

                    <!-- Progress Line -->
                    @if ($i < $totalSteps)
                        <div
                            class="flex-1 h-0.5 mx-4 transition-all duration-200
                            {{ $currentStep > $i ? 'bg-green-500' : 'bg-gray-300' }}">
                        </div>
                    @endif
                </div>
            @endfor
        </div>
    </div>

    <!-- Form Content -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 min-h-[400px]">

        <!-- Step 1: Select Days -->
        @if ($currentStep === 1)
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Which days would you like to participate?
                    </h3>
                    <p class="text-gray-600 mb-6">
                        Select all the days you plan to attend {{ $edition->name }}.
                    </p>
                </div>

                <div class="grid gap-3">
                    @forelse ($schedules as $schedule)
                        <label
                            class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors duration-200
                            {{ in_array($schedule->id, $selectedSchedules) ? 'border-blue-500 bg-blue-50' : '' }}">
                            <input type="checkbox" wire:model="selectedSchedules" value="{{ $schedule->id }}"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ $schedule->name }}</div>
                                @php
                                    $carbonDate = \Carbon\Carbon::parse($edition->date);
                                    $formattedDate = $carbonDate->format('D, M j');
                                @endphp
                                <div class="text-sm text-gray-500">{{ $formattedDate }}</div>
                            </div>
                        </label>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            No schedules available for this edition.
                        </div>
                    @endforelse
                </div>

                @error('selectedSchedules')
                    <div class="text-red-600 text-sm mt-2">{{ $message }}</div>
                @enderror
            </div>
        @endif

        <!-- Step 2: Preferences -->
        @if ($currentStep === 2)
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Your Preferences
                    </h3>
                    <p class="text-gray-600 mb-6">
                        Let us know about your accommodation and dining preferences.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg">
                        <input type="checkbox" wire:model="staysOnCampsite" id="campsite"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                        <div class="flex-1">
                            <label for="campsite" class="block font-medium text-gray-900 cursor-pointer">
                                Stay on campsite
                            </label>
                            <p class="text-sm text-gray-600 mt-1">
                                I would like to stay overnight at the event campsite during {{ $edition->name }}.
                            </p>
                        </div>
                    </div>

                    <!-- BBQ Option -->
                    <div class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg">
                        <input type="checkbox" wire:model="joinsBarbecue" id="bbq"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                        <div class="flex-1">
                            <label for="bbq" class="block font-medium text-gray-900 cursor-pointer">
                                Join the barbecue
                            </label>
                            <p class="text-sm text-gray-600 mt-1">
                                I would like to participate in the group barbecue event.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Step 3: Beverages -->
        @if ($currentStep === 3)
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Beverage Preferences
                    </h3>
                    <p class="text-gray-600 mb-6">
                        Select your preferred beverages. This helps us plan better for the event.
                    </p>
                </div>

                <div class="grid gap-3">
                    @forelse ($beverages as $beverage)
                        <label
                            class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors duration-200
                            {{ in_array($beverage->id, $selectedBeverages) ? 'border-blue-500 bg-blue-50' : '' }}">
                            <input type="checkbox" wire:model="selectedBeverages" value="{{ $beverage->id }}"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ $beverage->name }}</div>
                                @if ($beverage->description)
                                    <div class="text-sm text-gray-500">{{ $beverage->description }}</div>
                                @endif
                                @if ($beverage->contains_alcohol)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 mt-1">
                                        Contains Alcohol
                                    </span>
                                @endif
                            </div>
                        </label>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            No beverages available to select.
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>

    <!-- Navigation Buttons -->
    <div class="flex justify-between items-center mt-6">
        <button wire:click="previousStep" @if ($currentStep === 1) disabled @endif
            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
            Previous
        </button>

        <div class="text-sm text-gray-500">
            Step {{ $currentStep }} of {{ $totalSteps }}
        </div>

        @if ($currentStep < $totalSteps)
            <button wire:click="nextStep"
                class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                Next Step
            </button>
        @else
            <button wire:click="signup"
                class="px-6 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                Complete Signup
            </button>
        @endif
    </div>

    <!-- Progress Summary (Optional) -->
    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
        <h4 class="text-sm font-medium text-gray-900 mb-2">Summary:</h4>
        <div class="text-sm text-gray-600 space-y-1">
            @if (!empty($selectedSchedules))
                <div>📅 {{ count($selectedSchedules) }} day(s) selected</div>
            @endif
            @if ($staysOnCampsite)
                <div>🏕️ Staying on campsite</div>
            @endif
            @if ($joinsBarbecue)
                <div>🍖 Joining barbecue</div>
            @endif
            @if (!empty($selectedBeverages))
                <div>🥤 {{ count($selectedBeverages) }} beverage(s) selected</div>
            @endif
        </div>
    </div>
</div>
