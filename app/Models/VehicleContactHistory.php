<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleContactHistory extends Model
{
    protected $table = 'vehicle_contact_history';

    protected $fillable = [
        'vehicle_id',
        'customer_id',
        'role',
        'source',
        'observed_at',
        'evidence_type',
        'invoice_ref',
    ];

    protected $casts = [
        'observed_at' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(MasterVehicle::class, 'vehicle_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id', 'id');
    }
}
