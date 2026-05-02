<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'auditable_type', 'auditable_id',
        'old_values', 'new_values', 'description',
        'ip_address', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Record an audit entry.
     *
     * @param  string  $action  e.g. 'created', 'updated', 'deleted', 'exported', 'triggered'
     * @param  mixed  $auditable  Eloquent model or [type, id] array
     * @param  array  $old  Before-state (only relevant fields)
     * @param  array  $new  After-state  (only relevant fields)
     * @param  string|null  $description  Human-readable summary
     */
    public static function record(
        string $action,
        mixed $auditable,
        array $old = [],
        array $new = [],
        ?string $description = null
    ): void {
        [$type, $id] = $auditable instanceof Model
            ? [get_class($auditable), (string) $auditable->getKey()]
            : [(string) ($auditable[0] ?? 'Unknown'), (string) ($auditable[1] ?? '0')];

        static::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $type,
            'auditable_id' => $id,
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => substr(Request::userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
