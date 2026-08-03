<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Each edition picks a pixel-sprite scenery set for the page backdrops
 * (MBLAN26 = farm; 2027 gets a space set). Sets live in App\Support\ScenerySets.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            $table->string('scenery_set')->default('farm');
        });
    }

    public function down(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            $table->dropColumn('scenery_set');
        });
    }
};
