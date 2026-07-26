<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Site-wide beer counter per user (driven by the Discord /beer command and
     * editable by admins), replacing the old standalone Node bot counter.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('beer_count')->default(0)->after('barn_time_ms');
            $table->timestamp('last_beer_at')->nullable()->after('beer_count');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['beer_count', 'last_beer_at']);
        });
    }
};
