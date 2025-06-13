<?php

namespace App\Observers;

use App\AchievementType;
use App\Models\Tournament;
use App\Models\User;
use App\Services\AchievementService;

class TournamentObserver
{
    /**
     * Handle the Tournament "created" event.
     */
    public function created(Tournament $tournament): void
    {
        //
    }

    /**
     * Handle the Tournament "updated" event.
     */
    public function updated(Tournament $tournament): void
    {
        //
    }

    /**
     * Handle the Tournament "deleted" event.
     */
    public function deleted(Tournament $tournament): void
    {
        //
    }

    /**
     * Handle the Tournament "restored" event.
     */
    public function restored(Tournament $tournament): void
    {
        //
    }

    /**
     * Handle the Tournament "force deleted" event.
     */
    public function forceDeleted(Tournament $tournament): void
    {
        //
    }

    public function attached(Tournament $tournament, $relation, $pivotIds)
    {
        if ($relation === 'usersWithScores') {
            foreach ($pivotIds as $userId) {
                $user = User::find($userId);

                AchievementService::check($user, AchievementType::FIRST_TOURNAMENT->value);
                AchievementService::check($user, AchievementType::TOURNAMENT_SIGNUPS_3->value);
            }
        }
    }
}
