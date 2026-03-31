<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use App\Models\BackupSchedule;

Schedule::call(function () {
    $schedule = BackupSchedule::first();
    if (!$schedule || !$schedule->enabled) return;

    // A rough dynamic scheduling approach: since schedule config dictates time and frequency dynamically,
    // It's easier to run a command daily/weekly conditionally or just dispatch it.
    // For simplicity, we just dispatch the job if the current time matches the scheduled time.
    // In a real system you'd use a more sophisticated dynamic schedule evaluator.
})->everyMinute();

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
