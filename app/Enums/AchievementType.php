<?php

namespace App\Enums;

enum AchievementType: string
{
    case GAME_LIKE_5 = '5-game-likes';
    case GAME_LIKE_10 = '10-game-likes';
    case GAME_LIKE_20 = '20-game-likes';
    case FIRST_SIGNUP = 'first-signup';
    case FIRST_TOURNAMENT = 'first-tournament';
    case TOURNAMENT_SIGNUPS_3 = '3-tournament-signups';
    case WIN_HEARTHSTONE_TOURNAMENT = 'win-hearthstone-tournament';
    case HEARTHSTONE_TOURNAMENT_RUNNER_UP = 'hearthstone-tournament-runner-up';
    case HEARTHSTONE_TOURNAMENT_THIRD_PLACE = 'hearthstone-tournament-third-place';
    case WIN_MINECRAFT_TOURNAMENT = 'win-minecraft-tournament';
    case MINECRAFT_TOURNAMENT_RUNNER_UP = 'minecraft-tournament-runner-up';
    case MINECRAFT_TOURNAMENT_THIRD_PLACE = 'minecraft-tournament-third-place';
    case WIN_ZEEPKIST_TOURNAMENT = 'win-zeepkist-tournament';
    case ZEEPKIST_TOURNAMENT_RUNNER_UP = 'zeepkist-tournament-runner-up';
    case ZEEPKIST_TOURNAMENT_THIRD_PLACE = 'zeepkist-tournament-third-place';
    case JOIN_BARBECUE = 'join-barbecue';
    case JOIN_CAMPING = 'join-camping';
    case JOIN_EDITION_24 = 'join-edition_24';
    case JOIN_EDITION_25 = 'join-edition_25';
    case GET_TSHIRT_25 = 'get_tshirt_25';
    case DRINK_5_BEERS = 'drink-5-beers';
    case DRINK_10_BEERS = 'drink-10-beers';
    case DRINK_15_BEERS = 'drink-15-beers';
    case DRINK_20_BEERS = 'drink-20-beers';
    case DRINK_24_BEERS = 'drink-24-beers';
    case DRINK_30_BEERS = 'drink-30-beers';
    case DRINK_40_BEERS = 'drink-40-beers';
    case DRINK_48_BEERS = 'drink-48-beers';

    public static function availableSlugs(): array
    {
        return collect(self::cases())->pluck('value', 'value')->toArray();
    }
}
