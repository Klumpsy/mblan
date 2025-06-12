<x-app-layout>


    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Your Achievements</h3>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                        @foreach ($achievements as $achievement)
                            @php
                                $pivot = $achievement->users->first()?->pivot;
                                $unlocked = (bool) $pivot?->achieved_at;
                                $progress = $pivot?->progress ?? 0;
                                $threshold = $achievement->threshold;
                            @endphp

                            <div class="bg-gray-100 dark:bg-gray-700 rounded-xl p-4 flex flex-col items-center text-center shadow-sm transition transform hover:scale-[1.02] "
                                style="border: 2px solid {{ $unlocked ? $achievement->color : 'transparent' }};">
                                <img src="{{ Storage::url($achievement->icon_path) }}" alt="{{ $achievement->name }}"
                                    class="w-16 h-16 mb-2 rounded-md
                                        {{ $unlocked ? '' : 'grayscale opacity-40' }}"
                                    style="{{ $unlocked ? 'filter: drop-shadow(0 0 4px ' . $achievement->color . ');' : '' }}">

                                <h4 class="text-md font-semibold text-gray-900 dark:text-white">
                                    {{ $achievement->name }}
                                </h4>

                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                    {{ $achievement->description }}
                                </p>

                                @if ($threshold)
                                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        Progress: {{ $unlocked ? $threshold : $progress }} / {{ $threshold }}
                                    </div>
                                @endif

                                @if ($unlocked)
                                    <span
                                        class="mt-2 text-green-700 dark:text-green-400 text-xs font-medium bg-green-100 dark:bg-green-900 px-2 py-0.5 rounded-full">
                                        Unlocked
                                    </span>
                                @else
                                    <span
                                        class="mt-2 text-gray-500 dark:text-gray-400 text-xs font-medium bg-gray-200 dark:bg-gray-600 px-2 py-0.5 rounded-full">
                                        Locked
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if ($achievements->isEmpty())
                        <div class="text-center text-gray-500 dark:text-gray-400 mt-6">
                            You haven’t earned any achievements yet — keep playing!
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
