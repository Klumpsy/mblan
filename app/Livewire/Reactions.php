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
    /** Max reactor names shown in the who-reacted tooltip; the rest becomes "+N anderen". */
    public const MAX_TOOLTIP_NAMES = 15;

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

        // Who reacted, per emoji, oldest first — shown in the hover/long-press tooltip.
        $reactors = $model->reactions()
            ->join('users', 'users.id', '=', 'reactions.user_id')
            ->orderBy('reactions.id')
            ->get(['reactions.emoji', 'users.name'])
            ->groupBy('emoji')
            ->map(fn ($group) => [
                'names' => $group->pluck('name')->take(self::MAX_TOOLTIP_NAMES)->all(),
                'more' => max(0, $group->count() - self::MAX_TOOLTIP_NAMES),
            ]);

        return view('livewire.reactions', [
            'emojis' => Reaction::EMOJIS,
            'counts' => $counts,
            'mine' => $mine,
            'reactors' => $reactors,
        ]);
    }
}
