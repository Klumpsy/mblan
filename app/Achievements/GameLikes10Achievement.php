<?php

namespace App\Achievements;

use App\Interfaces\AchievementStrategy;
use App\Models\Achievement;
use App\Models\User;

class GameLikes10Achievement implements AchievementStrategy
{
    public function handle(User $user, Achievement $achievement): void
    {
        $count = $user->likedGames()->count();

        if ($count >= ($achievement->threshold ?? 10)) {
            $user->achievements()->syncWithoutDetaching([
                $achievement->id => [
                    'achieved_at' => now(),
                    'progress' => $count,
                ],
            ]);
        } else {
            $user->achievements()->syncWithoutDetaching([
                $achievement->id => ['progress' => $count],
            ]);
        }
    }
}
