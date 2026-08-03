<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Editions can carry their own uploaded sprite package (PNG's, beheerd in
 * Filament). Uploaded sprites win over the built-in scenery_set, so a new or
 * backfilled edition only needs a sprite upload to get its own look.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            $table->json('scenery_sprites')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            $table->dropColumn('scenery_sprites');
        });
    }
};
