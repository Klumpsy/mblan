<?php

namespace App\Achievements;

use App\Interfaces\AchievementStrategy;
use App\Models\Achievement;
use App\Models\User;

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

        if ($wins >= ($achievement->threshold ?? 1)) {
            $user->achievements()->syncWithoutDetaching([
                $achievement->id => [
                    'achieved_at' => now(),
                    'progress' => $wins,
                ],
            ]);
        } else {
            $user->achievements()->syncWithoutDetaching([
                $achievement->id => ['progress' => $wins],
            ]);
        }
    }
}
