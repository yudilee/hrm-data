<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterVehicle extends Model
{
    protected $table = 'master_vehicles';
    protected $primaryKey = 'magic';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'magic',
        'registration_no',
        'franc',
        'model',
        'variant',
        'description',
        'chassis_no',
        'mhl_number',
        'engine_no',
        'user_id',
        'status',
        'progress_code',
        'customer_magic',
        'reg_date',
        'created_date',
        'last_edited_date',
        'last_service_date',
    ];

    protected $casts = [
        'reg_date'         => 'date',
        'created_date'     => 'date',
        'last_edited_date' => 'date',
        'last_service_date'=> 'date',
    ];

    /**
     * The customer who owns this vehicle.
     */
    public function customer()
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_magic', 'magic_cust');
    }
}
