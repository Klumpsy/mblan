<?php

namespace App\Achievements;

use App\Interfaces\AchievementStrategy;
use App\Models\Achievement;
use App\Models\User;

class FirstSignupAchievement implements AchievementStrategy
{
    public function handle(User $user, Achievement $achievement): void
    {
        $count = $user->signups()->count();

        if ($count >= 1) {
            $user->achievements()->syncWithoutDetaching([
                $achievement->id => ['achieved_at' => now()],
            ]);
        }
    }
}
