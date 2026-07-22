@props(['submit'])

<div x-data x-reveal {{ $attributes->merge(['class' => 'md:grid md:grid-cols-3 md:gap-6']) }}>
    <x-section-title>
        <x-slot name="title">{{ $title }}</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>
    </x-section-title>

    <div class="mt-5 md:mt-0 md:col-span-2">
        <form wire:submit="{{ $submit }}">
            <div class="clip-corner metal-edge px-4 py-5 sm:p-6">
                <div class="grid grid-cols-6 gap-6">
                    {{ $form }}
                </div>
            </div>

            @if (isset($actions))
                <div class="flex items-center justify-end mt-px border-t border-primary-500/10 bg-forge-graphite/40 px-4 py-3 text-end sm:px-6">
                    {{ $actions }}
                </div>
            @endif
        </form>
    </div>
</div>
