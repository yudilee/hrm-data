<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ImportLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_start_creates_running_log(): void
    {
        $log = ImportLog::start('customers', 100, 'admin');

        $this->assertDatabaseHas('import_logs', [
            'id' => $log->id,
            'import_type' => 'customers',
            'status' => 'running',
            'total_records' => 100,
            'triggered_by' => 'admin',
        ]);
        $this->assertNotNull($log->started_at);
    }

    public function test_complete_updates_status(): void
    {
        $log = ImportLog::start('lvs_vehicles');
        $log->complete(['files_imported' => 5]);

        $this->assertDatabaseHas('import_logs', [
            'id' => $log->id,
            'status' => 'completed',
        ]);
        $this->assertNotNull($log->fresh()->completed_at);
    }

    public function test_fail_sets_error(): void
    {
        $log = ImportLog::start('suppliers');
        $log->fail('Connection refused');

        $this->assertDatabaseHas('import_logs', [
            'id' => $log->id,
            'status' => 'failed',
            'error_message' => 'Connection refused',
        ]);
    }

    public function test_progress_percent_returns_zero_when_no_total(): void
    {
        $log = ImportLog::start('customers', null);
        $this->assertSame(0, $log->progress_percent);
    }

    public function test_progress_percent_calculates_correctly(): void
    {
        $log = ImportLog::start('customers', 100);
        $log->updateProgress(45);

        $this->assertSame(45, $log->fresh()->progress_percent);
    }
}
