<?php

namespace App\Observers;

use App\Enums\AchievementType;
use App\Models\UserGame;
use App\Services\AchievementService;

class UserGameObserver
{
    /**
     * Handle the UserGame "created" event.
     */
    public function created(UserGame $userGame): void
    {
        AchievementService::check($userGame->user, AchievementType::GAME_LIKE_5->value);
        AchievementService::check($userGame->user, AchievementType::GAME_LIKE_10->value);
        AchievementService::check($userGame->user, AchievementType::GAME_LIKE_20->value);
    }

    /**
     * Handle the UserGame "updated" event.
     */
    public function updated(UserGame $userGame): void
    {
        //
    }

    /**
     * Handle the UserGame "deleted" event.
     */
    public function deleted(UserGame $userGame): void
    {
        //
    }

    /**
     * Handle the UserGame "restored" event.
     */
    public function restored(UserGame $userGame): void
    {
        //
    }

    /**
     * Handle the UserGame "force deleted" event.
     */
    public function forceDeleted(UserGame $userGame): void
    {
        //
    }
}
