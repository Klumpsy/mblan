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

    public ?int $editingId = null;

    public string $editStory = '';

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
        $this->dispatch('mblan-notify', message: 'Foto geplaatst op de tijdlijn', type: 'success');
    }

    public function loadMore(): void
    {
        $this->perPage += 6;
    }

    /**
     * May the current user edit this post? Owners can edit their own; admins
     * can edit any.
     */
    public function canEdit(Photo $photo): bool
    {
        $user = Auth::user();

        return $user !== null && ($photo->user_id === $user->id || $user->role === 'admin');
    }

    public function startEdit(int $id): void
    {
        $photo = Photo::findOrFail($id);
        abort_unless($this->canEdit($photo), 403);

        $this->editingId = $photo->id;
        $this->editStory = (string) $photo->story;
    }

    public function cancelEdit(): void
    {
        $this->reset('editingId', 'editStory');
    }

    public function saveEdit(): void
    {
        $photo = Photo::findOrFail($this->editingId);
        abort_unless($this->canEdit($photo), 403);

        $validated = $this->validate(
            ['editStory' => 'required|string|min:2|max:1000'],
            [
                'editStory.required' => 'Schrijf een kort verhaaltje bij je foto.',
                'editStory.min' => 'Je verhaal is wel erg kort.',
                'editStory.max' => 'Je verhaal is te lang (max 1000 tekens).',
            ],
        );

        $photo->update(['story' => trim($validated['editStory'])]);

        $this->reset('editingId', 'editStory');
        $this->dispatch('mblan-notify', message: 'Bericht bijgewerkt', type: 'success');
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
