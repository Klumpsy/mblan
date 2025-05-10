@props([
    'role' => 'admin',
    'route' => '#',
])

@auth
    @if (auth()->user()->role === $role)
        <a href="{{ route($route) }}" {{ $attributes->class(['btn']) }}>
            {{ $slot }}
        </a>
    @endif
@endauth
