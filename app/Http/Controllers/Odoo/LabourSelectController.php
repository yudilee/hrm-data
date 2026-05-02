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

                $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Successful</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white dark:bg-slate-800 rounded-3xl shadow-xl p-8 text-center border border-slate-100 dark:border-slate-700">
        <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Export Successful!</h1>
        <p class="text-slate-600 dark:text-slate-400 mb-8 text-sm">
            Labour selection for Job Order <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{$validated['job_order_id']}</span> has been sent back to Odoo.
        </p>

        <div class="space-y-3">
            <button onclick="window.close()" class="w-full py-3 bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-semibold rounded-xl hover:opacity-90 transition-all">
                Close Window
            </button>
            <a href="/" class="block w-full py-3 bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-semibold rounded-xl border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 transition-all text-sm text-center">
                Return to Dashboard
            </a>
        </div>
        
        <p class="mt-8 text-[10px] uppercase tracking-widest text-slate-400 font-medium">
            RTS Labour Integration System
        </p>
    </div>
</body>
</html>
HTML;

                return response($html)->header('Content-Type', 'text/html');
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
