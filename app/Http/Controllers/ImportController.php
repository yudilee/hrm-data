<?php

namespace App\Http\Controllers;

use App\Imports\CustomerImport;
use App\Imports\VehicleImport;
use App\Models\ImportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    // Folder paths — configured via config/data-sources.php (.env)
    protected string $customerDir;
    protected string $vehicleDir;
    protected string $supplierDbf;

    public function __construct()
    {
        $this->customerDir = config('data-sources.customer_dir');
        $this->vehicleDir  = config('data-sources.vehicle_dir');
        $this->supplierDbf = config('data-sources.supplier_dbf');
    }

    public function index()
    {
        // Scan folders for available files
        $customerFiles = collect(File::glob($this->customerDir . '/*.xls*'))
            ->map(fn($p) => ['path' => $p, 'name' => basename($p)])
            ->values();

        $vehicleFiles = collect(File::glob($this->vehicleDir . '/*.xls*'))
            ->map(fn($p) => ['path' => $p, 'name' => basename($p)])
            ->values();

        // Last log per import type for activity panel
        $lastLogs = ImportLog::whereIn('import_type', ['customers', 'lvs_vehicles', 'service_history', 'suppliers', 'smart_sync'])
            ->orderByDesc('started_at')
            ->get()
            ->groupBy('import_type')
            ->map(fn($g) => $g->first());

        return view('import.index', [
            'customerFiles' => $customerFiles,
            'vehicleFiles'  => $vehicleFiles,
            'lastLogs'      => $lastLogs,
            'supplierExists' => File::exists($this->supplierDbf),
        ]);
    }

    /**
     * Import customers via the Python DMS pipeline (import_dms_customers.py).
     * Reads from /data cust/*.xls with advanced deduplication logic.
     */
    public function importDmsCustomers()
    {
        $log = ImportLog::start('dms_customers', null, Auth::user()?->name ?? 'system');

        try {
            set_time_limit(0);
            Artisan::call('import:dms-customers');
            $output = Artisan::output();

            if (str_contains($output, 'completed successfully') || str_contains($output, 'Process completed')) {
                $log->complete();
                return back()->with('success', '✓ DMS Customers imported successfully via Python pipeline!');
            }

            $log->fail('Unexpected output: ' . substr($output, -200));
            return back()->with('error', 'DMS Customer import finished with unexpected result: ' . substr($output, -500));

        } catch (\Exception $e) {
            $log->fail($e->getMessage());
            Log::error('DMS customer import error', ['error' => $e->getMessage()]);
            return back()->with('error', 'DMS Customer import error: ' . $e->getMessage());
        }
    }

    /**
     * Import all H04 customer files from the data cust/ folder.
     */
    public function importCustomers()
    {
        $log = ImportLog::start('customers', null, Auth::user()?->name ?? 'system');

        try {
            $files = File::glob($this->customerDir . '/*.xls*');
            if (empty($files)) {
                throw new \Exception('No H04 customer files found in: ' . $this->customerDir);
            }

            $total = 0;
            foreach ($files as $file) {
                // Extract source from filename, e.g. "SBY CV" from "DATA CUST PER 18 APR26 SBY CV(H04).xls"
                preg_match('/(?:APR|MAR|FEB|JAN|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)\d+\s+(.+?)\(H04\)/i', basename($file), $m);
                $source = isset($m[1]) ? 'HRM' . strtoupper(trim($m[1])) : 'IMPORT';
                $source = str_replace(' ', '', $source); // e.g. "HRMSBYPC"

                // Map to standard source codes
                $sourceMap = [
                    'HRMSBYPC'  => 'HRMSBY PC',
                    'HRMSBYCV'  => 'HRMSBY CV',
                    'HRMJKTCV'  => 'HRMJKT CV',
                    'HRMDPSPC'  => 'HRMDPS PC',
                    'HRMDPSCV'  => 'HRMDPS CV',
                    'HRMSMGPC'  => 'HRMSMG PC',
                    'HRMSMGCV'  => 'HRMSMG CV',
                ];
                $source = $sourceMap[$source] ?? $source;

                Excel::import(new CustomerImport($source), $file);
                $total++;
            }

            $log->complete(['files_imported' => $total]);
            return back()->with('success', "✓ Customer data synced! Imported {$total} branch file(s) from server folder.");
        } catch (\Exception $e) {
            $log->fail($e->getMessage());
            Log::error('Customer folder import error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Customer import error: ' . $e->getMessage());
        }
    }

    /**
     * Run the full Smart Sync sequence:
     * Recover Ghosts -> LVS Vehicles -> Backfill Names -> Merge Duplicates.
     */
    public function smartSync(Request $request)
    {
        $log = ImportLog::start('smart_sync', null, Auth::user()?->name ?? 'system');

        try {
            $logPath = storage_path('logs/history_import.log');
            file_put_contents($logPath, "--- Starting Full Master Smart Sync ---\n");

            // Execute in background using absolute paths
            $php = PHP_BINARY;
            $artisan = base_path('artisan');
            // Pass log ID so the command can mark it complete when done
            $cmd = "{$php} {$artisan} rts:import-all --log-id={$log->id} >> {$logPath} 2>&1 &";
            exec($cmd);

            // If called via AJAX, return JSON so the page does NOT reload
            // and the live log panel can appear immediately
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => true, 'message' => '✓ Full Master Smart Sync started!']);
            }
            return back()->with('success', '✓ Full Master Smart Sync started! Monitor the Live Log below for progress.');
        } catch (\Exception $e) {
            $log->fail($e->getMessage());
            Log::error('Smart sync error', ['error' => $e->getMessage()]);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Smart Sync error: ' . $e->getMessage());
        }
    }

    /**
     * Import all LVS vehicle files from the lvs/ folder.
     * Uses the optimised ImportMasterVehiclesCommand.
     */
    public function importLvsVehicles()
    {
        $log = ImportLog::start('lvs_vehicles', null, Auth::user()?->name ?? 'system');

        try {
            set_time_limit(600);
            Artisan::call('import:master-vehicles', ['path' => $this->vehicleDir]);
            $output = Artisan::output();
            $log->complete();
            return back()->with('success', '✓ LVS Master Vehicles merged successfully from server folder!');
        } catch (\Exception $e) {
            $log->fail($e->getMessage());
            Log::error('LVS import error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error importing LVS data: ' . $e->getMessage());
        }
    }

    /**
     * Sync supplier data from DBF file on server.
     */
    public function importSuppliers()
    {
        $log = ImportLog::start('suppliers', null, Auth::user()?->name ?? 'system');

        try {
            $scriptPath = base_path('scripts/import_suppliers.py');

            $result = \Illuminate\Support\Facades\Process::env([
                'DB_HOST'       => env('DB_HOST', 'mysql'),
                'DB_PORT'       => env('DB_PORT', '3306'),
                'DB_NAME'       => env('DB_DATABASE', 'rts_labour_app'),
                'DB_USER'       => env('DB_USERNAME', 'sail'),
                'DB_PASSWORD'   => env('DB_PASSWORD', 'password'),
                'DBF_PATH'      => $this->supplierDbf,
                'IMPORT_SOURCE' => 'Supplier DBF',
            ])->run(['python3', $scriptPath]);

            $output = $result->output() . $result->errorOutput();

            if (str_contains($output, 'Finished!')) {
                $log->complete();
                return back()->with('success', '✓ Master Suppliers synchronized from server DBF!');
            }

            $log->fail('Unexpected output: ' . substr($output, -200));
            return back()->with('error', 'Supplier sync finished with unexpected result. Output: ' . substr($output, -500));

        } catch (\Exception $e) {
            $log->fail($e->getMessage());
            Log::error('Supplier sync error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Supplier Sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync service history from DBF files on server.
     */
    public function importHistory(Request $request)
    {
        $log = ImportLog::start('service_history', null, Auth::user()?->name ?? 'system');

        try {
            $logPath = storage_path('logs/history_import.log');
            file_put_contents($logPath, "--- Starting Service History Sync ---\n");

            $php     = PHP_BINARY;
            $artisan = base_path('artisan');
            $cmd     = "{$php} {$artisan} import:service-history --log-id={$log->id} >> {$logPath} 2>&1 &";
            exec($cmd);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => true, 'message' => '✓ Service History sync started!']);
            }
            return back()->with('success', '✓ Service History sync started! Watch the Live Log below.');
        } catch (\Exception $e) {
            $log->fail($e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Sync error: ' . $e->getMessage());
        }
    }


    public function importLabourCodes(Request $request)
    {
        $log = ImportLog::start('labour_codes', null, Auth::user()?->name ?? 'system');

        try {
            $logPath = storage_path('logs/history_import.log');
            file_put_contents($logPath, "--- Starting Labour Code Import ---\n");

            // Execute in background using absolute paths
            $php = PHP_BINARY;
            $artisan = base_path('artisan');
            $cmd = "{$php} {$artisan} import:labour-codes --log-id={$log->id} >> {$logPath} 2>&1 &";
            exec($cmd);

            // Return JSON for AJAX calls so the page does NOT reload
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => true, 'message' => '✓ Labour Code import started!']);
            }
            return back()->with('success', '✓ Labour Code import started! Watch the progress in the Live Log below.');
        } catch (\Exception $e) {
            $log->fail($e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Sync error: ' . $e->getMessage());
        }
    }

    public function getImportLog()
    {
        $logPath = storage_path('logs/history_import.log');
        if (!file_exists($logPath)) {
            return response()->json(['log' => 'Waiting for import to start...']);
        }
        
        // Read file and parse carriage returns (\r) generated by Python tqdm
        $content = file_get_contents($logPath);
        $lines = explode("\n", $content);
        
        $processedLines = [];
        foreach ($lines as $line) {
            $parts = explode("\r", $line);
            $finalPart = array_pop($parts); // Keep only the latest update on this line
            if (trim($finalPart) !== '') {
                $processedLines[] = $finalPart;
            }
        }
        
        $lastLines = array_slice($processedLines, -20);
        return response()->json(['log' => implode("\n", $lastLines)]);
    }

    /**
     * Return the latest import log for each type (for the progress panel).
     */
    public function getImportStatus()
    {
        $logs = ImportLog::whereIn('import_type', ['customers', 'dms_customers', 'lvs_vehicles', 'service_history', 'suppliers', 'labour_codes', 'smart_sync'])
            ->orderByDesc('started_at')
            ->get()
            ->groupBy('import_type')
            ->map(function ($group) {
                $log = $group->first();
                
                // Failsafe: if a log has been running for more than 20 minutes, assume it crashed/timed-out
                if ($log->status === 'running' && $log->started_at->diffInMinutes(now()) > 20) {
                    $log->update(['status' => 'failed', 'error_message' => 'Process timed out or was aborted by user refresh.']);
                    $log->status = 'failed';
                }
                
                return [
                    'status'             => $log->status,
                    'total_records'      => $log->total_records,
                    'processed_records'  => $log->processed_records,
                    'failed_records'     => $log->failed_records,
                    'progress_percent'   => $log->progress_percent,
                    'elapsed'            => $log->elapsed,
                    'started_at'         => $log->started_at?->toIso8601String(),
                    'completed_at'       => $log->completed_at?->toIso8601String(),
                    'meta'               => $log->meta,
                    'error_message'      => $log->error_message,
                ];
            });

        return response()->json($logs);
    }
}
