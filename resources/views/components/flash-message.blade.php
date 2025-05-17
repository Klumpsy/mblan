@php
    $types = [
        'success' => [
            'bg' => 'bg-green-100',
            'border' => 'border-green-500',
            'text' => 'text-green-700',
            'hover' => 'hover:text-green-900',
        ],
        'error' => [
            'bg' => 'bg-red-100',
            'border' => 'border-red-500',
            'text' => 'text-red-700',
            'hover' => 'hover:text-red-900',
        ],
        'info' => [
            'bg' => 'bg-blue-100',
            'border' => 'border-blue-500',
            'text' => 'text-blue-700',
            'hover' => 'hover:text-blue-900',
        ],
        'warning' => [
            'bg' => 'bg-yellow-100',
            'border' => 'border-yellow-500',
            'text' => 'text-yellow-700',
            'hover' => 'hover:text-yellow-900',
        ],
        'message' => [
            'bg' => 'bg-gray-100',
            'border' => 'border-gray-500',
            'text' => 'text-gray-700',
            'hover' => 'hover:text-gray-900',
        ],
    ];

    $hasMessage = false;
    foreach ($types as $type => $styles) {
        if (session($type)) {
            $hasMessage = true;
            break;
        }
    }
@endphp

@if ($hasMessage)
    <div class="fixed top-0 left-0 right-0 z-50">
        @foreach ($types as $type => $styles)
            @if (session($type))
                <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100 transform translate-y-0"
                    x-transition:leave-end="opacity-0 transform -translate-y-2"
                    class="{{ $styles['bg'] }} {{ $styles['text'] }} p-4 shadow-md">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="ml-3">
                                <p class="text-sm font-medium">
                                    {{ session($type) }}
                                </p>
                            </div>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="show = false"
                                class="{{ $styles['text'] }} {{ $styles['hover'] }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ explode('-', $styles['border'])[1] }}-500 rounded-md">

                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endif
