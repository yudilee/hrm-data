<?php

namespace App\Console\Commands;

use App\Models\ApiAccessLog;
use App\Models\LoginAttempt;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ArchiveOldLogs extends Command
{
    protected $signature = 'logs:archive {--dry-run : Show what would be archived without deleting}';
    protected $description = 'Archive API access logs older than 90 days and prune old login attempts';

    public function handle(): int
    {
        $dry = $this->option('dry-run');

        $this->info($dry ? '🔍 Dry-run mode — no data will be deleted.' : '🗄️ Archiving old logs...');

        // ── API Access Logs (90-day retention) ────────────────────────────────
        $count = ApiAccessLog::where('created_at', '<', now()->subDays(90))->count();
        $this->info("Found {$count} API access log rows older than 90 days.");

        if ($count > 0 && !$dry) {
            $result = ApiAccessLog::archiveOld();
            $this->info("  ✓ Archived {$result['archived']} rows → storage/logs/archive/");
            $this->info("  ✓ Deleted {$result['deleted']} rows from DB.");
            Log::channel('security')->info('API access logs archived', $result);
        }

        // ── Login Attempts (365-day retention) ─────────────────────────────────
        $loginCount = LoginAttempt::where('created_at', '<', now()->subDays(365))->count();
        $this->info("Found {$loginCount} login attempt rows older than 365 days.");

        if ($loginCount > 0 && !$dry) {
            LoginAttempt::where('created_at', '<', now()->subDays(365))->delete();
            $this->info("  ✓ Deleted {$loginCount} old login attempt rows.");
        }

        $this->info($dry ? 'Dry-run complete.' : '✅ Log archival complete.');
        return Command::SUCCESS;
    }
}
