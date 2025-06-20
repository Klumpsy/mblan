<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            if (!Schema::hasColumn('editions', 'slug')) {
                $table->string('slug')->unique();
            }
        });
    }

    public function down(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
