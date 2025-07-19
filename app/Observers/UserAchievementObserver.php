<?php

namespace App\Observers;

use App\Models\UserAchievement;
use App\Services\DiscordWebhookService;

class UserAchievementObserver
{

    public function __construct(
        private DiscordWebhookService $discordService
    ) {}
    /**
     * Handle the Achievement "created" event.
     */
    public function created(UserAchievement $userAchievement): void
    {
        $user = $userAchievement->user;
        $achievement = $userAchievement->achievement;

        $this->discordService->sendAchievementNotification($user, $achievement);
    }

    /**
     * Handle the Achievement "updated" event.
     */
    public function updated(UserAchievement $userAchievement): void
    {
        //
    }

    /**
     * Handle the Achievement "deleted" event.
     */
    public function deleted(UserAchievement $userAchievement): void
    {
        //
    }

    /**
     * Handle the Achievement "restored" event.
     */
    public function restored(UserAchievement $userAchievement): void
    {
        //
    }

    /**
     * Handle the Achievement "force deleted" event.
     */
    public function forceDeleted(UserAchievement $userAchievement): void
    {
        //
    }
}
