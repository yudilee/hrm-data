<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BackupSchedule;
use App\Services\BackupService;
use Illuminate\Support\Facades\Log;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a scheduled database backup based on the saved BackupSchedule settings';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService)
    {
        $schedule = BackupSchedule::first();

        if (!$schedule || !$schedule->enabled) {
            $this->info('Database backups are currently disabled in settings.');
            return 0;
        }

        try {
            $this->info('Starting automated database backup...');
            $backupService->create('Scheduled Automated Backup');
            $this->info('Backup generated successfully.');
            
            if ($schedule->prune_enabled) {
                $this->info('Pruning old backups...');
                $backupService->prune(
                    $schedule->keep_daily ?? 7,
                    $schedule->keep_weekly ?? 4,
                    $schedule->keep_monthly ?? 6
                );
                $this->info('Pruning complete.');
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            Log::error('Scheduled database backup failed: ' . $e->getMessage());
            return 1;
        }
    }
}
