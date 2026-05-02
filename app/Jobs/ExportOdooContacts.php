<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Exports\ContactExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ExportOdooContacts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Cache::put('odoo_export_status', 'processing', 600);

            $fileName = '1. Master Contact Customer & Vendor.xlsx';
            $publicPath = 'exports/'.$fileName;

            // 1. Generate and store in public disk
            Excel::store(new ContactExport, $publicPath, 'public');

            // 2. Secondary: Attempt to copy to the legacy template path
            try {
                $templatePath = '/home/yudi/dev/rts_code/Master Data Template/'.$fileName;
                $storageFile = storage_path('app/public/exports/'.$fileName);

                if (file_exists($storageFile)) {
                    $targetDir = dirname($templatePath);
                    // Silently ensure directory and copy
                    if (! is_dir($targetDir)) {
                        @mkdir($targetDir, 0755, true);
                    }
                    @copy($storageFile, $templatePath);
                }
            } catch (Throwable $e) {
                // Ignore errors on secondary copy
            }

            // Finalize status
            Cache::put('odoo_export_file', '/storage/'.$publicPath, 600);
            Cache::put('odoo_export_finished_at', now()->toDateTimeString(), 600);
            Cache::put('odoo_export_status', 'completed', 600);

        } catch (Throwable $e) {
            Cache::put('odoo_export_status', 'failed', 600);
            Cache::put('odoo_export_error', $e->getMessage(), 600);
        }
    }
}
