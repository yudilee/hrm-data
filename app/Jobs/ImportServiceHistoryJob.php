<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ImportServiceHistoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    public function __construct(
        protected int $logId
    ) {}

    public function handle(): void
    {
        $logPath = storage_path('logs/history_import.log');
        file_put_contents($logPath, "--- Starting Service History Sync ---\n");

        try {
            Artisan::call('import:service-history', ['--log-id' => $this->logId]);
        } catch (\Exception $e) {
            Log::error('ImportServiceHistoryJob failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
