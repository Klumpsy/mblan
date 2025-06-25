<?php

namespace App\Enums;

enum AchievementType: string
{
    case GAME_LIKE_5 = '5-game-likes';
    case GAME_LIKE_10 = '10-game-likes';
    case FIRST_SIGNUP = 'first-signup';
    case FIRST_TOURNAMENT = 'first-tournament';
    case TOURNAMENT_SIGNUPS_3 = '3-tournament-signups';
    case JOIN_BARBECUE = 'join-barbecue';
    case JOIN_CAMPING = 'join-camping';
    case JOIN_EDITION_24 = 'join-edition_24';
    case JOIN_EDITION_25 = 'join-edition_25';
    case GET_TSHIRT_25 = 'get_tshirt_25';

    public static function availableSlugs(): array
    {
        return collect(self::cases())->pluck('value', 'value')->toArray();
    }
}
