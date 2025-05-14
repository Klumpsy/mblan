<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->text('textBlockOne')->nullable();
            $table->text('textBlockTwo')->nullable();
            $table->text('textBlockThree')->nullable();
            $table->text('shortDescription')->nullable();
        });
        
        Schema::table('games', function (Blueprint $table) {
            if (Schema::hasColumn('games', 'year_of_release')) {
                $table->renameColumn('year_of_release', 'yearOfRelease');
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
            if (Schema::hasColumn('games', 'yearOfRelease')) {
                $table->renameColumn('yearOfRelease', 'year_of_release');
            }
        });
        
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn([
                'textBlockOne',
                'textBlockTwo',
                'textBlockThree',
                'shortDescription'
            ]);
        });
    }
};