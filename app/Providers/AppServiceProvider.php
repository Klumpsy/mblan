<?php

namespace App\Providers;

use App\Models\Game;
use Illuminate\Support\ServiceProvider;
use App\Models\Signup;
use App\Models\Tournament;
use App\Models\User;
use App\Models\UserGame;
use App\Observers\GameObserver;
use App\Observers\SignupObserver;
use App\Observers\TournamentObserver;
use App\Observers\UserGameObserver;
use App\Observers\UserObserver;
use App\Achievements\AchievementStrategyResolver;
use App\Models\BlogComment;
use App\Models\UserAchievement;
use App\Models\UserTournament;
use App\Observers\UserAchievementObserver;
use App\Observers\UserTournamentObserver;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AchievementStrategyResolver::class, function ($app) {
            return new AchievementStrategyResolver();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Signup::observe(SignupObserver::class);
        Game::observe(GameObserver::class);
        UserGame::observe(UserGameObserver::class);
        User::observe(UserObserver::class);
        Tournament::observe(TournamentObserver::class);
        UserTournament::observe(UserTournamentObserver::class);
        UserAchievement::observe(UserAchievementObserver::class);

        Gate::define('delete-blog-comment', function (User $user, BlogComment $blogComment) {
            return $blogComment->author->is($user) || $user->isAdmin();
        });
    }
}
