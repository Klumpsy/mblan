<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\User;
use App\Achievements\AchievementStrategyResolver;

class AchievementService
{
    public static function check(User $user, string $slug): void
    {
        $achievement = Achievement::where('slug', $slug)->first();
        if (! $achievement || $achievement->type !== 'automatic') return;

        $strategy = AchievementStrategyResolver::resolve($achievement);

        if ($strategy) {
            $strategy->handle($user, $achievement);
        }
    }
}
