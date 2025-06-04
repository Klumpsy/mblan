<a href="{{ route('editions.show', $edition->slug) }}"
    class="mb-4 block w-full bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
    <div class="flex flex-col md:flex-row">
        <div class="md:w-1/3 flex-shrink-0">
            <div class="w-full aspect-[3/2] md:aspect-[4/3] lg:aspect-[16/9] overflow-hidden rounded-lg">
                @if ($edition->logo)
                    <img src="{{ asset('storage/' . $edition->logo) }}" alt="{{ $edition->name }}"
                        class="w-full h-full object-cover" />
                @else
                    <div class="flex items-center justify-center w-full h-full bg-gray-200 text-gray-500">
                        <span>No image available</span>
                    </div>
                @endif
            </div>
        </div>
        <div class="md:w-2/3 p-4">
            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                {{ $edition->name }}
            </h5>
            <span class="font-normal text-gray-400 dark:text-white">
                {!! $edition->description !!}
            </span>
        </div>
    </div>
</a>
