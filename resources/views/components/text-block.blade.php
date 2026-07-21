@props(['text', 'title', 'index', 'id' => null])

<div class="clip-corner metal-edge p-6"
    @if ($id) id="{{ $id }}" @endif>
    <h3 class="flex justify-between font-display text-2xl font-bold uppercase tracking-wide text-white mb-4 border-b border-primary-500/15 pb-3 cursor-pointer transition-colors duration-200 hover:text-primary-300"
        @click="openSection = openSection === {{ $index }} ? null : {{ $index }}">
        <span>{{ $title }}</span>
        <span class="text-primary-400" x-text="openSection === {{ $index }} ? '-' : '+'"></span>
    </h3>
    <div class="prose prose-invert max-w-none text-forge-steel/80" x-show="openSection === {{ $index }}"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-4">
        {!! $text !!}
    </div>
</div>
