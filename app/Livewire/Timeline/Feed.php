<?php

namespace App\Livewire\Timeline;

use App\Models\Photo;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * The shared photo timeline. Any signed-in player can post one photo at a time
 * with a short story; the feed shows newest first and loads more as you scroll.
 */
class Feed extends Component
{
    use WithFileUploads;

    #[Validate('required|image|max:12288')] // 12 MB before optimization
    public $photo = null;

    #[Validate('required|string|min:2|max:1000')]
    public string $story = '';

    public int $perPage = 6;

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'photo.required' => 'Kies eerst een foto.',
            'photo.image' => 'Alleen afbeeldingen zijn toegestaan.',
            'photo.max' => 'Deze foto is te groot (max 12 MB).',
            'story.required' => 'Schrijf een kort verhaaltje bij je foto.',
            'story.min' => 'Je verhaal is wel erg kort.',
            'story.max' => 'Je verhaal is te lang (max 1000 tekens).',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $path = $this->photo->store('timeline', 'public');

        Photo::create([
            'user_id' => Auth::id(),
            'image' => $path,
            'story' => trim($this->story),
        ]);

        $this->reset('photo', 'story');
        $this->dispatch('photo-posted');
    }

    public function loadMore(): void
    {
        $this->perPage += 6;
    }

    public function render()
    {
        $photos = Photo::with('user')
            ->latest()
            ->take($this->perPage + 1)
            ->get();

        $hasMore = $photos->count() > $this->perPage;

        return view('livewire.timeline.feed', [
            'photos' => $photos->take($this->perPage),
            'hasMore' => $hasMore,
        ]);
    }
}
