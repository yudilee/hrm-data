<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ApiAccessLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'token_id', 'token_name', 'user_id',
        'method', 'path', 'query_params',
        'ip_address', 'user_agent',
        'response_status', 'response_time_ms',
        'created_at',
    ];

    protected $casts = [
        'query_params' => 'array',
        'created_at'   => 'datetime',
    ];

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForToken($query, int $tokenId)
    {
        return $query->where('token_id', $tokenId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeFailed($query)
    {
        return $query->where('response_status', '>=', 400);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeLast90Days($query)
    {
        return $query->where('created_at', '>=', now()->subDays(90));
    }

    // ─── Archival & Cleanup ───────────────────────────────────────────────────

    /**
     * Archive rows older than 90 days to CSV.gz and then delete them.
     */
    public static function archiveOld(): array
    {
        $cutoff  = now()->subDays(90);
        $rows    = static::where('created_at', '<', $cutoff)->count();

        if ($rows === 0) {
            return ['archived' => 0, 'deleted' => 0];
        }

        $archiveDir = storage_path('logs/archive');
        if (!is_dir($archiveDir)) {
            mkdir($archiveDir, 0755, true);
        }

        $filename = $archiveDir . '/api_access_' . now()->format('Y-m') . '.csv.gz';
        $gz = gzopen($filename, 'ab9');

        $first = true;
        static::where('created_at', '<', $cutoff)
            ->orderBy('created_at')
            ->chunk(1000, function ($chunk) use ($gz, &$first) {
                foreach ($chunk as $row) {
                    if ($first) {
                        gzwrite($gz, implode(',', array_keys($row->toArray())) . "\n");
                        $first = false;
                    }
                    gzwrite($gz, implode(',', array_map(
                        fn($v) => is_array($v) ? json_encode($v) : (string) $v,
                        $row->toArray()
                    )) . "\n");
                }
            });

        gzclose($gz);

        $deleted = static::where('created_at', '<', $cutoff)->delete();

        return ['archived' => $rows, 'deleted' => $deleted];
    }
}
