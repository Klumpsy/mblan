<?php

use App\Http\Controllers\Auth\DiscordController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TournamentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public splash: the Arti maze game. Everything else lives behind login.
Route::get('/', function () {
    $mazePath = public_path('images/farm/maze.json');
    $maze = is_file($mazePath) ? json_decode(file_get_contents($mazePath), true) : null;

    return view('index', ['maze' => $maze]);
})->name('home');

// Login met Discord (OAuth).
Route::get('/auth/discord', [DiscordController::class, 'redirect'])->name('discord.redirect');
Route::get('/auth/discord/callback', [DiscordController::class, 'callback'])->name('discord.callback');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::redirect('/dashboard', '/schedule')->name('dashboard');

    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
    Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');
    Route::get('/tournaments', [TournamentController::class, 'index'])->name('tournaments');
    Route::view('/live', 'live.index')->name('live');

    // Persist the barn-maze attempt stats onto the account (Arti Game leaderboard).
    // Only a completed run counts, and each completed run is authoritative: the
    // latest attempt overwrites the stored catches and time, so hitting "Opnieuw"
    // and replaying truly resets the recorded count.
    Route::post('/game/sync', function (\Illuminate\Http\Request $request) {
        if (!$request->boolean('completed')) {
            return response()->json(['ok' => true]);
        }

        $user = $request->user();
        $caught = max(0, (int) $request->input('caught', 0));
        $time = (int) $request->input('time', 0);
        $newTime = $time > 0 ? $time : null;

        // A genuine personal improvement (used only to gate the Discord record
        // announcement): a first completion, fewer catches, or the same catches
        // in a faster time. Captured before the stats are overwritten below.
        $wasCompleted = (bool) $user->barn_completed;
        $prevCatches = (int) $user->barn_catches;
        $prevTime = $user->barn_time_ms;
        $improved = ! $wasCompleted
            || $caught < $prevCatches
            || ($caught === $prevCatches
                && $newTime !== null
                && ($prevTime === null || $newTime < $prevTime));

        // Each completed run is authoritative: the latest attempt overwrites the
        // stored stats, so hitting "Opnieuw" and replaying truly resets the
        // recorded catch count instead of keeping an older personal best.
        $user->forceFill([
            'barn_catches' => $caught,
            'barn_completed' => true,
            'barn_time_ms' => $newTime,
        ])->save();

        // Announce to Discord only when a genuine improvement makes this player
        // the new number one on the Arti leaderboard.
        if ($improved) {
            $leader = \App\Models\User::where('barn_completed', true)
                ->orderBy('barn_catches')
                ->orderByRaw('barn_time_ms IS NULL')
                ->orderBy('barn_time_ms')
                ->orderBy('name')
                ->first();

            if ($leader && $leader->id === $user->id) {
                app(\App\Services\DiscordWebhookService::class)
                    ->announceArtiRecord($user, $caught, $newTime);
            }
        }

        return response()->json(['ok' => true]);
    })->name('game.sync');
});
