<?php

namespace App\Observers;

use App\Models\UserAchievement;

/**
 * Achievement Discord notifications are handled centrally by
 * App\Services\AchievementEvaluator, which fires exactly once on a new unlock
 * and can be silenced for backfills. This observer used to send them too,
 * causing duplicate Discord messages, so it no longer notifies.
 */
class UserAchievementObserver
{
    public function created(UserAchievement $userAchievement): void
    {
        //
    }

    public function updated(UserAchievement $userAchievement): void
    {
        //
    }

    public function deleted(UserAchievement $userAchievement): void
    {
        //
    }

    public function restored(UserAchievement $userAchievement): void
    {
        //
    }

    public function forceDeleted(UserAchievement $userAchievement): void
    {
        //
    }
}
