<?php

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

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::redirect('/dashboard', '/schedule')->name('dashboard');

    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
    Route::get('/tournaments', [TournamentController::class, 'index'])->name('tournaments');

    // Persist the barn-maze attempt stats onto the account (Arti Game leaderboard).
    // Only a completed run counts, and we keep each player's personal best:
    // the fewest catches and the fastest time. Replaying can only improve you.
    Route::post('/game/sync', function (\Illuminate\Http\Request $request) {
        if (!$request->boolean('completed')) {
            return response()->json(['ok' => true]);
        }

        $user = $request->user();
        $caught = max(0, (int) $request->input('caught', 0));
        $time = (int) $request->input('time', 0);

        $bestCatches = $user->barn_completed ? min((int) $user->barn_catches, $caught) : $caught;
        $bestTime = $user->barn_time_ms;
        if ($time > 0) {
            $bestTime = $bestTime ? min($bestTime, $time) : $time;
        }

        $user->forceFill([
            'barn_catches' => $bestCatches,
            'barn_completed' => true,
            'barn_time_ms' => $bestTime,
        ])->save();

        return response()->json(['ok' => true]);
    })->name('game.sync');
});
