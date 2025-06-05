<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('editions', function (Blueprint $table) {
            if (!Schema::hasColumn('editions', 'media_id')) {
                $table->foreignId('media_id')->constrained('media')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn('media_id');
        });
    }
};
