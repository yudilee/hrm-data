<?php

declare(strict_types=1);

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckTokenIpAllowlist;
use App\Http\Middleware\LogApiAccess;
use App\Http\Middleware\UpdateSessionActivity;
use App\Http\Middleware\VerifyOdooSignature;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'api.log' => LogApiAccess::class,
            'api.ip-allowlist' => CheckTokenIpAllowlist::class,
            'odoo.signature' => VerifyOdooSignature::class,
        ]);

        $middleware->web(append: [
            UpdateSessionActivity::class,
        ]);

        $middleware->api(append: [
            LogApiAccess::class,
            CheckTokenIpAllowlist::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'not_found',
                    'message' => 'The requested resource was not found.',
                ], 404);
            }
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'http_error',
                    'message' => $e->getMessage() ?: 'An error occurred.',
                ], $e->getStatusCode());
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                Log::error('API exception', [
                    'error' => $e->getMessage(),
                    'path' => $request->path(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'server_error',
                    'message' => app()->isProduction() ? 'An unexpected error occurred.' : $e->getMessage(),
                ], 500);
            }
        });
    })->create();

// ─── Rate Limiter — per-token override ────────────────────────────────────────
RateLimiter::for('api', function (Request $request) {
    $user = $request->user();
    $token = $user?->currentAccessToken();
    $limit = $token?->rate_limit
        ?? ($user?->role === 'admin' ? 300 : 60);

    return Limit::perMinute($limit)
        ->by($user?->id ?: $request->ip());
});
