<?php

namespace App\Observers;

use App\Enums\AchievementType;
use App\Models\UserTournament;
use App\Services\AchievementService;

class UserTournamentObserver
{
    /**
     * Handle the UserTournament "created" event.
     */
    public function created(UserTournament $userTournament): void
    {
        AchievementService::check($userTournament->user, AchievementType::FIRST_TOURNAMENT->value);
        AchievementService::check($userTournament->user, AchievementType::TOURNAMENT_SIGNUPS_3->value);
    }

    /**
     * Handle the UserTournament "updated" event.
     */
    public function updated(UserTournament $userTournament): void
    {
        //
    }

    /**
     * Handle the UserTournament "deleted" event.
     */
    public function deleted(UserTournament $userTournament): void
    {
        //
    }

    /**
     * Handle the UserTournament "restored" event.
     */
    public function restored(UserTournament $userTournament): void
    {
        //
    }

    /**
     * Handle the UserTournament "force deleted" event.
     */
    public function forceDeleted(UserTournament $userTournament): void
    {
        //
    }
}
