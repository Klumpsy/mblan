@props(['edition' => null])

@php($edition ??= \App\Models\Edition::current())

@if ($edition)
    {{-- Overrides the static fallback palette in app.css. --}}
    <style data-edition="{{ $edition->slug }}">:root { {!! $edition->cssVariables() !!} }</style>
@endif
