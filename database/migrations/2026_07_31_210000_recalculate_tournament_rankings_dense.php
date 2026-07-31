<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Rankings switched from competition style (1, 1, 3) to dense (1, 1, 2).
     * Recalculate every tournament once so stored rankings match.
     */
    public function up(): void
    {
        \App\Models\Tournament::all()->each->recalculateRankings();
    }

    public function down(): void
    {
        // Nothing to restore: rankings are derived data.
    }
};
