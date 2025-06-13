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
            AchievementType::GAME_LIKE_5->value => new GameLikes5Achievement(),
            AchievementType::GAME_LIKE_10->value => new GameLikes10Achievement(),
            AchievementType::JOIN_BARBECUE->value => new JoinBarbecueAchievement(),
            AchievementType::JOIN_CAMPING->value => new JoinCampingAchievement(),
            default => null,
        };
    }
}
