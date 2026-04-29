<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Gate;

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
        // Rate limiter: max 5 login attempts per minute per IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // API rate limiting: admin gets 300/min, users get 60/min
        RateLimiter::for('api', function (Request $request) {
            $user = $request->user();
            return $user?->role === 'admin'
                ? Limit::perMinute(300)->by($user->id)
                : Limit::perMinute(60)->by($user?->id ?: $request->ip());
        });

        // Password strength defaults
        Password::defaults(fn () => Password::min(8));

        // Allow access to Scramble API documentation in all environments
        Gate::define('viewApiDocs', function (?$user) {
            // Return true to make docs public, or add logic to restrict it
            return true;
        });
    }
}
