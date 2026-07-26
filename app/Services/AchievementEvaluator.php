<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\User;
use App\Support\AchievementMetrics;
use Illuminate\Support\Facades\Log;

/**
 * Awards automatic achievements to a user by comparing each achievement's live
 * metric value against its threshold. Idempotent and defensive: it never throws
 * (a failure here must never break the action that triggered it), records
 * achieved_at only on the first unlock, and fires a Discord notification the
 * moment an achievement is newly earned.
 *
 * Manual achievements are ignored here — those are granted by admins.
 */
class AchievementEvaluator
{
    public function __construct(private DiscordWebhookService $discord) {}

    /**
     * Sync every automatic achievement for one user. Returns the achievements
     * that were newly unlocked during this run. Pass $notify = false for bulk
     * backfills so hundreds of historical unlocks don't flood Discord.
     *
     * @return array<int, Achievement>
     */
    public function sync(User $user, bool $notify = true): array
    {
        $newlyUnlocked = [];

        try {
            $automatic = Achievement::where('type', 'automatic')
                ->whereNotNull('metric')
                ->get();

            $pivots = $user->achievements()
                ->whereIn('achievements.id', $automatic->pluck('id'))
                ->get()
                ->keyBy('id');

            foreach ($automatic as $achievement) {
                if (! AchievementMetrics::has($achievement->metric)) {
                    continue;
                }

                $value = AchievementMetrics::value($user, $achievement->metric);
                $threshold = max(1, (int) ($achievement->threshold ?? 1));
                $progress = min($value, $threshold);
                $unlocked = $value >= $threshold;

                $existing = $pivots->get($achievement->id);
                $wasAchieved = $existing && $existing->pivot->achieved_at;

                if (! $existing) {
                    // Don't store empty rows: a locked achievement with no
                    // progress yet is represented by the absence of a pivot
                    // (the UI defaults it to 0/threshold). Keeps the table lean
                    // and makes backfills fast.
                    if ($progress <= 0 && ! $unlocked) {
                        continue;
                    }

                    $user->achievements()->attach($achievement->id, [
                        'progress' => $progress,
                        'achieved_at' => $unlocked ? now() : null,
                    ]);
                } else {
                    // Self-correcting: achieved_at always reflects the current
                    // truth. Keep the original earn date once unlocked; clear it
                    // if the data no longer meets the threshold (e.g. an award
                    // made before a tournament was actually concluded).
                    $user->achievements()->updateExistingPivot($achievement->id, [
                        'progress' => $progress,
                        'achieved_at' => $unlocked
                            ? ($wasAchieved ? $existing->pivot->achieved_at : now())
                            : null,
                    ]);
                }

                if ($unlocked && ! $wasAchieved) {
                    $newlyUnlocked[] = $achievement;
                }
            }

            $user->load('achievements');
        } catch (\Throwable $e) {
            report($e);

            return $newlyUnlocked;
        }

        if ($notify) {
            foreach ($newlyUnlocked as $achievement) {
                $this->notify($user, $achievement);
            }
        }

        return $newlyUnlocked;
    }

    /**
     * Grant a (typically manual) achievement to a user directly, as an admin
     * action or system decision. Marks it achieved and notifies Discord once.
     */
    public function grant(User $user, Achievement $achievement): bool
    {
        $existing = $user->achievements()->where('achievements.id', $achievement->id)->first();
        if ($existing && $existing->pivot->achieved_at) {
            return false; // already had it
        }

        $payload = ['achieved_at' => now(), 'progress' => max(1, (int) ($achievement->threshold ?? 1))];
        if ($existing) {
            $user->achievements()->updateExistingPivot($achievement->id, $payload);
        } else {
            $user->achievements()->attach($achievement->id, $payload);
        }

        $this->notify($user, $achievement);

        return true;
    }

    private function notify(User $user, Achievement $achievement): void
    {
        try {
            $this->discord->sendAchievementNotification($user, $achievement);
        } catch (\Throwable $e) {
            Log::warning('Achievement Discord notification failed: '.$e->getMessage());
        }
    }
}
