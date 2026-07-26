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

    {{-- Per-page pixel-farm backdrop, chosen from the current route. --}}
    @php
        $sceneryVariant = match (true) {
            request()->routeIs('news.*') => 'news',
            request()->routeIs('tournaments') => 'tournaments',
            request()->routeIs('timeline') => 'timeline',
            request()->routeIs('profile.*') || request()->is('user/*') => 'profile',
            default => 'default',
        };
    @endphp
    <x-page-scenery :variant="$sceneryVariant" />

    <div class="min-h-screen text-forge-steel">
        <livewire:navigation-menu />

        <x-flash-message />
        <main>
            {{ $slot }}
        </main>
    </div>

    @stack('modals')

    <x-toast-host />
    <x-arti-uploader />

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
