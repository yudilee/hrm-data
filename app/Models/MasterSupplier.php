<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterSupplier extends Model
{
    protected $fillable = [
        'code',
        'name',
        'address_1',
        'address_2',
        'city',
        'postal_code',
        'phone',
        'fax',
        'contact_person',
        'email',
        'bank_name',
        'bank_account_no',
        'bank_account_name',
        'category',
        'source',
        'odoo_id',
        'sync_status',
        'last_synced_at',
    ];
}
