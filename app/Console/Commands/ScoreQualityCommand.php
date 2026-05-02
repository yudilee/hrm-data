<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ScoreQualityCommand extends Command
{
    protected $signature = 'rts:score-quality';

    protected $description = 'Recalculate data quality score for all customers using a single bulk SQL UPDATE';

    public function handle()
    {
        $this->info('Starting Data Quality Scoring (bulk SQL mode)...');
        $startTime = microtime(true);

        // Single SQL expression instead of loading all records into PHP
        $affected = DB::update("
            UPDATE master_customers
            SET data_quality_score = LEAST(100,
                (CASE WHEN name IS NOT NULL AND name != '' THEN 20 ELSE 0 END) +
                (CASE WHEN telp_1 IS NOT NULL AND telp_1 != ''
                      OR  telp_2 IS NOT NULL AND telp_2 != '' THEN 20 ELSE 0 END) +
                (CASE WHEN email IS NOT NULL AND email != '' THEN 15 ELSE 0 END) +
                (CASE WHEN address_1 IS NOT NULL AND address_1 != '' THEN 15 ELSE 0 END) +
                (CASE WHEN company_name IS NOT NULL AND company_name != '' THEN 10 ELSE 0 END) +
                (CASE WHEN dept IS NOT NULL AND dept != '' THEN 10 ELSE 0 END) +
                (CASE WHEN title IS NOT NULL AND title != '' THEN 10 ELSE 0 END)
            )
        ");

        $elapsed = round(microtime(true) - $startTime, 2);
        $this->info("✅ Updated quality scores for {$affected} customers in {$elapsed}s.");

        return Command::SUCCESS;
    }
}
