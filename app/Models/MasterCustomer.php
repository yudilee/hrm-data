<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterCustomer extends Model
{
    protected $table = 'master_customers';
    protected $primaryKey = 'magic_cust';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'magic_cust',
        'name',
        'address_1',
        'address_2',
        'address_3',
        'address_4',
        'address_5',
        'full_address',
        'company_name',
        'magic_comp',
        'email',
        'dept',
        'title',
        'telp_1',
        'telp_2',
        'telp_3',
        'telp_4',
        'date_created',
        'source',
    ];

    protected $casts = [
        'date_created' => 'date',
    ];

    /**
     * A customer can own many vehicles.
     */
    public function vehicles()
    {
        return $this->hasMany(MasterVehicle::class, 'customer_magic', 'magic_cust');
    }
}
