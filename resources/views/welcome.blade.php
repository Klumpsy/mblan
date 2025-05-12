<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>MBLAN</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<style>
    @keyframes fadeIn {
        0% {
            opacity: 0;
            transform: scale(0.95);
        }

        100% {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>

<body class="antialiased">
    <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-center bg-dark-800">
        @if (Route::has('login'))
            <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="font-semibold text-white hover:text-primary-300 focus:outline focus:outline-2 focus:rounded-sm">Dashboard</a>
                @else
                    <a href="{{ route('login') }}"
                        class="font-semibold text-white hover:text-primary-300 focus:outline focus:outline-2 focus:rounded-sm">
                        Login
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="ml-4 font-semibold text-white hover:text-primary-300 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
                    @endif
                @endauth
            </div>
        @endif

        <div class="max-w-7xl mx-auto p-6 lg:p-8 flex justify-center items-center">
            <img src="{{ asset('images/logo.svg') }}" alt="MBLAN Logo"
                class="w-64 md:w-96 h-auto transition-all duration-700 ease-in-out transform hover:scale-105"
                style="animation: fadeIn 1.2s ease-in-out;">
        </div>
    </div>
</body>

</html>
