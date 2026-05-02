<?php

declare(strict_types=1);

namespace App\Http\Controllers\Odoo;

use App\Http\Controllers\Controller;
use App\Models\LabourCode;
use App\Models\MasterVehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LabourSelectController extends Controller
{
    /**
     * Show the labour code selection page.
     *
     * This page is opened from Odoo via a signed URL. The middleware
     * has already verified the HMAC signature, expiry, and nonce.
     */
    public function show(Request $request)
    {
        $chassis = $request->input('chassis', '');
        $prefix = strlen($chassis) >= 6 ? substr(strtoupper($chassis), 0, 6) : '';

        // Look up labour codes for this model prefix
        $codes = collect();
        if ($prefix) {
            $codes = LabourCode::ofPrefix($prefix)
                ->orderBy('group_name')
                ->orderBy('code')
                ->get();
        }

        $grouped = $codes->groupBy('group_name');

        // Try to find the vehicle record
        $vehicle = null;
        if (strlen($chassis) >= 17) {
            $vehicle = MasterVehicle::with('customer')
                ->where('chassis_no', strtoupper($chassis))
                ->first();
        }

        return view('odoo.select-labour', [
            'codes' => $grouped,
            'allCodes' => $codes,
            'chassis' => $chassis,
            'modelPrefix' => $prefix,
            'vehicle' => $vehicle,
            'jobNumber' => $request->input('job_number', ''),
            'jobOrderId' => $request->input('job_order_id', ''),
            'callbackUrl' => $request->input('callback_url', ''),
            'customerName' => $request->input('customer_name', ''),
        ]);
    }

    /**
     * Process the selected labour codes and send them to Odoo via webhook.
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'job_order_id' => 'required',
            'job_number' => 'nullable|string',
            'callback_url' => 'required|url',
            'selected_codes' => 'required|array|min:1',
            'selected_codes.*' => 'integer|exists:labour_codes,id',
        ]);

        // Validate callback URL domain against allowlist
        $callbackHost = parse_url($validated['callback_url'], PHP_URL_HOST);
        $allowedHosts = config('services.odoo.allowed_callback_hosts', []);

        if (! empty($allowedHosts) && ! in_array($callbackHost, $allowedHosts, true)) {
            $debugInfo = "Blocked Host: '$callbackHost'. Allowed: [" . implode(', ', $allowedHosts) . "]";
            Log::channel('security')->warning('Odoo callback: blocked non-allowlisted host', [
                'host' => $callbackHost,
                'allowed' => $allowedHosts,
                'ip' => $request->ip(),
            ]);
            abort(403, "Callback URL domain is not in the allowlist. ($debugInfo)");
        }

        // Fetch the selected labour codes
        $codes = LabourCode::whereIn('id', $validated['selected_codes'])->get();

        $payload = [
            'job_order_id' => $validated['job_order_id'],
            'source' => 'rts_labour_app',
            'timestamp' => now()->toIso8601String(),
            'labours' => $codes->map(fn ($c) => [
                'rts_id' => $c->id,
                'code' => $c->code,
                'labour_key' => $c->labour_key,
                'description' => $c->description,
                'group_name' => $c->group_name,
                'time_hours' => (float) $c->time_hours,
            ])->values()->toArray(),
        ];

        // Sign the payload with webhook secret
        $webhookSecret = config('services.odoo.webhook_secret');
        $payloadJson = json_encode($payload);
        $signature = hash_hmac('sha256', $payloadJson, $webhookSecret);

        try {
            $response = Http::withHeaders([
                'X-RTS-Signature' => $signature,
                'X-RTS-Timestamp' => (string) time(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(15)->withBody($payloadJson, 'application/json')
                ->post($validated['callback_url']);

            if ($response->successful()) {
                Log::info('Odoo labour callback sent successfully', [
                    'job_order_id' => $validated['job_order_id'],
                    'codes_count' => $codes->count(),
                    'callback_url' => $validated['callback_url'],
                ]);

                if (view()->exists('odoo.success')) {
                    return view('odoo.success', [
                        'jobOrderId' => $validated['job_order_id'],
                    ]);
                }

                return response("Selection for {$validated['job_order_id']} exported successfully! You can close this window.");
            }

            Log::error('Odoo labour callback failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'job_order_id' => $validated['job_order_id'],
            ]);

            return back()
                ->withInput()
                ->withErrors(['callback' => 'Odoo returned an error (HTTP ' . $response->status() . '). Please try again or contact IT support.']);

        } catch (\Exception $e) {
            Log::error('Odoo labour callback exception', [
                'error' => $e->getMessage(),
                'job_order_id' => $validated['job_order_id'],
            ]);

            return back()
                ->withInput()
                ->withErrors(['callback' => 'Failed to connect to Odoo: ' . $e->getMessage()]);
        }
    }
}
