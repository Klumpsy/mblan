@props(['for'])

@error($for)
    <p {{ $attributes->merge(['class' => 'text-sm text-danger-400']) }}>{{ $message }}</p>
@enderror
