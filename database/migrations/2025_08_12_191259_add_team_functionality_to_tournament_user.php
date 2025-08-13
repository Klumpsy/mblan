<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournament_user', function (Blueprint $table) {
            $table->string('team_name')->nullable()->after('ranking');
            $table->integer('team_number')->nullable()->after('team_name');
            $table->integer('team_score')->nullable()->after('team_number');
        });
    }

    public function down(): void
    {
        Schema::table('tournament_user', function (Blueprint $table) {
            $table->dropColumn(['team_name', 'team_number', 'team_score']);
        });
    }
};
