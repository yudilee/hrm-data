<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email', 'ip_address', 'user_agent',
        'success', 'failure_reason', 'created_at',
    ];

    protected $casts = [
        'success'    => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Record a login attempt.
     */
    public static function record(
        string $email,
        bool   $success,
        string $ip,
        string $ua,
        ?string $reason = null
    ): void {
        static::create([
            'email'          => strtolower(trim($email)),
            'ip_address'     => $ip,
            'user_agent'     => substr($ua, 0, 500),
            'success'        => $success,
            'failure_reason' => $reason,
            'created_at'     => now(),
        ]);
    }

    /**
     * Count recent failures from a given IP.
     */
    public static function recentFailures(string $ip, int $minutes = 15): int
    {
        return static::where('ip_address', $ip)
            ->where('success', false)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }
}
