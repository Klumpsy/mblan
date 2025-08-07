<?php

namespace App\Achievements;

use App\Interfaces\AchievementStrategy;
use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;

class FirstSignupAchievement implements AchievementStrategy
{
    public function handle(User $user, Achievement $achievement): void
    {
        $count = $user->signups()->count();

        if ($count >= 1) {
            $userAchievement = UserAchievement::firstOrNew([
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
            ]);

            if (!$userAchievement->achieved_at) {
                $userAchievement->achieved_at = now();
                $userAchievement->save();
            }
        }
    }
}
