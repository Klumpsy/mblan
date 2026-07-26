<?php

use App\Models\User;
use App\Services\AchievementEvaluator;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Re-sync every user so the "tournaments won / podium" fix takes effect:
     * the evaluator is now self-correcting and clears achievements that were
     * awarded from a live (not yet concluded) tournament ranking. Silent (no
     * Discord), per-user guarded, and safe to re-run via `achievements:backfill`.
     */
    public function up(): void
    {
        $evaluator = app(AchievementEvaluator::class);

        User::query()->chunkById(200, function ($chunk) use ($evaluator) {
            foreach ($chunk as $user) {
                try {
                    $evaluator->sync($user, notify: false);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        });
    }

    public function down(): void
    {
        // Not reversible.
    }
};
