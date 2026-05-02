<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    /**
     * Log an audit event for this model.
     *
     * @param  string  $action  created|updated|deleted|restored|exported|triggered
     * @param  array  $old  Previous state (optional)
     * @param  array  $new  New state (optional)
     * @param  string|null  $description  Human-readable summary (optional)
     */
    public function audit(string $action, array $old = [], array $new = [], ?string $description = null): void
    {
        AuditLog::record($action, $this, $old, $new, $description);
    }
}
