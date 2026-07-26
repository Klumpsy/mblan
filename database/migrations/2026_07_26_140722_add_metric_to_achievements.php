<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `metric` drives automatic achievements: it names which live value the
     * evaluator compares against `threshold` (see App\Support\AchievementMetrics).
     * Null means the achievement is granted manually by an admin.
     */
    public function up(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            if (! Schema::hasColumn('achievements', 'metric')) {
                $table->string('metric')->nullable()->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            if (Schema::hasColumn('achievements', 'metric')) {
                $table->dropColumn('metric');
            }
        });
    }
};
