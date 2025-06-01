<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>MBLAN</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased">
    <x-flash-message />
    <div class="relative flex justify-center items-center min-h-screen bg-cover bg-center bg-gradient-to-r from-purple-500 to-pink-500">
        <div class="max-w-7xl mx-auto p-6 lg:p-8 flex justify-center items-center relative z-10">
            <div class="flex flex-col items-center">
                <img src="{{ asset('images/logo.svg') }}" alt="MBLAN Logo"
                    class="w-80 md:w-auto md:max-w-xl h-auto mb-8 transition-all duration-700 ease-in-out transform hover:scale-105 animate-glow">

                @if (Route::has('login'))
                    <div class="flex flex-wrap justify-center mt-6 gap-4">
                        @auth
                            <a href="{{ url('/dashboard') }}"
                                class="button-shine px-8 py-4 bg-black/70 rounded-xl font-bold text-white text-lg hover:shadow-lg hover:shadow-purple-500/30 transition duration-300 ease-in-out transform hover:-translate-y-1 border-2 border-white/20 backdrop-blur-sm">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="button-shine px-8 py-4 bg-black/70 rounded-xl font-bold text-white text-lg hover:shadow-lg hover:shadow-orange-500/30 transition duration-300 ease-in-out transform hover:-translate-y-1 border-2 border-white/20 backdrop-blur-sm">
                                Login
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                    class="button-081458
                                    shine px-8 py-4 bg-black/70 rounded-xl font-bold text-white text-lg hover:shadow-lg hover:shadow-purple-500/30 transition duration-300 ease-in-out transform hover:-translate-y-1 border-2 border-white/20 backdrop-blur-sm">
                                    Register
                                </a>
                            @endif
                        @endauth
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>

</html>
