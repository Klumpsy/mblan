<?php

namespace App\Achievements;

use App\Achievements\FirstSignupAchievement;
use App\Achievements\FirstTournamentAchievement;
use App\Achievements\TournamentSignup3Achievement;
use App\Interfaces\AchievementStrategy;
use App\Enums\AchievementType;
use App\Models\Achievement;

class AchievementStrategyResolver
{
    public static function resolve(Achievement $achievement): ?AchievementStrategy
    {
        return match ($achievement->slug) {
            AchievementType::FIRST_TOURNAMENT->value => new FirstTournamentAchievement(),
            AchievementType::TOURNAMENT_SIGNUPS_3->value => new TournamentSignup3Achievement(),
            AchievementType::FIRST_SIGNUP->value => new FirstSignupAchievement(),
            AchievementType::WIN_HEARTHSTONE_TOURNAMENT->value => new WinHearthstoneTournamentAchievement(),
            AchievementType::HEARTHSTONE_TOURNAMENT_RUNNER_UP->value => new HearthstoneTournamentRunnerUpAchievement(),
            AchievementType::HEARTHSTONE_TOURNAMENT_THIRD_PLACE->value => new HearthstoneTournamentThirdPlaceAchievement(),
            AchievementType::WIN_MINECRAFT_TOURNAMENT->value => new WinMinecraftTournamentAchievement(),
            AchievementType::MINECRAFT_TOURNAMENT_RUNNER_UP->value => new MinecraftTournamentRunnerUpAchievement(),
            AchievementType::MINECRAFT_TOURNAMENT_THIRD_PLACE->value => new MinecraftTournamenthirdPlaceAchievement(),
            AchievementType::WIN_ZEEPKIST_TOURNAMENT->value => new WinZeepkistTournamentAchievement(),
            AchievementType::ZEEPKIST_TOURNAMENT_RUNNER_UP->value => new ZeepkistTournamentRunnerUpAchievement(),
            AchievementType::ZEEPKIST_TOURNAMENT_THIRD_PLACE->value => new ZeepkistTournamentThirdPlaceAchievement(),
            AchievementType::GAME_LIKE_5->value => new GameLikes5Achievement(),
            AchievementType::GAME_LIKE_10->value => new GameLikes10Achievement(),
            AchievementType::GAME_LIKE_20->value => new GameLikes20Achievement(),
            AchievementType::JOIN_BARBECUE->value => new JoinBarbecueAchievement(),
            AchievementType::JOIN_CAMPING->value => new JoinCampingAchievement(),
            AchievementType::GET_TSHIRT_25->value => new GetTshirtAchievement(),
            AchievementType::DRINK_5_BEERS->value => new Drink5BeersAchievement(),
            AchievementType::DRINK_10_BEERS->value => new Drink10BeersAchievement(),
            AchievementType::DRINK_15_BEERS->value => new Drink15BeersAchievement(),
            AchievementType::DRINK_20_BEERS->value => new Drink20BeersAchievement(),
            AchievementType::DRINK_24_BEERS->value => new Drink24BeersAchievement(),
            AchievementType::DRINK_30_BEERS->value => new Drink30BeersAchievement(),
            AchievementType::DRINK_40_BEERS->value => new Drink40BeersAchievement(),
            AchievementType::DRINK_48_BEERS->value => new Drink48BeersAchievement,
            default => null,
        };
    }
}
