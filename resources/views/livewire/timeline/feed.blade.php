<div>
    {{-- ===== Post a photo ===== --}}
    <form wire:submit="save" class="clip-corner metal-edge mb-14 p-6">
        <div class="mb-5 flex items-center gap-3">
            <img src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}"
                class="h-9 w-9 rounded-full object-cover ring-2 ring-primary-500/30" />
            <div>
                <p class="font-display text-sm uppercase tracking-wide text-white">Deel een foto</p>
                <p class="font-pixel text-[8px] uppercase tracking-[0.2em] text-forge-steel/50">Eén foto per keer, met je verhaal</p>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            {{-- Photo picker + preview --}}
            <div>
                <label class="group relative flex aspect-video w-full cursor-pointer items-center justify-center overflow-hidden clip-corner border border-dashed border-primary-500/25 bg-forge-graphite/40 transition hover:border-primary-500/50">
                    <input type="file" accept="image/*" wire:model="photo" class="hidden" />

                    @if ($photo && ! $errors->has('photo'))
                        <img src="{{ $photo->temporaryUrl() }}" alt="Voorbeeld" class="h-full w-full object-cover" />
                        <span class="absolute bottom-2 right-2 font-pixel text-[8px] uppercase tracking-widest text-white/80 bg-forge-black/60 px-2 py-1">Wijzig</span>
                    @else
                        <div class="flex flex-col items-center gap-2 text-forge-steel/50" wire:loading.remove wire:target="photo">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="font-pixel text-[9px] uppercase tracking-widest">Kies een foto</span>
                        </div>
                    @endif

                    <div class="absolute inset-0 flex items-center justify-center bg-forge-black/60" wire:loading.flex wire:target="photo">
                        <span class="font-pixel text-[9px] uppercase tracking-widest text-primary-300">Uploaden...</span>
                    </div>
                </label>
                @error('photo') <p class="mt-2 font-pixel text-[8px] uppercase tracking-widest text-warning-400">{{ $message }}</p> @enderror
            </div>

            {{-- Story --}}
            <div class="flex flex-col">
                <textarea wire:model="story" rows="5" maxlength="1000" placeholder="Vertel je verhaal bij deze foto..."
                    class="clip-corner w-full flex-1 resize-none border border-primary-500/15 bg-forge-graphite/40 p-3 text-sm text-forge-steel placeholder:text-forge-steel/40 focus:border-primary-500/40 focus:outline-none focus:ring-0"></textarea>
                @error('story') <p class="mt-2 font-pixel text-[8px] uppercase tracking-widest text-warning-400">{{ $message }}</p> @enderror

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn-wood clip-corner text-[10px] disabled:opacity-50"
                        wire:loading.attr="disabled" wire:target="save,photo">
                        <span wire:loading.remove wire:target="save">Plaatsen</span>
                        <span wire:loading wire:target="save">Bezig...</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- ===== Timeline ===== --}}
    @if ($photos->isEmpty())
        <div class="clip-corner metal-edge p-12 text-center">
            <p class="text-sm text-forge-steel/60">Nog geen foto's. Wees de eerste die er één deelt.</p>
        </div>
    @else
        <ol class="relative ml-2 space-y-8 border-l-2 border-primary-500/15 pl-6">
            @foreach ($photos as $photo)
                <li class="relative" wire:key="photo-{{ $photo->id }}">
                    {{-- Node on the timeline line --}}
                    <span class="absolute -left-[1.72rem] top-5 h-3 w-3 rounded-full ring-4 ring-forge-black {{ $photo->user_id === auth()->id() ? 'bg-primary-400' : 'bg-primary-500/50' }}"></span>

                    <div class="overflow-hidden clip-corner metal-edge">
                        <div class="flex items-center gap-3 border-b border-primary-500/10 bg-forge-graphite/40 px-4 py-3">
                            <img src="{{ $photo->user?->profile_photo_url }}" alt="{{ $photo->user?->name }}"
                                class="h-9 w-9 shrink-0 rounded-full object-cover ring-2 {{ $photo->user_id === auth()->id() ? 'ring-primary-400' : 'ring-primary-500/20' }}" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-display text-sm uppercase tracking-wide text-white">
                                    {{ $photo->user?->name ?? 'Onbekend' }}
                                    @if ($photo->user_id === auth()->id())
                                        <span class="ml-1 font-pixel text-[7px] uppercase tracking-widest text-primary-400">jij</span>
                                    @endif
                                </p>
                                <time class="font-pixel text-[8px] uppercase tracking-[0.2em] text-forge-steel/50"
                                    datetime="{{ $photo->created_at->toIso8601String() }}">
                                    {{ $photo->created_at->translatedFormat('D d M Y') }} &middot; {{ $photo->created_at->format('H:i') }}
                                </time>
                            </div>
                        </div>

                        <img src="{{ asset('storage/' . $photo->image) }}" alt="Foto van {{ $photo->user?->name }}"
                            class="w-full object-cover" loading="lazy" decoding="async" />

                        @if ($photo->story)
                            <p class="whitespace-pre-line px-4 py-4 text-sm leading-relaxed text-forge-steel/80">{{ $photo->story }}</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>

        @if ($hasMore)
            <div class="flex justify-center pt-2">
                <button wire:click="loadMore" wire:loading.attr="disabled" wire:target="loadMore"
                    class="clip-corner metal-edge px-6 py-3 font-display text-xs uppercase tracking-widest text-forge-steel transition hover:text-white disabled:opacity-50">
                    <span wire:loading.remove wire:target="loadMore">Laad meer</span>
                    <span wire:loading wire:target="loadMore">Laden...</span>
                </button>
            </div>
        @endif
    @endif
</div>
