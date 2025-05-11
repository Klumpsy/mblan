<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\TournamentController;
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
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/games', [GameController::class, 'index'])->name('games');
    Route::get('/games/create', [GameController::class, 'create'])
        ->name('games.create')
        ->middleware('role:admin');
    Route::get('/games/{id}', [GameController::class, 'show'])->name('games.show');
    Route::post('/games', [GameController::class, 'store'])
        ->name('games.store')
        ->middleware('role:admin');
    Route::get('/tournaments', [TournamentController::class, 'index'])->name('tournaments');
}); 

