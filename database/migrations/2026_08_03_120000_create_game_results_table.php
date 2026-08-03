<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The entree-game (Arti maze) results move from columns on users to a row per
 * player per edition, so every edition gets its own game and leaderboard.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('edition_id')->constrained();
            $table->unsignedInteger('catches')->default(0);
            $table->boolean('completed')->default(false);
            $table->unsignedInteger('time_ms')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'edition_id']);
        });

        // Move the existing barn stats over to the active edition.
        $editionId = DB::table('editions')->where('is_active', true)->value('id');

        if ($editionId) {
            DB::table('users')
                ->where(fn ($q) => $q->where('barn_completed', true)->orWhere('barn_catches', '>', 0))
                ->orderBy('id')
                ->each(function ($user) use ($editionId) {
                    DB::table('game_results')->insert([
                        'user_id' => $user->id,
                        'edition_id' => $editionId,
                        'catches' => (int) $user->barn_catches,
                        'completed' => (bool) $user->barn_completed,
                        'time_ms' => $user->barn_time_ms,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['barn_catches', 'barn_completed', 'barn_time_ms']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('barn_catches')->default(0);
            $table->boolean('barn_completed')->default(false);
            $table->unsignedInteger('barn_time_ms')->nullable();
        });

        Schema::dropIfExists('game_results');
    }
};
