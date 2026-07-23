<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'barn_catches')) {
                $table->unsignedInteger('barn_catches')->default(0)->after('role');
            }
            if (!Schema::hasColumn('users', 'barn_completed')) {
                $table->boolean('barn_completed')->default(false)->after('barn_catches');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['barn_catches', 'barn_completed']);
        });
    }
};
