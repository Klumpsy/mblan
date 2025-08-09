<?php

namespace App\Achievements;

use App\Interfaces\AchievementStrategy;
use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Support\Facades\Log;

abstract class AbstractDrinkBeer implements AchievementStrategy
{
    protected const BEER_COUNT_THRESHOLD = 5;

    public function handle(User $user, Achievement $achievement): void
    {
        Log::info('AbstractDrinkBeer handle started', [
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
        ]);

        $currentSignup = $user->signups()
            ->whereHas('edition', function ($query) {
                $query->where('year', now()->year);
            })
            ->where('confirmed', true)
            ->first();

        if (!$currentSignup) {
            Log::info('AbstractDrinkBeer - no current signup found');
            return;
        }

        $totalBeers = $currentSignup->beer_count;
        $threshold = $achievement->threshold ?? static::BEER_COUNT_THRESHOLD;

        Log::info('AbstractDrinkBeer - beer count and threshold', [
            'total_beers' => $totalBeers,
            'threshold' => $threshold,
        ]);

        $userAchievement = UserAchievement::where([
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
        ])->first();

        if (!$userAchievement) {
            Log::info('AbstractDrinkBeer - creating new UserAchievement');

            // Create new record
            UserAchievement::create([
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
                'progress' => $totalBeers,
                'achieved_at' => $totalBeers >= $threshold ? now() : null,
            ]);

            Log::info('AbstractDrinkBeer - UserAchievement created');
        } else {
            Log::info('AbstractDrinkBeer - updating existing UserAchievement', [
                'current_achieved_at' => $userAchievement->achieved_at,
                'current_progress' => $userAchievement->progress,
            ]);

            // Update existing record
            $updates = ['progress' => $totalBeers];

            // Only set achieved_at if not already achieved and threshold is met
            if (!$userAchievement->achieved_at && $totalBeers >= $threshold) {
                $updates['achieved_at'] = now();
                Log::info('AbstractDrinkBeer - will set achieved_at');
            }

            $userAchievement->update($updates);
            Log::info('AbstractDrinkBeer - UserAchievement updated');
        }
    }
}
