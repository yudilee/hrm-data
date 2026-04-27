<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\OdooService;

class SettingController extends Controller
{
    public function index()
    {
        return view('settings.index');
    }

    public function update(Request $request)
    {
        Setting::set('show_dashboard', $request->has('show_dashboard') ? '1' : '0');
        Setting::set('empty_before_sync', $request->has('empty_before_sync') ? '1' : '0');
        Setting::set('default_bc_manager', $request->input('default_bc_manager', ''));
        Setting::set('default_bc_spv', $request->input('default_bc_spv', ''));
        Setting::set('enable_pdf_watermark', $request->has('enable_pdf_watermark') ? '1' : '0');
        Setting::set('journal_paper_size', $request->input('journal_paper_size', 'A5'));
        Setting::set('odoo_deep_sync_journal', $request->has('odoo_deep_sync_journal') ? '1' : '0');
        return back()->with('success', 'Settings updated successfully.');
    }

    public function emptyDatabase()
    {
        \App\Models\JournalLine::query()->delete();
        \App\Models\JournalEntry::query()->delete();
        
        return back()->with('success', 'Database cleared successfully.');
    }

    /**
     * Truncate labour_codes and re-import from the Data Operation folder.
     */
    public function rebuildLabourCodes(): JsonResponse
    {
        // Prevent concurrent runs
        if (Cache::get('labour_rebuild_running')) {
            return response()->json(['status' => 'already_running', 'message' => 'A rebuild is already in progress.']);
        }

        Cache::put('labour_rebuild_running', true, 600);  // 10-min safety TTL
        Cache::put('labour_rebuild_status', 'running', 600);
        Cache::put('labour_rebuild_log', '', 600);
        Cache::put('labour_rebuild_started_at', now()->toDateTimeString(), 600);
        Cache::forget('labour_rebuild_finished_at');
        Cache::forget('labour_rebuild_result');

        $scriptPath = base_path('../../import_data.py'); // /home/yudi/dev/rts_code/import_data.py

        try {
            $result = \Illuminate\Support\Facades\Process::env([
                'DB_HOST'     => '127.0.0.1',
                'DB_PORT'     => env('FORWARD_DB_PORT', '3309'),
                'DB_USER'     => env('DB_USERNAME', 'sail'),
                'DB_PASSWORD' => env('DB_PASSWORD', 'password'),
                'DB_NAME'     => env('DB_DATABASE', 'rts_labour_app'),
            ])->timeout(300)->run(['python3', $scriptPath]);

            $output = $result->output() . $result->errorOutput();
            $success = str_contains($output, 'Done!') || str_contains($output, 'Successfully inserted');

            $count = DB::table('labour_codes')->count();

            Cache::put('labour_rebuild_status', $success ? 'success' : 'error', 3600);
            Cache::put('labour_rebuild_log', $output, 3600);
            Cache::put('labour_rebuild_result', $success ? "{$count} labour codes loaded" : 'Script finished with errors', 3600);
            Cache::put('labour_rebuild_finished_at', now()->toDateTimeString(), 3600);

        } catch (\Exception $e) {
            Log::error('Labour code rebuild failed', ['error' => $e->getMessage()]);
            Cache::put('labour_rebuild_status', 'error', 3600);
            Cache::put('labour_rebuild_log', $e->getMessage(), 3600);
            Cache::put('labour_rebuild_result', 'Exception: ' . $e->getMessage(), 3600);
            Cache::put('labour_rebuild_finished_at', now()->toDateTimeString(), 3600);
        } finally {
            Cache::forget('labour_rebuild_running');
        }

        return response()->json([
            'status'  => Cache::get('labour_rebuild_status'),
            'result'  => Cache::get('labour_rebuild_result'),
            'log'     => Cache::get('labour_rebuild_log'),
            'finished_at' => Cache::get('labour_rebuild_finished_at'),
        ]);
    }

    /**
     * Poll the current rebuild status (for AJAX progress checking).
     */
    public function rebuildStatus(): JsonResponse
    {
        return response()->json([
            'status'       => Cache::get('labour_rebuild_status', 'idle'),
            'running'      => (bool) Cache::get('labour_rebuild_running', false),
            'result'       => Cache::get('labour_rebuild_result', ''),
            'log'          => Cache::get('labour_rebuild_log', ''),
            'started_at'   => Cache::get('labour_rebuild_started_at', ''),
            'finished_at'  => Cache::get('labour_rebuild_finished_at', ''),
        ]);
    }

    /**
     * Save Odoo configuration
     */
    public function saveOdooConfig(Request $request): JsonResponse
    {
        $request->validate([
            'odoo_url' => 'required|url',
            'odoo_db' => 'required|string',
            'odoo_user' => 'required|string',
            'odoo_password' => 'required|string',
        ]);

        Setting::set('odoo_url', $request->input('odoo_url'));
        Setting::set('odoo_db', $request->input('odoo_db'));
        Setting::set('odoo_user', $request->input('odoo_user'));
        Setting::set('odoo_password', $request->input('odoo_password'));

        return response()->json(['success' => true, 'message' => 'Odoo configuration saved successfully.']);
    }

    /**
     * Test Odoo connection
     */
    public function testOdooConnection(): JsonResponse
    {
        $odoo = new OdooService();
        $result = $odoo->testConnection();
        return response()->json($result);
    }

    /**
     * Get schedule configuration
     */
    public function getSchedule(): JsonResponse
    {
        return response()->json([
            'enabled' => Setting::getValue('odoo_schedule_enabled', 'false') === 'true',
            'interval' => Setting::getValue('odoo_schedule_interval', 'daily'),
            'last_sync' => Setting::getValue('odoo_last_sync', null),
        ]);
    }

    /**
     * Save schedule configuration
     */
    public function saveSchedule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'interval' => 'required|in:hourly,every_2_hours,every_4_hours,every_6_hours,every_12_hours,daily',
        ]);

        Setting::setValue('odoo_schedule_enabled', $validated['enabled'] ? 'true' : 'false');
        Setting::setValue('odoo_schedule_interval', $validated['interval']);

        return response()->json([
            'success' => true,
            'message' => $validated['enabled'] 
                ? "Auto-sync enabled ({$validated['interval']})" 
                : 'Auto-sync disabled',
        ]);
    }
}
