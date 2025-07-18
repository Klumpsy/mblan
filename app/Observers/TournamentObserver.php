<?php

namespace App\Observers;

use App\Enums\AchievementType;
use App\Jobs\SendDiscordTournamentAnnouncement;
use App\Models\Tournament;
use App\Models\User;
use App\Services\AchievementService;
use App\Services\DiscordWebhookService;

class TournamentObserver
{

    public function __construct(
        private DiscordWebhookService $discordService
    ) {}

    public function created(Tournament $tournament): void
    {
        //
    }

    public function updated(Tournament $tournament): void
    {
        if ($tournament->isDirty('is_active') && $tournament->is_active) {
            if (config('discord.queue_announcements', true)) {
                SendDiscordTournamentAnnouncement::dispatch($tournament, 'started');
            } else {
                $this->discordService->announceTournament($tournament);
            }
        }

        if ($tournament->isDirty('is_active') && !$tournament->is_active) {
            if (config('discord.queue_announcements', true)) {
                SendDiscordTournamentAnnouncement::dispatch($tournament, 'ended');
                SendDiscordTournamentAnnouncement::dispatch($tournament, 'results');
            } else {
                $this->discordService->announceTournamentEnd($tournament);
                $this->discordService->sendTournamentResults($tournament);
            }
        }
    }

    /**
     * Handle the Tournament "deleted" event.
     */
    public function deleted(Tournament $tournament): void
    {
        //
    }

    /**
     * Handle the Tournament "restored" event.
     */
    public function restored(Tournament $tournament): void
    {
        //
    }

    /**
     * Handle the Tournament "force deleted" event.
     */
    public function forceDeleted(Tournament $tournament): void
    {
        //
    }

    public function attached(Tournament $tournament, $relation, $pivotIds)
    {
        if ($relation === 'usersWithScores') {
            foreach ($pivotIds as $userId) {
                $user = User::find($userId);

                AchievementService::check($user, AchievementType::FIRST_TOURNAMENT->value);
                AchievementService::check($user, AchievementType::TOURNAMENT_SIGNUPS_3->value);
            }
        }
    }
}
