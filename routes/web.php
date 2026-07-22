<?php

use App\Http\Controllers\EditionController;
use App\Http\Controllers\TournamentController;
use App\Models\Edition;
use App\Models\User;
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

    return view('index', [
        'activeEdition' => $activeEdition,
        // A few names purely to decorate the hero with floating avatars.
        'avatarNames' => User::inRandomOrder()->take(8)->pluck('name'),
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
