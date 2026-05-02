<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    public $timestamps = false;

    protected $table = 'import_logs';

    protected $fillable = [
        'import_type',
        'status',
        'total_records',
        'processed_records',
        'failed_records',
        'meta',
        'error_message',
        'triggered_by',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Create a new running import log entry and return it.
     */
    public static function start(string $importType, ?int $total = null, ?string $triggeredBy = 'system'): static
    {
        return static::create([
            'import_type' => $importType,
            'status' => 'running',
            'total_records' => $total,
            'triggered_by' => $triggeredBy,
            'started_at' => now(),
        ]);
    }

    /**
     * Mark as completed with final stats.
     */
    public function complete(array $meta = []): void
    {
        $this->update([
            'status' => 'completed',
            'meta' => $meta,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark as failed with an error message.
     */
    public function fail(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }

    /**
     * Update the processed count (called periodically during import).
     */
    public function updateProgress(int $processed, int $failed = 0): void
    {
        $this->update([
            'processed_records' => $processed,
            'failed_records' => $failed,
        ]);
    }

    /**
     * Get elapsed seconds since started.
     */
    public function getElapsedAttribute(): float
    {
        $end = $this->completed_at ?? now();

        return $this->started_at ? $end->diffInSeconds($this->started_at) : 0;
    }

    /**
     * Get percentage progress (0–100).
     */
    public function getProgressPercentAttribute(): int
    {
        if (! $this->total_records || $this->total_records === 0) {
            return 0;
        }

        return min(100, (int) (($this->processed_records / $this->total_records) * 100));
    }
}
