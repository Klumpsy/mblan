<?php

namespace App\Achievements;

use App\Interfaces\AchievementStrategy;
use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;

abstract class AbstractDrinkBeer implements AchievementStrategy
{
    protected const BEER_COUNT_THRESHOLD = 5;

    public function handle(User $user, Achievement $achievement): void
    {
        $currentSignup = $user->signups()
            ->whereHas('edition', function ($query) {
                $query->where('year', now()->year);
            })
            ->where('confirmed', true)
            ->first();

        if (!$currentSignup) {
            return;
        }

        $totalBeers = $currentSignup->beer_count;
        $threshold = $achievement->threshold ?? static::BEER_COUNT_THRESHOLD;

        $userAchievement = UserAchievement::firstOrNew([
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
        ]);

        $userAchievement->progress = $totalBeers;

        if ($totalBeers >= $threshold && !$userAchievement->achieved_at) {
            $userAchievement->achieved_at = now();
        }

        $userAchievement->save();
    }
}
