@props(['tag'])

<span class="px-2.5 py-0.5 rounded-full text-xs font-medium "
    style="background-color: {{ $tag->color }}; color: white;">
    {{ $tag->name }}
</span>
