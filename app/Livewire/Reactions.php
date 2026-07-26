<?php

namespace App\Livewire;

use App\Models\Reaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Emoji reactions (heart / laugh / goat) for any model using HasReactions.
 * Drop in with <livewire:reactions :model="$photo" :key="..." />.
 */
class Reactions extends Component
{
    public string $reactableClass;

    public int $reactableId;

    public function mount(Model $model): void
    {
        $this->reactableClass = $model::class;
        $this->reactableId = $model->getKey();
    }

    public function toggle(string $emoji): void
    {
        if (! Auth::check()) {
            $this->dispatch('login-required');

            return;
        }

        if (! array_key_exists($emoji, Reaction::EMOJIS)) {
            return;
        }

        $model = $this->resolveModel();

        $existing = $model->reactions()
            ->where('user_id', Auth::id())
            ->where('emoji', $emoji)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            $model->reactions()->create(['user_id' => Auth::id(), 'emoji' => $emoji]);
        }
    }

    protected function resolveModel(): Model
    {
        return $this->reactableClass::findOrFail($this->reactableId);
    }

    public function render()
    {
        $model = $this->resolveModel();

        $counts = $model->reactions()
            ->selectRaw('emoji, count(*) as total')
            ->groupBy('emoji')
            ->pluck('total', 'emoji');

        $mine = Auth::check()
            ? $model->reactions()->where('user_id', Auth::id())->pluck('emoji')->all()
            : [];

        return view('livewire.reactions', [
            'emojis' => Reaction::EMOJIS,
            'counts' => $counts,
            'mine' => $mine,
        ]);
    }
}
