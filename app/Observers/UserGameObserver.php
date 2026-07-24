<?php

namespace App\Observers;

use App\Enums\AchievementType;
use App\Models\UserGame;
use App\Services\AchievementService;
use App\Services\DiscordWebhookService;

class UserGameObserver
{
    /**
     * Handle the UserGame "created" event.
     */
    public function created(UserGame $userGame): void
    {
        AchievementService::check($userGame->user, AchievementType::GAME_LIKE_5->value);
        AchievementService::check($userGame->user, AchievementType::GAME_LIKE_10->value);
        AchievementService::check($userGame->user, AchievementType::GAME_LIKE_20->value);

        $this->announceLikeMilestone($userGame);
    }

    /**
     * Announce to Discord when a game's like count hits a configured milestone.
     * Counts increment one at a time, so an exact match fires each milestone once.
     */
    private function announceLikeMilestone(UserGame $userGame): void
    {
        $game = $userGame->game;

        if (! $game) {
            return;
        }

        $count = $game->likedByUsers()->count();

        if (in_array($count, (array) config('discord.like_milestones', []), true)) {
            app(DiscordWebhookService::class)->announceGameLikeMilestone($game, $count);
        }
    }

    /**
     * Handle the UserGame "updated" event.
     */
    public function updated(UserGame $userGame): void
    {
        //
    }

    /**
     * Handle the UserGame "deleted" event.
     */
    public function deleted(UserGame $userGame): void
    {
        //
    }

    /**
     * Handle the UserGame "restored" event.
     */
    public function restored(UserGame $userGame): void
    {
        //
    }

    /**
     * Handle the UserGame "force deleted" event.
     */
    public function forceDeleted(UserGame $userGame): void
    {
        //
    }
}
