<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MBLAN') }}</title>
    <link rel="preload" href="{{ asset('images/logo.svg') }}" as="image">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=chakra-petch:400,500,600,700|montserrat:400,500,600,700|press-start-2p:400&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="font-sans antialiased">
    <x-banner />

    {{-- farm + green ambiance so the app matches the landing --}}
    <div class="pointer-events-none fixed inset-0 -z-10" aria-hidden="true">
        <div class="absolute inset-0 bg-gradient-to-b from-forge-forest/60 via-forge-black to-forge-black"></div>
        <div class="absolute inset-0 bg-primary-900/15"></div>
        <div class="absolute inset-0 bg-grid opacity-[0.10]"></div>
        <div class="absolute left-1/2 top-0 h-[45vmax] w-[45vmax] -translate-x-1/2 -translate-y-1/3 rounded-full bg-primary-500/12 blur-[130px]"></div>
        <img src="{{ asset('images/farm/backdrop.png') }}" alt=""
            class="pixel absolute inset-x-0 bottom-0 h-56 w-full object-cover opacity-[0.28]"
            style="-webkit-mask-image: linear-gradient(to top, #000, transparent); mask-image: linear-gradient(to top, #000, transparent);" />
    </div>

    <div class="min-h-screen bg-forge-black/40 text-forge-steel">
        <livewire:navigation-menu />

        @if (isset($header))
            <header class="border-b border-primary-500/20 bg-forge-forest/60 backdrop-blur">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif
        <x-flash-message />
        <main>
            {{ $slot }}
        </main>
    </div>

    @stack('modals')

    {{-- sync the barn-maze attempt stats (from the guest cookie) onto the account --}}
    <script>
        (function () {
            function c(n) { var m = document.cookie.match('(?:^|; )' + n + '=([^;]*)'); return m ? decodeURIComponent(m[1]) : null; }
            var caught = c('mblan_caught');
            if (caught === null) return;
            fetch('{{ route('game.sync') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({
                    caught: parseInt(caught, 10) || 0,
                    completed: c('mblan_done') === '1',
                    time: parseInt(c('mblan_time') || '0', 10) || 0,
                }),
            }).catch(function () {});
        })();
    </script>

    @livewireScripts
</body>

</html>
