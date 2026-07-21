<a href="{{ route('editions.show', $edition->slug) }}" class="group block">
    <x-forge.card class="overflow-hidden !p-0">
        <div class="flex flex-col md:flex-row">
            <div class="md:w-1/3 flex-shrink-0">
                <div class="w-full aspect-[3/2] md:aspect-[4/3] lg:aspect-[16/9] overflow-hidden">
                    @if ($edition->logo)
                        <img src="{{ asset('storage/' . $edition->logo) }}" alt="{{ $edition->name }}"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" />
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-forge-graphite text-forge-steel/40">
                            <span class="text-xs uppercase tracking-widest">No image available</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="md:w-2/3 p-6">
                <div class="mb-3 flex items-center gap-3">
                    <span class="h-3 w-3 rounded-full"
                        style="background: {{ $edition->color ?? '#65E59A' }}; box-shadow: 0 0 12px {{ $edition->color ?? '#65E59A' }};"></span>
                    <span class="font-display text-xs uppercase tracking-widest text-forge-steel/60">{{ $edition->year }}</span>
                </div>
                <h5 class="mb-2 font-display text-2xl font-bold uppercase tracking-wide text-white transition-colors group-hover:text-primary-300">
                    {{ $edition->name }}
                </h5>
                <div class="line-clamp-3 text-sm text-forge-steel/70">
                    {!! strip_tags($edition->description) !!}
                </div>
            </div>
        </div>
    </x-forge.card>
</a>
