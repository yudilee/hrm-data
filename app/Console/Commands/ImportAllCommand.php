<?php

namespace App\Console\Commands;

use App\Models\ImportLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ImportAllCommand extends Command
{
    protected $signature = 'rts:import-all {--log-id= : ID of the ImportLog record to update on completion}';
    protected $description = 'Run the full smart import sequence: Recover Ghosts -> LVS Vehicles -> Backfill Names -> Merge Duplicates.';

    public function handle()
    {
        $this->info('Starting Master Smart Import Sequence...');

        $steps = [
            [
                'command' => 'rts:recover-ghosts',
                'label'   => '1. Recovering Ghosts from Service History'
            ],
            [
                'command' => 'import:master-vehicles',
                'label'   => '2. Importing LVS Master Vehicle Data'
            ],
            [
                'command' => 'rts:backfill-names',
                'label'   => '3. Backfilling Missing Names from History'
            ],
            [
                'command' => 'rts:merge-duplicates',
                'label'   => '4. Final Duplicate Merging & Consolidation'
            ],
        ];

        foreach ($steps as $step) {
            $this->newLine();
            $this->info(">>> Running Step {$step['label']}...");
            Artisan::call($step['command'], [], $this->getOutput());
        }

        $this->newLine();
        $this->info('✅ Master Smart Import Sequence completed successfully!');

        // Mark the ImportLog record as completed
        if ($logId = $this->option('log-id')) {
            optional(ImportLog::find($logId))->complete();
        }
    }
}
