<?php

namespace App\Observers;

use App\Models\UserAchievement;
use App\Services\DiscordWebhookService;

/**
 * Achievement Discord notifications are handled centrally by
 * App\Services\AchievementEvaluator, which fires exactly once on a new unlock
 * and can be silenced for backfills. This observer used to send them too,
 * causing duplicate Discord messages, so it no longer notifies.
 */
class UserAchievementObserver
{
    public function __construct(
        private DiscordWebhookService $discordService
    ) {}

    /**
     * Handle the Achievement "created" event.
     */
    private function handleAchievement(UserAchievement $userAchievement): void
    {
        if ($userAchievement->achieved_at) {
            $user = $userAchievement->user;
            $achievement = $userAchievement->achievement;
            $this->discordService->sendAchievementNotification($user, $achievement);
        }
    }

    public function created(UserAchievement $userAchievement): void
    {
        $this->handleAchievement($userAchievement);
    }

    public function updated(UserAchievement $userAchievement): void
    {
        if (
            $userAchievement->wasChanged('achieved_at') &&
            $userAchievement->getOriginal('achieved_at') === null &&
            $userAchievement->achieved_at !== null
        ) {
            $this->handleAchievement($userAchievement);
        }
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
