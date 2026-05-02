<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterSupplier extends Model
{
    use SoftDeletes;

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
