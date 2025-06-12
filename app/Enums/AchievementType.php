<?php

namespace App\Enums;

enum AchievementType: string
{
    case GAME_LIKE_5 = '5-game-likes';
    case GAME_LIKE_10 = '10-game-likes';
    case FIRST_SIGNUP = 'first-signup';
    case FIRST_TOURNAMENT = 'first-tournament';
    case TOURNAMENT_SIGNUPS_3 = '3-tournament-signups';

    public static function availableSlugs(): array
    {
        return collect(self::cases())->pluck('value', 'value')->toArray();
    }
}
