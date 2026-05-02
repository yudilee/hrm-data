<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ImportLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class ImportService
{
    protected string $customerDir;

    protected string $vehicleDir;

    protected string $supplierDbf;

    public function __construct()
    {
        $this->customerDir = config('data-sources.customer_dir');
        $this->vehicleDir = config('data-sources.vehicle_dir');
        $this->supplierDbf = config('data-sources.supplier_dbf');
    }

    public function scanCustomerFiles(): array
    {
        return collect(File::glob($this->customerDir.'/*.xls*'))
            ->map(fn ($p) => ['path' => $p, 'name' => basename($p)])
            ->values()
            ->all();
    }

    public function scanVehicleFiles(): array
    {
        return collect(File::glob($this->vehicleDir.'/*.xls*'))
            ->map(fn ($p) => ['path' => $p, 'name' => basename($p)])
            ->values()
            ->all();
    }

    public function supplierFileExists(): bool
    {
        return File::exists($this->supplierDbf);
    }

    public function getLastLogs(): array
    {
        return ImportLog::whereIn('import_type', ['customers', 'lvs_vehicles', 'service_history', 'suppliers', 'smart_sync'])
            ->orderByDesc('started_at')
            ->get()
            ->groupBy('import_type')
            ->map(fn ($g) => $g->first())
            ->all();
    }

    public function getCustomerDir(): string
    {
        return $this->customerDir;
    }

    public function getVehicleDir(): string
    {
        return $this->vehicleDir;
    }

    public function getSupplierDbf(): string
    {
        return $this->supplierDbf;
    }

    public function getImportStatus(): array
    {
        $logs = ImportLog::whereIn('import_type', [
            'customers', 'dms_customers', 'lvs_vehicles', 'service_history', 'suppliers', 'labour_codes', 'smart_sync',
        ])
            ->orderByDesc('started_at')
            ->get()
            ->groupBy('import_type')
            ->map(function ($group) {
                $log = $group->first();

                if ($log->status === 'running' && $log->started_at->diffInMinutes(now()) > 20) {
                    $log->update(['status' => 'failed', 'error_message' => 'Process timed out or was aborted by user refresh.']);
                    $log->status = 'failed';
                }

                return [
                    'status' => $log->status,
                    'total_records' => $log->total_records,
                    'processed_records' => $log->processed_records,
                    'failed_records' => $log->failed_records,
                    'progress_percent' => $log->progress_percent,
                    'elapsed' => $log->elapsed,
                    'started_at' => $log->started_at?->toIso8601String(),
                    'completed_at' => $log->completed_at?->toIso8601String(),
                    'meta' => $log->meta,
                    'error_message' => $log->error_message,
                ];
            });

        return $logs->all();
    }

    public function readImportLog(): array
    {
        $logPath = storage_path('logs/history_import.log');
        if (! file_exists($logPath)) {
            return ['log' => 'Waiting for import to start...'];
        }

        $content = file_get_contents($logPath);
        $lines = explode("\n", $content);

        $processedLines = [];
        foreach ($lines as $line) {
            $parts = explode("\r", $line);
            $finalPart = array_pop($parts);
            if (trim($finalPart) !== '') {
                $processedLines[] = $finalPart;
            }
        }

        $lastLines = array_slice($processedLines, -20);

        return ['log' => implode("\n", $lastLines)];
    }

    public function getSourceCodeFromFilename(string $filename): string
    {
        preg_match('/(?:APR|MAR|FEB|JAN|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)\d+\s+(.+?)\(H04\)/i', basename($filename), $m);
        $source = isset($m[1]) ? 'HRM'.strtoupper(trim($m[1])) : 'IMPORT';
        $source = str_replace(' ', '', $source);

        $sourceMap = [
            'HRMSBYPC' => 'HRMSBY PC',
            'HRMSBYCV' => 'HRMSBY CV',
            'HRMJKTCV' => 'HRMJKT CV',
            'HRMDPSPC' => 'HRMDPS PC',
            'HRMDPSCV' => 'HRMDPS CV',
            'HRMSMGPC' => 'HRMSMG PC',
            'HRMSMGCV' => 'HRMSMG CV',
        ];

        return $sourceMap[$source] ?? $source;
    }

    public function runSupplierSync(): array
    {
        $scriptPath = base_path('scripts/import_suppliers.py');

        $result = Process::env([
            'DB_HOST' => env('DB_HOST', 'mysql'),
            'DB_PORT' => env('DB_PORT', '3306'),
            'DB_NAME' => env('DB_DATABASE', 'master_data'),
            'DB_USER' => env('DB_USERNAME', 'sail'),
            'DB_PASSWORD' => env('DB_PASSWORD', 'password'),
            'DBF_PATH' => $this->supplierDbf,
            'IMPORT_SOURCE' => 'Supplier DBF',
        ])->run(['python3', $scriptPath]);

        return [
            'output' => $result->output().$result->errorOutput(),
            'success' => str_contains($result->output().$result->errorOutput(), 'Finished!'),
        ];
    }

    public function startLog(string $type, ?int $total = null): ImportLog
    {
        return ImportLog::start($type, $total, Auth::user()?->name ?? 'system');
    }
}
