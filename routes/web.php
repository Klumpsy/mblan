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
    Route::post('/game/sync', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        $caught = max(0, (int) $request->input('caught', 0));
        $completed = (bool) $user->barn_completed || $request->boolean('completed');

        // Keep the best (fastest) completion time in ms.
        $time = (int) $request->input('time', 0);
        $bestTime = $user->barn_time_ms;
        if ($request->boolean('completed') && $time > 0) {
            $bestTime = $bestTime ? min($bestTime, $time) : $time;
        }

        $user->forceFill([
            'barn_catches' => max((int) $user->barn_catches, $caught),
            'barn_completed' => $completed,
            'barn_time_ms' => $bestTime,
        ])->save();

        return response()->json(['ok' => true]);
    })->name('game.sync');
});
