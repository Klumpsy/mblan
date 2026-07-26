<?php

use App\Models\User;
use App\Services\AchievementEvaluator;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * One-off backfill so already-registered players immediately see the
     * achievements they've earned (signups, editions, tournaments, ...). Runs
     * silently — no Discord spam — and per-user guarded so a single bad row can
     * never fail the deploy. Re-run any time with `php artisan achievements:backfill`.
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
        // Not reversible: leaves earned achievements in place.
    }
};
