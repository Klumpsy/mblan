<?php

namespace App\Livewire;

use App\Models\Achievement;
use App\Models\User;
use App\Services\AchievementEvaluator;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Shows a user's achievement wall on their profile: every displayable
 * achievement, unlocked ones lit up and locked ones greyed with a progress
 * bar. Mounting re-syncs automatic achievements so the wall (and any Discord
 * notification) is always up to date when a user looks at their profile.
 */
class UserAchievements extends Component
{
    public int $userId;

    public function mount(?User $user = null): void
    {
        $user ??= auth()->user();
        $this->userId = $user->id;

        // System-awards on view: cheap, idempotent, and never throws.
        app(AchievementEvaluator::class)->sync($user);
    }

    public function render()
    {
        $user = User::findOrFail($this->userId);

        // Only meaningful achievements: manual ones, or automatic ones wired to
        // a known metric (hides half-configured legacy rows).
        $achievements = Achievement::query()
            ->where(fn ($q) => $q->where('type', 'manual')->orWhereNotNull('metric'))
            ->orderByRaw("CASE WHEN type = 'manual' THEN 1 ELSE 0 END")
            ->orderBy('threshold')
            ->orderBy('name')
            ->get();

        $pivots = $user->achievements()->get()->keyBy('id');

        $cards = $achievements->map(function (Achievement $a) use ($pivots): array {
            $pivot = $pivots->get($a->id)?->pivot;
            $threshold = max(1, (int) ($a->threshold ?? 1));
            $progress = (int) ($pivot->progress ?? 0);
            $unlocked = (bool) ($pivot?->achieved_at);

            return [
                'achievement' => $a,
                'unlocked' => $unlocked,
                'progress' => min($progress, $threshold),
                'threshold' => $threshold,
                'pct' => $unlocked ? 100 : (int) round(min($progress, $threshold) / $threshold * 100),
                'achieved_at' => $pivot?->achieved_at,
            ];
        });

        return view('livewire.user-achievements', [
            'cards' => $cards,
            'unlockedCount' => $cards->where('unlocked', true)->count(),
            'total' => $cards->count(),
        ]);
    }
}
