<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EditionController;
use App\Http\Controllers\TournamentController;
use App\Models\Blog;
use App\Models\Edition;
use App\Models\Tournament;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $activeEdition = Edition::where('is_active', true)->first()
        ?? Edition::orderByDesc('year')->first();

    $activeEdition?->load(['schedules' => fn ($q) => $q->orderBy('date'), 'schedules.games']);

    $tournaments = $activeEdition
        ? Tournament::whereHas('schedule.edition', fn ($q) => $q->where('year', $activeEdition->year))
            ->with(['game', 'schedule'])
            ->get()
        : collect();

    return view('index', [
        'activeEdition' => $activeEdition,
        'latestBlogs' => Blog::published()->latest('published_at')->take(3)->get(),
        'tournaments' => $tournaments,
        'stats' => [
            'editions' => Edition::count(),
            'games' => \App\Models\Game::count(),
            'players' => \App\Models\User::count(),
        ],
    ]);
})->name('home');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Game roster = the current edition's schedule
    Route::get('/schedule', [EditionController::class, 'schedule'])->name('schedule');

    Route::get('/tournaments', [TournamentController::class, 'index'])->name('tournaments');

    Route::controller(BlogController::class)->group(function () {
        Route::get('/blogs', 'index')->name('blogs');
        Route::get('/blogs/{blog:slug}', 'show')->name('blogs.show');
    });

    // Signup flow for an edition (reached from the landing / dashboard CTA)
    Route::controller(EditionController::class)->group(function () {
        Route::get('/editions/{edition:slug}/signup', 'signup')
            ->name('editions.signup')
            ->middleware('can:signup-edition,edition');
        Route::post('/editions/{edition:slug}/signout', 'signout')
            ->name('editions.signout')
            ->middleware('can:signout-edition,edition');
    });
});
