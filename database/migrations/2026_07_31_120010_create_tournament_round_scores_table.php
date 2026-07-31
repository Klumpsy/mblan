<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_round_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_round_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('points')->default(0);
            $table->timestamps();

            $table->unique(['tournament_round_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_round_scores');
    }
};
