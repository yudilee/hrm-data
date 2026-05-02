<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ApiAccessLog;
use App\Models\AppNotification;
use App\Models\LoginAttempt;
use App\Models\TokenRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckSuspiciousActivity extends Command
{
    protected $signature = 'security:check-activity';

    protected $description = 'Check for suspicious login or API activity and notify admins';

    public function handle(): int
    {
        $this->checkBruteForce();
        $this->checkExcessiveApiFailures();
        $this->checkPendingTokenRequests();

        return Command::SUCCESS;
    }

    private function checkBruteForce(): void
    {
        // IPs with 20+ failed logins in the last 15 minutes
        $suspicious = LoginAttempt::selectRaw('ip_address, COUNT(*) as attempts')
            ->where('success', false)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->groupBy('ip_address')
            ->having('attempts', '>=', 20)
            ->get();

        foreach ($suspicious as $row) {
            $msg = "Brute-force detected from IP {$row->ip_address}: {$row->attempts} failed logins in 15 min.";
            Log::channel('security')->critical($msg);
            Log::critical($msg, ['ip' => $row->ip_address, 'attempts' => $row->attempts]);

            AppNotification::notifyAdmins(
                'brute_force',
                '🚨 Brute-Force Attack Detected',
                $msg,
                ['ip' => $row->ip_address, 'attempts' => $row->attempts]
            );
        }
    }

    private function checkExcessiveApiFailures(): void
    {
        // IPs with 50+ failed API requests (4xx/5xx) in the last 10 minutes
        $suspicious = ApiAccessLog::selectRaw('ip_address, COUNT(*) as attempts')
            ->where('response_status', '>=', 400)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->groupBy('ip_address')
            ->having('attempts', '>=', 50)
            ->get();

        foreach ($suspicious as $row) {
            $msg = "Excessive API failures from IP {$row->ip_address}: {$row->attempts} errors in 10 min.";
            Log::channel('security')->warning($msg);
            Log::warning($msg, ['ip' => $row->ip_address, 'attempts' => $row->attempts]);

            // Deduplicate: only notify once per hour per IP
            $alreadyNotified = AppNotification::where('type', 'api_abuse')
                ->where('created_at', '>=', now()->subHour())
                ->where('body', 'like', "%{$row->ip_address}%")
                ->exists();

            if (! $alreadyNotified) {
                AppNotification::notifyAdmins(
                    'api_abuse',
                    '⚠️ Excessive API Errors',
                    $msg,
                    ['ip' => $row->ip_address, 'attempts' => $row->attempts]
                );
            }
        }
    }

    private function checkPendingTokenRequests(): void
    {
        // If there are unreviewed token requests > 2 days old, remind admins
        $old = TokenRequest::where('status', 'pending')
            ->where('created_at', '<', now()->subDays(2))
            ->count();

        if ($old > 0) {
            $alreadyNotified = AppNotification::where('type', 'pending_token_requests')
                ->where('created_at', '>=', now()->subDay())
                ->exists();

            if (! $alreadyNotified) {
                AppNotification::notifyAdmins(
                    'pending_token_requests',
                    '📬 Pending API Token Requests',
                    "{$old} token request(s) have been waiting for review for more than 2 days.",
                    ['count' => $old]
                );
            }
        }
    }
}
