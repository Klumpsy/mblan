<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->text('text_block_one')->nullable();
            $table->text('text_block_two')->nullable();
            $table->text('text_block_three')->nullable();
            $table->text('short_description')->nullable();
        });

        Schema::table('games', function (Blueprint $table) {
            if (Schema::hasColumn('games', 'year_of_release')) {
                $table->renameColumn('year_of_release', 'year_of_release');
            }
        });

        Schema::table('games', function (Blueprint $table) {
            if (Schema::hasColumn('games', 'description')) {
                $table->dropColumn('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->text('description')->nullable();
        });

        Schema::table('games', function (Blueprint $table) {
            if (Schema::hasColumn('games', 'year_of_release')) {
                $table->renameColumn('year_of_release', 'year_of_release');
            }
        });

        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn([
                'text_block_one',
                'text_block_two',
                'text_block_three',
                'short_description'
            ]);
        });
    }
};
