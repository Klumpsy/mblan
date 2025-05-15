<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_schedule', function (Blueprint $table) {
            $table->foreignId('game_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('schedule_id')
                ->constrained()
                ->onDelete('cascade');
            $table->primary(['game_id', 'schedule_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_schedule');
    }
};
