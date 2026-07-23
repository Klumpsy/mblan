<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Editions are gone: the site is a single event. Decouple schedules/signups/media
 * from editions and drop the edition tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('signups', 'edition_id')) {
            // Drop both FKs so the composite unique (which the user_id FK leans on) can go.
            Schema::table('signups', function (Blueprint $table) {
                $table->dropForeign(['edition_id']);
                $table->dropForeign(['user_id']);
            });
            Schema::table('signups', function (Blueprint $table) {
                $table->dropUnique('signups_user_id_edition_id_unique');
            });
            Schema::table('signups', function (Blueprint $table) {
                $table->dropColumn('edition_id');
            });
            // Restore the user_id FK (recreates its own index).
            Schema::table('signups', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        foreach (['schedules', 'media'] as $tbl) {
            if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'edition_id')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->dropForeign(['edition_id']);
                });
                Schema::table($tbl, function (Blueprint $table) {
                    $table->dropColumn('edition_id');
                });
            }
        }

        Schema::dropIfExists('edition_user_exclusive');
        Schema::dropIfExists('edition_user');
        Schema::dropIfExists('editions');
    }

    public function down(): void
    {
        // One-way simplification; editions are not restored.
    }
};
