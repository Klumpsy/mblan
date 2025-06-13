<?php

namespace App\Achievements;

use App\Interfaces\AchievementStrategy;
use App\Models\Achievement;
use App\Models\User;

class JoinBarbecueAchievement implements AchievementStrategy
{
    public function handle(User $user, Achievement $achievement): void
    {
        $signups = $user->signups();

        if ($signups->where('joins_barbecue', true)->count() >= 1) {
            $user->achievements()->syncWithoutDetaching([
                $achievement->id => ['achieved_at' => now()],
            ]);
        }
    }
}
