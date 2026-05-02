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

class ImportLabourCodesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    public function __construct(
        protected int $logId
    ) {}

    public function handle(): void
    {
        $logPath = storage_path('logs/history_import.log');
        file_put_contents($logPath, "--- Starting Labour Code Import ---\n");

        try {
            Artisan::call('import:labour-codes', ['--log-id' => $this->logId]);
        } catch (\Exception $e) {
            Log::error('ImportLabourCodesJob failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
