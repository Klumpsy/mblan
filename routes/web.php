<?php

use App\Http\Controllers\EditionController;
use App\Http\Controllers\TournamentController;
use App\Models\Edition;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public splash. Everything else lives behind login.
Route::get('/', function () {
    $activeEdition = Edition::where('is_active', true)->first()
        ?? Edition::orderByDesc('year')->first();

    $mazePath = public_path('images/farm/maze.json');
    $maze = is_file($mazePath) ? json_decode(file_get_contents($mazePath), true) : null;

    return view('index', [
        'activeEdition' => $activeEdition,
        'maze' => $maze,
    ]);
})->name('home');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Post-login home is the schedule.
    Route::redirect('/dashboard', '/schedule')->name('dashboard');

    // Game roster = the current edition's schedule
    Route::get('/schedule', [EditionController::class, 'schedule'])->name('schedule');

    Route::get('/tournaments', [TournamentController::class, 'index'])->name('tournaments');

    // Persist the barn-maze attempt stats onto the account (for a future leaderboard).
    Route::post('/game/sync', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        $caught = max(0, (int) $request->input('caught', 0));
        $user->forceFill([
            'barn_catches' => max((int) $user->barn_catches, $caught),
            'barn_completed' => (bool) $user->barn_completed || $request->boolean('completed'),
        ])->save();

        return response()->json(['ok' => true, 'barn_catches' => $user->barn_catches]);
    })->name('game.sync');

    // Signup flow for an edition (reached from the schedule page CTA)
    Route::controller(EditionController::class)->group(function () {
        Route::get('/editions/{edition:slug}/signup', 'signup')
            ->name('editions.signup')
            ->middleware('can:signup-edition,edition');
        Route::post('/editions/{edition:slug}/signout', 'signout')
            ->name('editions.signout')
            ->middleware('can:signout-edition,edition');
    });
});
