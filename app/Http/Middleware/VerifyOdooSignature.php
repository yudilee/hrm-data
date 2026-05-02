<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Verify HMAC-SHA256 signed requests from Odoo.
 *
 * Validates:
 * 1. Signature matches (prevents URL tampering)
 * 2. Timestamp not expired (prevents replay after expiry)
 * 3. Nonce not reused (prevents replay within expiry window)
 */
class VerifyOdooSignature
{
    public function handle(Request $request, Closure $next)
    {
        $secret = config('services.odoo.shared_secret');

        if (empty($secret)) {
            Log::channel('security')->error('Odoo integration: shared_secret not configured');
            abort(503, 'Odoo integration is not configured.');
        }

        // 1. Check expiry (with clock skew tolerance for cross-host Docker deployments)
        $exp = (int) $request->input('exp', 0);
        $skewTolerance = config('services.odoo.skew_tolerance_seconds', 30);
        if (time() - $skewTolerance > $exp) {
            $timeInfo = "Server: " . gmdate('Y-m-d H:i:s') . " UTC. Link Exp: " . gmdate('Y-m-d H:i:s', $exp) . " UTC";
            Log::channel('security')->warning('Odoo signed URL expired', [
                'exp' => $exp,
                'now' => time(),
                'ip' => $request->ip(),
            ]);
            abort(403, "This link has expired. Please try again from Odoo. ($timeInfo)");
        }

        // 2. Verify HMAC signature
        $signature = $request->input('sig', '');
        
        // Only include the parameters that Odoo originally signed
        $signedKeys = ['job_order_id', 'job_number', 'chassis', 'customer_name', 'callback_url', 'nonce', 'exp'];
        $params = $request->only($signedKeys);
        
        ksort($params);
        $message = collect($params)->map(fn ($v, $k) => "{$k}={$v}")->join('&');
        $expected = hash_hmac('sha256', $message, $secret);

        if (! hash_equals($expected, $signature)) {
            Log::channel('security')->warning('Odoo signed URL: invalid signature', [
                'ip' => $request->ip(),
                'params' => array_keys($params),
            ]);
            abort(403, 'Invalid signature. This request may have been tampered with.');
        }

        // 3. Check nonce not reused (prevent replay attacks)
        $nonce = $request->input('nonce', '');
        if (! empty($nonce)) {
            $nonceKey = 'odoo_nonce:' . $nonce;
            if (Cache::has($nonceKey)) {
                Log::channel('security')->warning('Odoo signed URL: nonce replay attempt', [
                    'nonce' => $nonce,
                    'ip' => $request->ip(),
                ]);
                abort(403, 'This link has already been used. Please generate a new one from Odoo.');
            }
            // Store nonce for 2x the URL expiry period
            $expirySeconds = config('services.odoo.url_expiry_seconds', 300);
            Cache::put($nonceKey, true, $expirySeconds * 2);
        }

        return $next($request);
    }
}
