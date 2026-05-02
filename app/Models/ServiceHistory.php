<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceHistory extends Model
{
    use SoftDeletes;

    protected $table = 'service_histories';

    protected $fillable = [
        'CJOBN', 'CINVN', 'CNPOL', 'CHASN', 'CENGN',
        'DRECV', 'DINVN', 'CCUST', 'ENAME', 'EADDR',
        'ECITY', 'EPHON', 'ETYPE', 'DSTNK', 'EKMPOS',
        'ALBRS', 'ASPTS', 'ASSPS', 'ASUBS', 'AOTHS1', 'AOTHS2',
        'DISC', 'ATAXS', 'AMTRS', 'PTAX',
        'customer_id', 'vehicle_id', 'source',
        'odoo_id', 'sync_status', 'last_synced_at',
    ];

    protected $casts = [
        'DRECV' => 'date',
        'DINVN' => 'date',
        'DSTNK' => 'date',
        'last_synced_at' => 'datetime',
    ];

    public function labours()
    {
        return $this->hasMany(ServiceHistoryLabour::class, 'service_history_id', 'id');
    }

    public function parts()
    {
        return $this->hasMany(ServiceHistoryPart::class, 'service_history_id', 'id');
    }

    /**
     * Link to MasterVehicle via the new global vehicle_id
     */
    public function vehicle()
    {
        return $this->belongsTo(MasterVehicle::class, 'vehicle_id', 'id');
    }

    /**
     * Link to MasterCustomer via the new global customer_id
     */
    public function customer()
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id', 'id');
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

    public function getDrecvAttribute($value)
    {
        return $this->normalizeYear($value);
    }

    public function getDinvnAttribute($value)
    {
        return $this->normalizeYear($value);
    }
}
