<?php

namespace App\Achievements;

use App\Interfaces\AchievementStrategy;
use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;

class TournamentSignup3Achievement implements AchievementStrategy
{
    public function handle(User $user, Achievement $achievement): void
    {
        $count = $user->tournaments()->count();

        $userAchievement = UserAchievement::firstOrNew([
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
        ]);

        $userAchievement->progress = $count;

        if ($count >= ($achievement->threshold ?? 3) && !$userAchievement->achieved_at) {
            $userAchievement->achieved_at = now();
        }

        $userAchievement->save();
    }
}
