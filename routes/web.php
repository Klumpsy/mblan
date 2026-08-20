<?php

use App\Http\Controllers\Auth\DiscordController;
use App\Http\Controllers\DiscordInteractionController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\TournamentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Publieke themische landingspagina van de actieve editie.
Route::view('/', 'landing')->name('home');

// De editie-klassieker (Arti in Space). Publiek, net als vroeger op de
// landingspagina; game.sync blijft achter login.
Route::view('/spel', 'spel')->name('spel');

// Login met Discord (OAuth).
Route::get('/auth/discord', [DiscordController::class, 'redirect'])->name('discord.redirect');
Route::get('/auth/discord/callback', [DiscordController::class, 'callback'])->name('discord.callback');

// Discord interaction callback (slash commands + RSVP buttons). Public, but
// every request is authenticated by its Ed25519 signature via middleware.
Route::post('/discord/interactions', [DiscordInteractionController::class, 'handle'])
    ->middleware('discord.signature')
    ->name('discord.interactions');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::redirect('/dashboard', '/schedule')->name('dashboard');

    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
    Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');
    Route::get('/nieuws', [NewsController::class, 'index'])->name('news.index');
    Route::get('/nieuws/{news}', [NewsController::class, 'show'])->name('news.show');
    Route::get('/tournaments', [TournamentController::class, 'index'])->name('tournaments');
    Route::get('/tijdlijn', [TimelineController::class, 'index'])->name('timeline');
    Route::get('/edities', [\App\Http\Controllers\EditionController::class, 'index'])->name('editions.index');
    Route::get('/edities/{edition:slug}', [\App\Http\Controllers\EditionController::class, 'show'])->name('editions.show');
    Route::view('/pizza', 'pizza.index')->name('pizza');
    Route::view('/live', 'live.index')->name('live');

    // Persist the barn-maze attempt stats onto the account (Arti Game leaderboard).
    // Only a completed run counts, and each completed run is authoritative: the
    // latest attempt overwrites the stored catches and time, so hitting "Opnieuw"
    // and replaying truly resets the recorded count.
    Route::post('/game/sync', function (\Illuminate\Http\Request $request) {
        if (!$request->boolean('completed')) {
            return response()->json(['ok' => true]);
        }

        $edition = \App\Models\Edition::current();
        if (! $edition) {
            return response()->json(['ok' => true]);
        }

        $user = $request->user();

        // De editie-klassieker (space shooter): punten, hoogste wint. Alleen
        // een verbetering van het persoonlijke record wordt opgeslagen.
        if ($request->has('score')) {
            $score = max(0, (int) $request->input('score', 0));

            $previous = \App\Models\GameResult::where('user_id', $user->id)
                ->where('edition_id', $edition->id)
                ->first();

            $improved = $score > (int) ($previous?->score ?? -1);

            if ($improved) {
                \App\Models\GameResult::updateOrCreate(
                    ['user_id' => $user->id, 'edition_id' => $edition->id],
                    ['score' => $score, 'completed' => true],
                );

                // Announce only when the improvement makes this player number one.
                $leader = \App\Models\GameResult::query()
                    ->join('users', 'users.id', '=', 'game_results.user_id')
                    ->where('game_results.edition_id', $edition->id)
                    ->where('game_results.completed', true)
                    ->whereNotNull('game_results.score')
                    ->orderByDesc('game_results.score')
                    ->orderBy('users.name')
                    ->select('game_results.*')
                    ->first();

                if ($leader && $leader->user_id === $user->id) {
                    app(\App\Services\DiscordWebhookService::class)
                        ->announceGameRecord($user, $score);
                }
            }

            return response()->json(['ok' => true]);
        }
        $caught = max(0, (int) $request->input('caught', 0));
        $time = (int) $request->input('time', 0);
        $newTime = $time > 0 ? $time : null;

        // A genuine personal improvement (used only to gate the Discord record
        // announcement): a first completion, fewer catches, or the same catches
        // in a faster time. Captured before the stats are overwritten below.
        $previous = \App\Models\GameResult::where('user_id', $user->id)
            ->where('edition_id', $edition->id)
            ->first();
        $improved = ! $previous?->completed
            || $caught < $previous->catches
            || ($caught === $previous->catches
                && $newTime !== null
                && ($previous->time_ms === null || $newTime < $previous->time_ms));

        // Each completed run is authoritative: the latest attempt overwrites the
        // stored stats, so hitting "Opnieuw" and replaying truly resets the
        // recorded catch count instead of keeping an older personal best.
        \App\Models\GameResult::updateOrCreate(
            ['user_id' => $user->id, 'edition_id' => $edition->id],
            ['catches' => $caught, 'completed' => true, 'time_ms' => $newTime],
        );

        // Announce to Discord only when a genuine improvement makes this player
        // the new number one on this edition's Arti leaderboard.
        if ($improved) {
            $leader = \App\Models\GameResult::query()
                ->join('users', 'users.id', '=', 'game_results.user_id')
                ->where('game_results.edition_id', $edition->id)
                ->where('game_results.completed', true)
                ->orderBy('game_results.catches')
                ->orderByRaw('game_results.time_ms IS NULL')
                ->orderBy('game_results.time_ms')
                ->orderBy('users.name')
                ->select('game_results.*')
                ->first();

            if ($leader && $leader->user_id === $user->id) {
                app(\App\Services\DiscordWebhookService::class)
                    ->announceArtiRecord($user, $caught, $newTime);
            }
        }

        return response()->json(['ok' => true]);
    })->name('game.sync');
});
