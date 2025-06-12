<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Signup;
use App\Observers\SignupObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Signup::observe(SignupObserver::class);
    }
}
