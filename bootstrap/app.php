<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'              => \App\Http\Middleware\CheckRole::class,
            'abilities'         => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability'           => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
            'api.log'           => \App\Http\Middleware\LogApiAccess::class,
            'api.ip-allowlist'  => \App\Http\Middleware\CheckTokenIpAllowlist::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\UpdateSessionActivity::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\LogApiAccess::class,
            \App\Http\Middleware\CheckTokenIpAllowlist::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// ─── Rate Limiter — per-token override ────────────────────────────────────────
RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
    $user  = $request->user();
    $token = $user?->currentAccessToken();
    $limit = $token?->rate_limit
        ?? ($user?->role === 'admin' ? 300 : 60);

    return \Illuminate\Cache\RateLimiting\Limit::perMinute($limit)
        ->by($user?->id ?: $request->ip());
});


