<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EditionController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\TournamentController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
    });
    Route::controller(GameController::class)->group(function () {
        Route::get('/games', 'index')->name('games');
        Route::get('/games/{id}', 'show')->name('games.show');
    });
    Route::controller(EditionController::class)->group(function () {
        Route::get('/editions', 'index')->name('editions');
        Route::get('/editions/{id}', 'show')->name('editions.show');
        Route::get('/editions/{id}/signup', 'signup')->name('editions.signup');
    });
    Route::controller(TournamentController::class)->group(function () {
        Route::get('/tournaments', 'index')->name('tournaments')->middleware('can:hasConfirmedSignup,' . User::class);
        Route::get('/tournament/{id}', 'show')->name('tournaments.show');
    });
    Route::controller(MediaController::class)->group(function () {
        Route::get('/media', 'index')->name('media');
    });
});
