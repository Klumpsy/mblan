<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * De nieuwe editie-klassieker (space shooter) scoort punten: hoogste wint.
 * Oude maze-resultaten (catches/time_ms) blijven staan met score NULL, zodat
 * de MBLAN26-recap zijn eigen uitslag houdt.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_results', function (Blueprint $table) {
            $table->unsignedInteger('score')->nullable()->after('catches');
        });
    }

    public function down(): void
    {
        Schema::table('game_results', function (Blueprint $table) {
            $table->dropColumn('score');
        });
    }
};
