<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Exports\ContactExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;

class TestOdooExport extends Command
{
    protected $signature = 'rts:test-export';

    protected $description = 'Test the Odoo export performance directly';

    public function handle()
    {
        $this->info('Starting Odoo Contact Export Test...');
        $start = microtime(true);

        // Map the logic from OdooExportController
        $templatePath = '/home/yudi/dev/rts_code/Master Data Template/1. Master Contact Customer & Vendor.xlsx';

        Excel::store(new ContactExport, 'test_bench.xlsx', 'local');

        $storagePath = storage_path('app/private/test_bench.xlsx');
        if (File::exists($storagePath)) {
            $targetDir = dirname($templatePath);
            if (! File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }
            File::copy($storagePath, $templatePath);
            $this->info('Contacts exported to: '.$templatePath);
        }

        $end = microtime(true);
        $duration = $end - $start;

        $this->info('Execution Time: '.number_format($duration, 2).' seconds');
    }
}
