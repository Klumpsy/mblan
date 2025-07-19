<?php

namespace App\Jobs;

use App\Achievements\AchievementStrategyResolver;
use App\Models\Achievement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAllAchievements implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public function handle(): void
    {
        Log::info('Starting achievement processing for all users');

        $userCount = 0;
        $achievementsAwarded = 0;
        $errors = 0;

        try {
            User::chunk(100, function ($users) use (&$userCount, &$achievementsAwarded, &$errors) {
                foreach ($users as $user) {
                    try {
                        $awarded = $this->processUserAchievements($user);
                        $achievementsAwarded += $awarded;
                        $userCount++;
                    } catch (\Exception $e) {
                        $errors++;
                        Log::error('Failed to process achievements for user', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });

            Log::info('Achievement processing completed', [
                'users_processed' => $userCount,
                'achievements_awarded' => $achievementsAwarded,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            Log::error('Achievement processing failed completely', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function processUserAchievements(User $user): int
    {
        $awarded = 0;

        $unearned = Achievement::whereNotIn(
            'id',
            $user->achievements()->pluck('achievements.id')
        )->get();

        foreach ($unearned as $achievement) {
            $beforeCount = $user->achievements()->count();

            $strategy = AchievementStrategyResolver::resolve($achievement);

            if ($strategy) {
                $strategy->handle($user, $achievement);

                $afterCount = $user->fresh()->achievements()->count();
                if ($afterCount > $beforeCount) {
                    $awarded++;
                }
            }
        }

        return $awarded;
    }
}
