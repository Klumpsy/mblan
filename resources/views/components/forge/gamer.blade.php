{{-- Pure-CSS walking astronaut avatar. Positioning/speed via wrapper .walker. --}}
<div {{ $attributes->merge(['class' => 'gamer']) }} aria-hidden="true">
    <span class="gamer__head"></span>
    <span class="gamer__arm gamer__arm--l"></span>
    <span class="gamer__arm gamer__arm--r"></span>
    <span class="gamer__body"></span>
    <span class="gamer__leg gamer__leg--l"></span>
    <span class="gamer__leg gamer__leg--r"></span>
</div>
