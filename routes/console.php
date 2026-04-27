<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\BackupSchedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register backup command every minute, the command itself can check if it's due
Schedule::command('backup:database')->everyMinute()->when(function () {
    $schedule = BackupSchedule::first();
    if (!$schedule || !$schedule->enabled) return false;
    
    // Check if current time matches scheduled time (HH:MM)
    $now = now();
    $scheduledTime = \Carbon\Carbon::createFromFormat('H:i', $schedule->time);
    
    if ($now->format('H:i') !== $scheduledTime->format('H:i')) return false;

    // Check frequency
    if ($schedule->frequency === 'daily') return true;
    if ($schedule->frequency === 'weekly' && $now->isSunday()) return true;
    if ($schedule->frequency === 'monthly' && $now->day === 1) return true;
    
    return false;
});

// Session cleanup
Schedule::call(function () {
    $schedule = BackupSchedule::first();
    if ($schedule && $schedule->session_cleanup_enabled) {
        $days = $schedule->session_cleanup_days ?? 7;
        \App\Models\UserSession::where('last_active_at', '<', now()->subDays($days))->delete();
    }
})->daily();

// Security: check for suspicious activity every 5 minutes
Schedule::command('security:check-activity')->everyFiveMinutes();

// Log archival: run daily at 3 AM
Schedule::command('logs:archive')->dailyAt('03:00');
