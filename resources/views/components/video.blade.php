@props([
    'link',
    'aspectW' => '16',
    'aspectH' => '9',
    'ratio' => null,
    'controls' => true,
    'autoplay' => false,
    'muted' => false,
    'loop' => false,
    'startAt' => 0,
    'title' => 'YouTube video player',
    'loading' => 'lazy',
    'width' => '560',
    'height' => '315',
])

@php
    if ($ratio && strpos($ratio, ':') !== false) {
        [$aspectW, $aspectH] = explode(':', $ratio);
    }

    function getYoutubeIdFromUrl($url)
    {
        $pattern =
            '/^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})(?:\S+)?$/';

        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    $videoId = getYoutubeIdFromUrl($link);
    $aspectRatio = ((float) $aspectH / (float) $aspectW) * 100;

    $params = [];
    if ($controls === false) {
        $params[] = 'controls=0';
    }
    if ($autoplay) {
        $params[] = 'autoplay=1';
    }
    if ($muted) {
        $params[] = 'mute=1';
    }
    if ($loop) {
        $params[] = 'loop=1';
    }
    if ($startAt > 0) {
        $params[] = 'start=' . intval($startAt);
    }

    $paramString = !empty($params) ? '?' . implode('&', $params) : '';
@endphp

@if ($videoId)
    <div {{ $attributes->merge(['class' => 'relative w-full overflow-hidden rounded-lg shadow-md']) }}
        style="padding-bottom: {{ $aspectRatio }}%;">
        <iframe class="absolute top-0 left-0 w-full h-full"
            src="https://www.youtube.com/embed/{{ $videoId }}{{ $paramString }}" title="{{ $title }}"
            frameborder="0" loading="{{ $loading }}"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            width="{{ $width }}" height="{{ $height }}" allowfullscreen>
        </iframe>
    </div>
@else
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Error:</strong>
        <span class="block sm:inline">Invalid YouTube URL</span>
    </div>
@endif
