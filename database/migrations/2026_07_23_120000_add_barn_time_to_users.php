<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'barn_time_ms')) {
                // Best (fastest) completion time of the Arti maze, in milliseconds.
                $table->unsignedInteger('barn_time_ms')->nullable()->after('barn_completed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('barn_time_ms');
        });
    }
};
