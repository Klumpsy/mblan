<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col items-center sm:items-center md:flex-row md:justify-between w-full">
            <h1 class="font-semibold text-xl text-gray-800 dark:text-primary-400 leading-tight mb-4 md:mb-0">
                Sign-up for {{ $edition->name }}
            </h1>
            <div class="flex flex-col items-center md:flex-row content-center">
                <div class="me-3 flex items-center text-sm text-gray-500 dark:text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Participants: {{ $edition->participants->count() }}
                </div>
                <span
                    class="text-sm bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-400 px-3 py-1 rounded-full">
                    {{ $edition->year }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between mb-6">
                <a href="{{ route('editions.show', $edition->slug) }}"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to {{ $edition->name }} overview
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="flex flex-col md:flex-row md:items-center p-4 md:p-6">
                    <div class="flex-grow">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $edition->name }}</h2>
                        <span class="text-gray-600 dark:text-gray-400">
                            Perfect! You decided to sign up for {{ $edition->name }}, let's fill in some information and
                            make this an unforgettable experience!

                            We'll need to know which days you're planning to join us, whether you'd like to stay at our
                            cozy campsite under the stars, and of course - we want to make sure you're well-fed and
                            hydrated! Tell us about your BBQ preferences and favorite beverages so we can prepare the
                            perfect feast for everyone.

                            Let's get started on your adventure!
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="flex flex-col md:flex-row md:items-center p-4 md:p-6">
                    <livewire:edition-signup :edition="$edition" />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
