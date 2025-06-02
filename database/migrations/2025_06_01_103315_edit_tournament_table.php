<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            if (!Schema::hasColumn('tournaments', 'schedule')) {
                $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
                $table->boolean(('is_active'))->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn('schedule_id');
            $table->dropColumn('is_active');
        });
    }
};