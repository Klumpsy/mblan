<div x-data="{ open: false }"
    x-effect="document.documentElement.style.overflow = open ? 'hidden' : ''"
    @keydown.escape.window="open = false"
    @resize.window="if (window.innerWidth >= 1024) open = false">
<nav class="sticky top-0 z-50 border-b border-primary-500/20 bg-forge-black/85 backdrop-blur">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('schedule') }}">
                        <x-application-mark class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden lg:flex lg:space-x-8 lg:-my-px lg:ms-10">
                    <x-nav-link href="{{ route('schedule') }}" :active="request()->routeIs('schedule')" class="whitespace-nowrap">
                        {{ __('Schema') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('tournaments') }}" :active="request()->routeIs('tournaments')" class="whitespace-nowrap">
                        {{ __('Leaderboard') }}
                    </x-nav-link>
                    {{-- Mega menu: pages that only matter during the LAN itself. --}}
                    @php($lanActive = request()->routeIs('news.*') || request()->routeIs('timeline') || request()->routeIs('live') || request()->routeIs('pizza'))
                    <div x-data="{ lan: false }" class="relative flex"
                        @mouseenter="lan = true" @mouseleave="lan = false"
                        @keydown.escape.window="lan = false" @click.outside="lan = false">
                        <button type="button" @click="lan = true"
                            class="inline-flex items-center gap-1.5 whitespace-nowrap border-b-2 px-1 pt-1 font-pixel text-[9px] uppercase tracking-wider leading-5 transition duration-150 ease-in-out focus:outline-none {{ $lanActive ? 'border-primary-400 text-white' : 'border-transparent text-forge-steel hover:border-primary-500/40 hover:text-primary-300' }}">
                            {{ __('Tijdens de LAN') }}
                            <svg class="h-3 w-3 transition-transform" :class="lan && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div x-show="lan" x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="absolute left-1/2 top-full z-50 w-[26rem] -translate-x-1/2 pt-3">
                            <div class="frame-wood grid grid-cols-2 gap-2 bg-forge-black/95 p-3 backdrop-blur">
                                <a href="{{ route('news.index') }}" @click="lan = false"
                                    class="group clip-corner block px-4 py-3 transition hover:bg-primary-500/10 {{ request()->routeIs('news.*') ? 'bg-primary-500/10' : '' }}">
                                    <span class="font-pixel text-[9px] uppercase tracking-wider {{ request()->routeIs('news.*') ? 'text-primary-300' : 'text-white' }} group-hover:text-primary-300">{{ __('Nieuws') }}</span>
                                    <span class="mt-1 block text-xs text-forge-steel/70">Updates en aankondigingen</span>
                                </a>
                                <a href="{{ route('timeline') }}" @click="lan = false"
                                    class="group clip-corner block px-4 py-3 transition hover:bg-primary-500/10 {{ request()->routeIs('timeline') ? 'bg-primary-500/10' : '' }}">
                                    <span class="font-pixel text-[9px] uppercase tracking-wider {{ request()->routeIs('timeline') ? 'text-primary-300' : 'text-white' }} group-hover:text-primary-300">{{ __('Tijdlijn') }}</span>
                                    <span class="mt-1 block text-xs text-forge-steel/70">Foto's van de vloer</span>
                                </a>
                                <a href="{{ route('live') }}" @click="lan = false"
                                    class="group clip-corner block px-4 py-3 transition hover:bg-primary-500/10 {{ request()->routeIs('live') ? 'bg-primary-500/10' : '' }}">
                                    <span class="font-pixel text-[9px] uppercase tracking-wider {{ request()->routeIs('live') ? 'text-primary-300' : 'text-white' }} group-hover:text-primary-300">{{ __('Live') }}</span>
                                    <span class="mt-1 block text-xs text-forge-steel/70">Stream en chat</span>
                                </a>
                                <a href="{{ route('pizza') }}" @click="lan = false"
                                    class="group clip-corner block px-4 py-3 transition hover:bg-primary-500/10 {{ request()->routeIs('pizza') ? 'bg-primary-500/10' : '' }}">
                                    <span class="font-pixel text-[9px] uppercase tracking-wider {{ request()->routeIs('pizza') ? 'text-primary-300' : 'text-white' }} group-hover:text-primary-300">{{ __('Eten') }}</span>
                                    <span class="mt-1 block text-xs text-forge-steel/70">Pizza en bestellingen</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <x-nav-link href="{{ route('editions.index') }}" :active="request()->routeIs('editions.*')" class="whitespace-nowrap">
                        {{ __('Edities') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Right side - Desktop -->
            <div class="hidden lg:flex lg:items-center lg:space-x-4">
                <a href="{{ config('app.discord_server') }}" target="_blank" rel="noopener noreferrer"
                    class="font-display text-xs uppercase tracking-widest text-forge-steel transition hover:text-primary-300">
                    Discord
                </a>

                <!-- Settings Dropdown -->
                <div class="relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <button
                                    class="flex text-sm border-2 border-primary-500/30 rounded-full focus:outline-none focus:border-primary-400 transition">
                                    <img class="h-8 w-8 rounded-full object-cover"
                                        src="{{ Auth::user()->profile_photo_url }}"
                                        alt="{{ Auth::user()->name }}" />
                                </button>
                            @else
                                <button type="button"
                                    class="inline-flex items-center px-3 py-2 border border-primary-500/20 clip-corner text-sm leading-4 font-display uppercase tracking-wider text-forge-steel bg-forge-graphite hover:text-primary-300 hover:border-primary-500/40 focus:outline-none transition ease-in-out duration-150">
                                    <span class="truncate max-w-[120px]">{{ Auth::user()->name }}</span>
                                </button>
                            @endif
                        </x-slot>

                        <x-slot name="content">
                            <div class="block px-4 py-2 font-display text-xs uppercase tracking-widest text-primary-400/70">
                                {{ __('Manage Account') }}
                            </div>

                            <x-dropdown-link href="{{ route('profile.show') }}">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                    {{ __('API Tokens') }}
                                </x-dropdown-link>
                            @endif

                            <div class="border-t border-primary-500/15"></div>

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

            <!-- Mobile Right Side -->
            <div class="flex items-center lg:hidden">
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

    </nav>

    <!-- Responsive Navigation Menu — full-screen overlay -->
    <div x-cloak x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click.self="open = false"
        class="fixed inset-x-0 bottom-0 top-16 z-40 overflow-y-auto overscroll-contain bg-forge-black/95 backdrop-blur-md lg:hidden">
        <div class="mx-auto max-w-7xl px-4 pt-2 pb-6 sm:px-6">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link href="{{ route('schedule') }}" :active="request()->routeIs('schedule')">
                {{ __('Schema') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('tournaments') }}" :active="request()->routeIs('tournaments')">
                {{ __('Leaderboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('editions.index') }}" :active="request()->routeIs('editions.*')">
                {{ __('Edities') }}
            </x-responsive-nav-link>

            {{-- Pages that only matter during the LAN itself. --}}
            <p class="px-3 pt-4 pb-1 font-pixel text-[8px] uppercase tracking-[0.2em] text-primary-400/70">{{ __('Tijdens de LAN') }}</p>
            <x-responsive-nav-link href="{{ route('news.index') }}" :active="request()->routeIs('news.*')">
                {{ __('Nieuws') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('timeline') }}" :active="request()->routeIs('timeline')">
                {{ __('Tijdlijn') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('live') }}" :active="request()->routeIs('live')">
                {{ __('Live') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('pizza') }}" :active="request()->routeIs('pizza')">
                {{ __('Eten') }}
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-1 border-t border-primary-500/15">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0 me-3">
                        <img class="h-10 w-10 rounded-full object-cover"
                            src="{{ Auth::user()->profile_photo_url }}"
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

                <x-responsive-nav-link href="{{ config('app.discord_server') }}">
                    {{ __('Discord') }}
                </x-responsive-nav-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-responsive-nav-link>
                @endif

                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf
                    <x-responsive-nav-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
        </div>

        {{-- pasture flourish: Arti trots across toward the barn when the menu opens --}}
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-24 overflow-hidden" aria-hidden="true">
            <div class="absolute inset-x-0 bottom-0 h-10 bg-gradient-to-t from-primary-900/45 via-primary-900/15 to-transparent"></div>
            <div class="absolute bottom-1 right-4 w-14 opacity-80">
                <img class="pixel block w-full" src="{{ asset('images/farm/barn.png') }}" alt="">
            </div>
            <div class="menu-arti">
                <img class="pixel" src="{{ asset('images/farm/arti.png') }}" alt="">
            </div>
        </div>
    </div>
</div>
