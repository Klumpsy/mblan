<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AchievementEvaluator;
use Illuminate\Console\Command;

/**
 * Awards automatic achievements to every existing user based on their current
 * data (signups, tournaments, beer, photos, ...). Runs silently by default so a
 * one-off backfill of historical unlocks doesn't flood the Discord channel;
 * pass --notify to announce them.
 */
class BackfillAchievements extends Command
{
    protected $signature = 'achievements:backfill {--notify : Post a Discord message for each newly unlocked achievement}';

    protected $description = 'Sync automatic achievements for all existing users (backfill)';

    public function handle(AchievementEvaluator $evaluator): int
    {
        $notify = (bool) $this->option('notify');
        $unlocked = 0;
        $users = 0;

        User::query()->chunkById(200, function ($chunk) use ($evaluator, $notify, &$unlocked, &$users) {
            foreach ($chunk as $user) {
                $unlocked += count($evaluator->sync($user, $notify));
                $users++;
            }
        });

        $this->info("Backfill klaar: {$users} gebruikers verwerkt, {$unlocked} achievements toegekend.");

        return self::SUCCESS;
    }
}
