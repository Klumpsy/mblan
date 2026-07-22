<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            // Scoring preset per game type: points | time | wins | kills | goals | rounds | custom
            if (!Schema::hasColumn('tournaments', 'scoring_type')) {
                $table->string('scoring_type')->default('points')->after('is_team_based');
            }
            // What one unit of "score" is called in the UI (Points, Seconds, Kills, ...).
            if (!Schema::hasColumn('tournaments', 'score_label')) {
                $table->string('score_label')->default('Punten')->after('scoring_type');
            }
            // Sort direction: true = higher score wins (points/kills), false = lower wins (time/penalties).
            if (!Schema::hasColumn('tournaments', 'higher_is_better')) {
                $table->boolean('higher_is_better')->default(true)->after('score_label');
            }
            // Optional free-form rules shown to players.
            if (!Schema::hasColumn('tournaments', 'rules')) {
                $table->text('rules')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn(['scoring_type', 'score_label', 'higher_is_better', 'rules']);
        });
    }
};
