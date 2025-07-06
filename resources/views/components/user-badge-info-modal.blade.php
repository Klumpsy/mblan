@props(['user', 'size' => 'md'])

@php
    $sizes = [
        'sm' => 'w-8 h-8',
        'md' => 'w-12 h-12',
        'lg' => 'w-16 h-16',
    ];
    $avatarSize = $sizes[$size] ?? $sizes['md'];

    $initials = collect(explode(' ', $user->name))
        ->map(fn($word) => strtoupper(substr($word, 0, 1)))
        ->take(2)
        ->join('');

    $colors = [
        'bg-red-500',
        'bg-blue-500',
        'bg-green-500',
        'bg-yellow-500',
        'bg-purple-500',
        'bg-pink-500',
        'bg-indigo-500',
        'bg-teal-500',
    ];
    $colorIndex = abs(crc32($user->name)) % count($colors);
    $avatarColor = $colors[$colorIndex];
@endphp

<div x-data="{ open: false }" class="inline-block relative m-3">
    <!-- Avatar Button -->
    <button type="button" @click="open = true"
        class="group inline-flex items-center space-x-2 rounded-full transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
        <div class="relative flex-shrink-0 {{ $avatarSize }}">
            @if ($user->profile_photo_url)
                <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}"
                    class="{{ $avatarSize }} rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700 group-hover:ring-primary-400 transition-all duration-200">
            @else
                <div
                    class="{{ $avatarSize }} {{ $avatarColor }} rounded-full ring-2 ring-gray-200 dark:ring-gray-700 group-hover:ring-primary-400 flex items-center justify-center">
                    <span
                        class="text-white font-semibold {{ $size === 'sm' ? 'text-xs' : ($size === 'lg' ? 'text-lg' : 'text-sm') }}">
                        {{ $initials }}
                    </span>
                </div>
            @endif

        </div>

        @if ($size !== 'sm')
            <div class="hidden sm:block text-left">
                <p
                    class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-primary-400 transition-colors">
                    {{ $user->name }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ $user->role }}</p>
            </div>
        @endif
    </button>

    <!-- Modal -->
    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:p-0 bg-black/50"
        @keydown.escape.window="open = false" @click.self="open = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6 relative" @click.stop>
            <!-- Close Button -->
            <button @click="open = false"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Profile Header -->
            <div class="text-center mb-6">
                <div class="relative inline-block">
                    @if ($user->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}"
                            class="w-24 h-24 rounded-full object-cover ring-4 ring-primary-400 mx-auto">
                    @else
                        <div
                            class="w-24 h-24 {{ $avatarColor }} rounded-full ring-4 ring-primary-400 mx-auto flex items-center justify-center">
                            <span class="text-white font-bold text-xl">{{ $initials }}</span>
                        </div>
                    @endif
                    <div
                        class="absolute -bottom-2 -right-2 w-6 h-6 bg-green-500 rounded-full ring-2 ring-white dark:ring-gray-800">
                    </div>
                </div>
                <h3 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 capitalize">{{ $user->role }}</p>
                @if ($user->isAdmin())
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200 mt-2">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"
                                clip-rule="evenodd" />
                        </svg>
                        Admin
                    </span>
                @endif
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                @foreach ([['label' => 'Achievements', 'count' => $user->achievements_count ?? 0], ['label' => 'Tournaments', 'count' => $user->tournaments_count ?? 0], ['label' => 'Comments', 'count' => $user->blog_comments_count ?? 0], ['label' => 'Liked Games', 'count' => $user->liked_games_count ?? 0]] as $stat)
                    <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="text-2xl font-bold text-primary-400">{{ $stat['count'] }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-300">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>

            @if (!empty($user->likedGames))
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Latest Liked Games
                    </h4>
                    <div class="space-y-2">
                        @foreach ($user->likedGames as $game)
                            <div
                                class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                @if ($game->image_url)
                                    <img src="{{ $game->image_url }}" alt="{{ $game->name }}"
                                        class="w-10 h-10 rounded object-cover">
                                @else
                                    <div
                                        class="w-10 h-10 bg-gradient-to-br from-primary-400 to-primary-600 rounded flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $game->name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $game->category ?? 'Game' }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
