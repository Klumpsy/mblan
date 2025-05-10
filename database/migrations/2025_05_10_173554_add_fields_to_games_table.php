<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->year('year_of_release')->nullable();
            $table->string('image')->nullable();
            $table->string('linkToWebsite')->nullable();
            $table->string('linkToYoutube')->nullable();
            $table->integer('likes')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'year_of_release',
                'image',
                'linkToWebsite',
                'linkToYoutube',
                'likes',
            ]);
        });
    }
};
