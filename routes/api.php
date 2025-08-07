<?php

use App\Http\Controllers\DiscordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('discord')->group(function () {
    Route::post('/beer', [DiscordController::class, 'addBeer']);
    Route::get('/beer/leaderboard', [DiscordController::class, 'getBeerLeaderboard']);
});
