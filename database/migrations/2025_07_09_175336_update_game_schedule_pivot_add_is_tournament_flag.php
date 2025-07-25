<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_schedule', function (Blueprint $table) {
            $table->boolean('is_tournament')->default(false)->after('game_id');
        });
    }

    public function down(): void
    {
        Schema::table('game_schedule', function (Blueprint $table) {
            $table->dropColumn([
                'is_tournament',
            ]);
        });
    }
};
