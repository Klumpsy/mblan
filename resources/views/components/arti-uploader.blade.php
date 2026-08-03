{{--
    Full-screen upload indicator with the bobbing Arti sprite, shown whenever an
    image is being prepared/uploaded anywhere on the site. Driven by the
    `mblan-uploading` window event that resources/js/image-upload.js dispatches
    (on: true when an upload starts, on: false when it finishes). A counter keeps
    it visible until every in-flight upload is done.
--}}
<div
    x-data="{
        count: 0,
        get show() { return this.count > 0; },
    }"
    x-on:mblan-uploading.window="$event.detail.on ? count++ : (count = Math.max(0, count - 1))"
    x-show="show"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-[80] flex items-center justify-center bg-forge-black/75 backdrop-blur-sm"
>
    <div class="clip-corner metal-edge flex flex-col items-center gap-4 bg-forge-graphite/90 px-10 py-8 text-center">
        <img
            src="{{ \App\Models\Edition::current()?->sceneryMascot() ?? asset('images/farm/arti.png') }}"
            alt="Arti"
            class="pixel h-16 w-16"
            style="animation: sprite-bob 0.3s steps(2, end) infinite;"
        />
        <p class="font-pixel text-[10px] uppercase leading-relaxed tracking-widest text-primary-300">
            Arti sjouwt je foto<br>naar de schuur...
        </p>
    </div>
</div>
