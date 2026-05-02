<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ImportLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class ImportServiceHistoryCommand extends Command
{
    protected $signature = 'import:service-history {--log-id= : ImportLog ID to mark complete}';

    protected $description = 'Import vehicle service history from FoxPro DBF files across all branch subfolders';

    public function handle()
    {
        $this->info('Starting multi-branch vehicle history import...');

        try {
            $scriptPath = base_path('scripts/import_dbf_history.py');

            if (! file_exists($scriptPath)) {
                $this->error("Python script not found at: $scriptPath");

                return 1;
            }

            $this->info("Executing Python script: $scriptPath");
            $logPath = storage_path('logs/history_import.log');
            file_put_contents($logPath, '--- Start Import '.now()." ---\n");

            $result = Process::env([
                'DB_HOST' => env('DB_HOST', 'mysql'),
                'DB_PORT' => env('DB_PORT', '3306'),
                'DB_DATABASE' => env('DB_DATABASE', 'rts_labour_app'),
                'DB_USERNAME' => env('DB_USERNAME', 'sail'),
                'DB_PASSWORD' => env('DB_PASSWORD', 'password'),
            ])->timeout(1200)->run(['python3', $scriptPath], function (string $type, string $output) use ($logPath) {
                file_put_contents($logPath, $output, FILE_APPEND);
                $this->output->write($output);
            });

            $output = $result->output().$result->errorOutput();

            if (str_contains($output, 'Import completely successfully')) {
                $msg = "\nPython import finished. Running data linking...\n";
                $this->info($msg);
                file_put_contents($logPath, $msg, FILE_APPEND);

                Artisan::call('rts:link-history');
                $linkOutput = Artisan::output();

                file_put_contents($logPath, $linkOutput."\n--- Service History Sync Complete ---\n", FILE_APPEND);

                $this->info('Service History synchronized and Master Data healed successfully!');
                if ($logId = $this->option('log-id')) {
                    optional(ImportLog::find($logId))->complete();
                }

                return 0;
            }

            $this->error('Sync finished with unexpected result. Check output above.');

            return 1;

        } catch (\Exception $e) {
            $this->error('Sync error: '.$e->getMessage());
            Log::error('Service history sync command error', ['error' => $e->getMessage()]);
            if ($logId = $this->option('log-id')) {
                optional(ImportLog::find($logId))->fail($e->getMessage());
            }

            return 1;
        }
    }
}
