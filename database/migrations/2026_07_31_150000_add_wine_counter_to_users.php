<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Site-wide wine counter per user, driven by the Discord /wine command.
     * Mirrors the beer counter.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('wine_count')->default(0)->after('last_beer_at');
            $table->timestamp('last_wine_at')->nullable()->after('wine_count');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['wine_count', 'last_wine_at']);
        });
    }
};
