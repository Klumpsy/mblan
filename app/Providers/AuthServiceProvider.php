<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Edition;
use App\Models\Tournament;
use App\Models\User;
use App\Policies\EditionPolicy;
use App\Policies\TournamentPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Edition::class => EditionPolicy::class,
        Tournament::class => TournamentPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('access-admin-panel', function (User $user) {
            return $user->hasRole('admin');
        });
    }
}
