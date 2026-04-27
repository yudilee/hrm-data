<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class ImportLabourCodesCommand extends Command
{
    protected $signature = 'import:labour-codes {--log-id= : ID of the ImportLog record to update on completion}';
    protected $description = 'Import labour codes from the master Excel file using the Python pipeline.';

    public function handle()
    {
        $this->info('Starting Labour Code Import Pipeline...');
        
        $scriptPath = base_path('scripts/import_data.py');
        $logPath = storage_path('logs/history_import.log');

        if (!file_exists($scriptPath)) {
            $this->error("Python script not found at: $scriptPath");
            return 1;
        }

        // Run the python script and let it output to STDOUT/STDERR.
        // The calling controller will redirect this to the log file.
        $result = Process::env([
            'DB_HOST'     => config('database.connections.mysql.host'),
            'DB_PORT'     => config('database.connections.mysql.port'),
            'DB_USER'     => config('database.connections.mysql.username'),
            'DB_PASSWORD' => config('database.connections.mysql.password'),
            'DB_NAME'     => config('database.connections.mysql.database'),
            'DATA_DIR'    => base_path('Data Operation'),
        ])->timeout(600)->run("python3 $scriptPath");

        if ($result->successful()) {
            $this->info($result->output());
            $this->info('✅ Labour Codes imported successfully!');
            // Mark the ImportLog record as completed
            if ($logId = $this->option('log-id')) {
                optional(\App\Models\ImportLog::find($logId))->complete();
            }
            return 0;
        } else {
            $this->error($result->output());
            $this->error($result->errorOutput());
            $this->error('❌ Labour Code import failed.');
            Log::error('Labour Code Import Failed', ['output' => $result->errorOutput()]);
            if ($logId = $this->option('log-id')) {
                optional(\App\Models\ImportLog::find($logId))->fail('Script returned non-zero exit code');
            }
            return 1;
        }
    }
}
