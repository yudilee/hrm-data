<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ApiAccessLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LogApiAccess
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $requestId = Str::uuid()->toString();

        $response = $next($request);

        $elapsed = (int) round((microtime(true) - $startTime) * 1000);

        try {
            $user = $request->user('sanctum');
            $token = $user?->currentAccessToken();

            // Check if the token is expired
            if ($token && isset($token->expires_at) && $token->expires_at && now()->gt($token->expires_at)) {
                Log::channel('security')->warning('Expired token used', [
                    'token_id' => $token->id,
                    'token_name' => $token->name,
                    'ip' => $request->ip(),
                    'path' => $request->path(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'token_expired',
                    'message' => 'This API token has expired. Please request a new token.',
                ], 401)->header('X-Request-Id', $requestId);
            }

            $status = $response->getStatusCode();

            // Sanitise query params — remove sensitive keys
            $queryParams = collect($request->query())->except(['token', 'password', 'secret'])->toArray();

            ApiAccessLog::create([
                'token_id' => $token?->id,
                'token_name' => $token?->name,
                'user_id' => $user?->id,
                'method' => $request->method(),
                'path' => '/'.$request->path(),
                'query_params' => $queryParams ?: null,
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                'response_status' => $status,
                'response_time_ms' => $elapsed,
                'created_at' => now(),
            ]);

            // Log suspicious statuses to the security channel
            if ($status === 401 || $status === 403) {
                Log::channel('security')->warning('API auth failure', [
                    'status' => $status,
                    'path' => $request->path(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'request_id' => $requestId,
                ]);
            }

            if ($status === 429) {
                Log::channel('security')->warning('API rate limit hit', [
                    'path' => $request->path(),
                    'ip' => $request->ip(),
                    'token_id' => $token?->id,
                    'token_name' => $token?->name,
                    'request_id' => $requestId,
                ]);
            }

        } catch (\Throwable $e) {
            // Never let logging break the API response
            Log::error('ApiAccessLog middleware error: '.$e->getMessage());
        }

        return $response->header('X-Request-Id', $requestId);
    }
}
