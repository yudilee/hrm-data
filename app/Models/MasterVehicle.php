<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterVehicle extends Model
{
    use SoftDeletes;

    protected $table = 'master_vehicles';
    // Removed specific primaryKey because it's now 'id' (auto-increment)

    protected $fillable = [
        'legacy_magic',
        'registration_no',
        'chassis_no',
        'engine_no',
        'mhl_number',
        'franc',
        'model',
        'variant',
        'description',
        'primary_customer_id',
        'user_id',
        'status',
        'progress_code',
        'reg_date',
        'created_date',
        'last_edited_date',
        'last_service_date',
        'true_franchise',
        'branches_visited',
        'source',
        'legacy_mappings',
        'is_recovered',
        'odoo_id',
        'sync_status',
        'last_synced_at',
    ];

    protected $casts = [
        'reg_date' => 'date',
        'created_date' => 'date',
        'last_edited_date' => 'date',
        'last_service_date' => 'date',
        'last_synced_at' => 'datetime',
        'branches_visited' => 'array',
        'legacy_mappings' => 'array',
        'is_recovered' => 'boolean',
    ];

    /**
     * The primary owner/customer of this vehicle.
     */
    public function primaryCustomer()
    {
        return $this->belongsTo(MasterCustomer::class, 'primary_customer_id', 'id');
    }

    /**
     * Alias for primaryCustomer() to maintain backward compatibility.
     */
    public function customer()
    {
        return $this->primaryCustomer();
    }

    /**
     * The contact history associated with this vehicle.
     */
    public function contactHistory()
    {
        return $this->hasMany(VehicleContactHistory::class, 'vehicle_id', 'id');
    }

    /**
     * The service histories associated with this vehicle.
     */
    public function serviceHistories()
    {
        return $this->hasMany(ServiceHistory::class, 'vehicle_id', 'id');
    }

    /**
     * Fix corrupted years from FoxPro imports (e.g., 9019 -> 2019)
     */
    protected function normalizeYear($value)
    {
        if (! $value) {
            return null;
        }
        $date = Carbon::parse($value);
        if ($date->year > 2050) {
            $date->year(2000 + ($date->year % 100));
        } elseif ($date->year < 1970 && $date->year > 0) {
            $date->year(2000 + ($date->year % 100));
        }

        return $date;
    }

    public function getRegDateAttribute($value)
    {
        return $this->normalizeYear($value);
    }

    public function getLastServiceDateAttribute($value)
    {
        return $this->normalizeYear($value);
    }
}
