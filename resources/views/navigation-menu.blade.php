<nav x-data="{ open: false }" class="sticky top-0 z-50 border-b border-primary-500/20 bg-forge-black/85 backdrop-blur">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex "> <!-- Added min-w-0 to prevent flex item from growing too large -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-mark class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links - Hidden on smaller screens, shown from lg up -->
                <div class="hidden lg:flex lg:space-x-6 xl:space-x-8 lg:-my-px lg:ms-6 xl:ms-10">
                    <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" class="whitespace-nowrap">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('games') }}" :active="str_starts_with(request()->path(), 'games')" class="whitespace-nowrap">
                        {{ __('Games') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('editions') }}" :active="str_starts_with(request()->path(), 'editions')" class="whitespace-nowrap">
                        {{ __('Editions') }}
                    </x-nav-link>
                    @can('viewPagesThatRequireSignup', auth()->user())
                        <x-nav-link href="{{ route('tournaments') }}" :active="request()->routeIs('tournaments')" class="whitespace-nowrap">
                            {{ __('Tournaments') }}
                        </x-nav-link>
                        <x-nav-link href="{{ route('media') }}" :active="request()->routeIs('media')" class="whitespace-nowrap">
                            {{ __('Media') }}
                        </x-nav-link>
                        <x-nav-link href="{{ route('blogs') }}" :active="request()->routeIs('blogs')" class="whitespace-nowrap">
                            {{ __('News') }}
                        </x-nav-link>
                    @endcan
                </div>
            </div>

            <!-- Right side - Desktop -->
            <div class="hidden lg:flex lg:items-center lg:space-x-4">
                <x-edition-switcher />

                @can('viewPagesThatRequireSignup', auth()->user())
                    <a href="{{ config('app.discord_server') }}" target="_blank" rel="noopener noreferrer"
                        class="flex-shrink-0">
                        <img src="{{ asset('images/discord.png') }}" alt="Discord"
                            class="h-8 w-8 object-contain hover:scale-105 ease-in-out cursor-pointer transition duration-300">
                    </a>
                @endcan

                <!-- Settings Dropdown -->
                <div class="relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <button
                                    class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                    <img class="h-8 w-8 rounded-full object-cover"
                                        src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}"
                                        alt="{{ Auth::user()->name }}" />
                                </button>
                            @else
                                <span class="inline-flex">
                                    <button type="button"
                                        class="inline-flex items-center px-3 py-2 border border-primary-500/20 clip-corner text-sm leading-4 font-display uppercase tracking-wider text-forge-steel bg-forge-graphite hover:text-primary-300 hover:border-primary-500/40 focus:outline-none transition ease-in-out duration-150">
                                        <span class="truncate max-w-[120px]">{{ Auth::user()->name }}</span>
                                        <svg class="ms-2 -me-0.5 h-4 w-4 flex-shrink-0"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </span>
                            @endif
                        </x-slot>

                        <x-slot name="content">
                            <!-- Account Management -->
                            <div class="block px-4 py-2 font-display text-xs uppercase tracking-widest text-primary-400/70">
                                {{ __('Manage Account') }}
                            </div>

                            <x-dropdown-link href="{{ route('profile.show') }}">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <x-dropdown-link href="{{ route('achievements') }}">
                                {{ __('Achievements') }}
                            </x-dropdown-link>

                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                    {{ __('API Tokens') }}
                                </x-dropdown-link>
                            @endif

                            <div class="border-t border-primary-500/15"></div>
                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf
                                <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Mobile/Tablet Right Side (md and below) -->
            <div class="flex items-center space-x-3 lg:hidden">
                @can('viewPagesThatRequireSignup', auth()->user())
                    <a href="{{ config('app.discord_server') }}" target="_blank" rel="noopener noreferrer"
                        class="flex-shrink-0">
                        <img src="{{ asset('images/discord.png') }}" alt="Discord"
                            class="h-7 w-7 sm:h-8 sm:w-8 object-contain hover:scale-105 ease-in-out cursor-pointer transition duration-300">
                    </a>
                @endcan

                <!-- Hamburger -->
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-forge-steel hover:text-primary-300 hover:bg-primary-500/10 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden lg:hidden">
        <div class="px-4 pt-3">
            <x-edition-switcher />
        </div>
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('games') }}" :active="str_starts_with(request()->path(), 'games')">
                {{ __('Games') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('editions') }}" :active="str_starts_with(request()->path(), 'editions')">
                {{ __('Editions') }}
            </x-responsive-nav-link>
            @can('viewPagesThatRequireSignup', auth()->user())
                <x-responsive-nav-link href="{{ route('tournaments') }}" :active="request()->routeIs('tournaments')">
                    {{ __('Tournaments') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('media') }}" :active="request()->routeIs('media')">
                    {{ __('Media') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('blogs') }}" :active="request()->routeIs('blogs')">
                    {{ __('News') }}
                </x-responsive-nav-link>
            @endcan
        </div>

        <div class="pt-4 pb-1 border-t border-primary-500/15">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0 me-3">
                        <img class="h-10 w-10 rounded-full object-cover"
                            src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}"
                            alt="{{ Auth::user()->name }}" />
                    </div>
                @endif

                <div class="min-w-0 flex-1">
                    <div class="font-display text-base uppercase tracking-wide text-white truncate">
                        {{ Auth::user()->name }}</div>
                    <div class="text-sm text-forge-steel/60 truncate">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link href="{{ route('achievements') }}" :active="request()->routeIs('achievements')">
                    {{ __('Achievements') }}
                </x-responsive-nav-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf
                    <x-responsive-nav-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
