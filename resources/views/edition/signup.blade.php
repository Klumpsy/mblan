<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col items-center sm:items-center md:flex-row md:justify-between w-full">
            <h1 class="font-display text-xl font-bold uppercase tracking-wide text-white leading-tight mb-4 md:mb-0">
                Sign-up for {{ $edition->name }}
            </h1>
            <div class="flex flex-col items-center md:flex-row content-center gap-3">
                <div class="flex items-center text-sm uppercase tracking-widest text-forge-steel/70">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Participants: <span class="ml-1 font-display text-primary-300">{{ $edition->confirmedSignups()->count() }}</span>
                </div>
                <span
                    class="font-display text-xs uppercase tracking-widest metal-edge clip-corner px-3 py-1.5 text-forge-steel/80">
                    {{ $edition->year }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-6">
            <div class="flex justify-between mb-8">
                <x-forge.btn href="{{ route('editions.show', $edition->slug) }}" variant="ghost" class="!px-5 !py-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to {{ $edition->name }} overview
                </x-forge.btn>
            </div>

            <div x-data x-reveal>
                <x-forge.card class="mb-6">
                    <div class="flex flex-col md:flex-row md:items-center">
                        <div class="flex-grow">
                            <h2 class="mb-3 font-display text-2xl font-bold uppercase tracking-wide text-white">{{ $edition->name }}</h2>
                            <p class="text-forge-steel/80">
                                Perfect! You decided to sign up for {{ $edition->name }}, let's fill in some information and
                                make this an unforgettable experience!

                                We'll need to know which days you're planning to join us, whether you'd like to stay at our
                                cozy campsite under the stars, and of course - we want to make sure you're well-fed and
                                hydrated! Tell us about your BBQ preferences and favorite beverages so we can prepare the
                                perfect feast for everyone.

                                Let's get started on your adventure!
                            </p>
                        </div>
                    </div>
                </x-forge.card>
            </div>

            <div x-data x-reveal.100>
                <x-forge.card class="!p-0">
                    <div class="flex flex-col md:flex-row md:items-center p-4 md:p-6">
                        <livewire:edition.signup :edition="$edition" />
                    </div>
                </x-forge.card>
            </div>
        </div>
    </div>
</x-app-layout>
