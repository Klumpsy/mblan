<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 sm:px-8 bg-white border-b border-gray-200">
                <div class="text-2xl font-bold mb-4">
                    LAN Party Registration
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 px-4 py-3 rounded relative bg-green-100 border border-green-400 text-green-700">
                        {{ session('message') }}
                    </div>
                @endif

                @if ($registrationSubmitted)
                    <div
                        class="mb-6 p-4 border rounded-lg
                        @if ($registrationStatus === 'pending') bg-yellow-50 border-yellow-300 text-yellow-800
                        @elseif ($registrationStatus === 'approved') bg-green-50 border-green-300 text-green-800
                        @elseif ($registrationStatus === 'rejected') bg-red-50 border-red-300 text-red-800 @endif">

                        @if ($registrationStatus === 'pending')
                            <h3 class="text-lg font-semibold mb-2">Your registration is pending approval</h3>
                            <p>We'll notify you once your registration has been reviewed by our team.</p>
                        @elseif ($registrationStatus === 'approved')
                            <h3 class="text-lg font-semibold mb-2">Your registration has been approved!</h3>
                            @if ($showPaymentButton)
                                <p class="mb-4">You can now complete your payment to secure your spot at the LAN party.
                                </p>
                                <button wire:click="initiatePayment"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-300 disabled:opacity-25 transition">
                                    Pay with iDEAL
                                </button>
                            @else
                                <p>Your payment has been received. You're all set for the event!</p>
                            @endif
                        @elseif ($registrationStatus === 'rejected')
                            <h3 class="text-lg font-semibold mb-2">Your registration was not approved</h3>
                            <p>Unfortunately, we couldn't approve your registration. Please contact us for more
                                information.</p>
                        @endif
                    </div>
                @endif

                @if (!$registrationSubmitted || $registrationStatus === 'pending')
                    <form wire:submit.prevent="submitRegistration">
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Which days will you
                                attend?</label>
                            @foreach ($availableDays as $key => $day)
                                <label class="inline-flex items-center mt-2 mr-4">
                                    <input type="checkbox" wire:model="attendanceDays" value="{{ $key }}"
                                        class="form-checkbox h-5 w-5 text-blue-600">
                                    <span class="ml-2 text-gray-700">{{ $day }}</span>
                                </label>
                            @endforeach
                            @error('attendanceDays')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <input type="checkbox" wire:model="stayingForCamping"
                                    class="form-checkbox h-5 w-5 text-blue-600">
                                <span class="ml-2">I'll be staying at the camping site</span>
                            </label>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dietary Requirements</label>
                            <textarea wire:model="dietaryRequirements" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                placeholder="Vegetarian, vegan, allergies, etc."></textarea>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Equipment You're
                                Bringing</label>
                            <textarea wire:model="equipment" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                placeholder="PC specs, monitors, peripherals, etc."></textarea>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                            <textarea wire:model="additionalNotes" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                placeholder="Any other information you'd like us to know"></textarea>
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-300 disabled:opacity-25 transition">
                                Submit Registration
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
