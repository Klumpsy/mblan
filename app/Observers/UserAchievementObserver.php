<?php

namespace App\Observers;

use App\Models\UserAchievement;
use App\Services\DiscordWebhookService;
use Illuminate\Support\Facades\Log;

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
        Log::critical('UserAchievement created observer triggered', [
            'user_id' => $userAchievement->user_id,
            'achievement_id' => $userAchievement->achievement_id,
            'achieved_at' => $userAchievement->achieved_at
        ]);

        $this->handleAchievement($userAchievement);
    }

    public function updated(UserAchievement $userAchievement): void
    {
        Log::critical('UserAchievement updated observer triggered', [
            'user_id' => $userAchievement->user_id,
            'achievement_id' => $userAchievement->achievement_id,
            'achieved_at' => $userAchievement->achieved_at,
            'was_changed' => $userAchievement->wasChanged('achieved_at'),
            'original' => $userAchievement->getOriginal('achieved_at')
        ]);

        if (
            $userAchievement->wasChanged('achieved_at')
        ) {

            $this->handleAchievement($userAchievement);
        }
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
