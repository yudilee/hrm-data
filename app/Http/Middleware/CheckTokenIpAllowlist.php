<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckTokenIpAllowlist
{
    public function handle(Request $request, Closure $next)
    {
        $user  = $request->user('sanctum');
        $token = $user?->currentAccessToken();

        if ($token && !empty($token->allowed_ips)) {
            $allowedIps  = is_array($token->allowed_ips)
                ? $token->allowed_ips
                : json_decode($token->allowed_ips, true) ?? [];

            $clientIp = $request->ip();

            if (!empty($allowedIps) && !in_array($clientIp, $allowedIps, true)) {
                Log::channel('security')->warning('API token used from non-allowlisted IP', [
                    'token_id'   => $token->id,
                    'token_name' => $token->name,
                    'allowed'    => $allowedIps,
                    'actual_ip'  => $clientIp,
                    'path'       => $request->path(),
                ]);

                return response()->json([
                    'success' => false,
                    'error'   => 'ip_not_allowed',
                    'message' => 'Access denied: your IP address is not permitted for this token.',
                ], 403);
            }
        }

        return $next($request);
    }
}
