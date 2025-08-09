@props(['text', 'title', 'index', 'id' => null])

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6"
    @if ($id) id="{{ $id }}" @endif>
    <h3 class="flex justify-between text-2xl font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-700 pb-2 cursor-pointer"
        @click="openSection = openSection === {{ $index }} ? null : {{ $index }}">
        <span>{{ $title }}</span>
        <span x-text="openSection === {{ $index }} ? '-' : '+'"></span>
    </h3>
    <div class="prose dark:prose-invert max-w-none dark:text-gray-400" x-show="openSection === {{ $index }}"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-4">
        {!! $text !!}
    </div>
</div>
