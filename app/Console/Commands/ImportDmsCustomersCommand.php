<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class ImportDmsCustomersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:dms-customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Master Customers from DMS XLS files using Python script';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting DMS Customers Import Process...');
        
        $scriptPath = base_path('scripts/import_dms_customers.py');
        
        if (!file_exists($scriptPath)) {
            $this->error("Python script not found at: {$scriptPath}");
            return Command::FAILURE;
        }

        $this->info("Executing Python script: {$scriptPath}");
        
        $logPath = storage_path('logs/history_import.log');
        file_put_contents($logPath, "--- Start DMS Customer Import " . now() . " ---\n");

        $result = Process::env([
            'DB_HOST'     => env('DB_HOST', 'mysql'),
            'DB_PORT'     => env('DB_PORT', '3306'),
            'DB_DATABASE' => env('DB_DATABASE', 'rts_labour_app'),
            'DB_USERNAME' => env('DB_USERNAME', 'sail'),
            'DB_PASSWORD' => env('DB_PASSWORD', 'password'),
        ])->forever()->run("python3 \"{$scriptPath}\"", function (string $type, string $output) use ($logPath) {
            // Stream output to console
            echo $output;
            // Also append to log file for UI monitor
            file_put_contents($logPath, $output, FILE_APPEND);
        });

        if ($result->successful()) {
            $this->info('DMS Customers Import completed successfully.');
            file_put_contents($logPath, "Process completed.\n", FILE_APPEND);
            return Command::SUCCESS;
        } else {
            $this->error('DMS Customers Import failed.');
            $this->error($result->errorOutput());
            file_put_contents($logPath, "Process failed.\n" . $result->errorOutput(), FILE_APPEND);
            return Command::FAILURE;
        }
    }
}
