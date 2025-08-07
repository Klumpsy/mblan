<?php

namespace App\Achievements;

use App\Interfaces\AchievementStrategy;
use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;

abstract class AbstractWinTournament implements AchievementStrategy
{
    protected const GAME_NAME = 'Hearthstone';
    protected const RANKING = 1;

    public function handle(User $user, Achievement $achievement): void
    {
        $wins = $user->tournaments()
            ->where('concluded', true)
            ->whereHas('game', function ($query) {
                $query->where('name', static::GAME_NAME);
            })
            ->whereHas('usersWithScores', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('ranking', static::RANKING);
            })
            ->count();

        $userAchievement = UserAchievement::firstOrNew([
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
        ]);

        $userAchievement->progress = $wins;
        $threshold = $achievement->threshold ?? 1;
        if ($wins >= $threshold && !$userAchievement->achieved_at) {
            $userAchievement->achieved_at = now();
        }

        $userAchievement->save();
    }
}
