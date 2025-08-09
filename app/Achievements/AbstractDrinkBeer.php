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

        $userAchievement = UserAchievement::where([
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
        ])->first();

        if (!$userAchievement) {
            // Create new record
            UserAchievement::create([
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
                'progress' => $totalBeers,
                'achieved_at' => $totalBeers >= $threshold ? now() : null,
            ]);
        } else {
            // Update existing record
            $updates = ['progress' => $totalBeers];

            // Only set achieved_at if not already achieved and threshold is met
            if (!$userAchievement->achieved_at && $totalBeers >= $threshold) {
                $updates['achieved_at'] = now();
            }

            $userAchievement->update($updates);
        }
    }
}
