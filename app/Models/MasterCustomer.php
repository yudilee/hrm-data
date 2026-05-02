<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\CustomerNormalizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterCustomer extends Model
{
    use SoftDeletes;

    protected $table = 'master_customers';
    // Removed specific primaryKey because it's now 'id' (auto-increment)

    protected $fillable = [
        'name',
        'normalized_name',
        'is_company',
        'customer_type',
        'phone_fingerprint',
        'primary_phone_type',
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
        'sources',
        'legacy_mappings',
        'data_quality_score',
        'is_recovered',
        'odoo_id',
        'sync_status',
        'last_synced_at',
    ];

    protected $casts = [
        'date_created' => 'date',
        'last_synced_at' => 'datetime',
        'is_company' => 'boolean',
        'is_recovered' => 'boolean',
        'legacy_mappings' => 'array',
        'sources' => 'array',
    ];

    /**
     * A customer can own many vehicles directly.
     */
    public function vehicles()
    {
        return $this->hasMany(MasterVehicle::class, 'primary_customer_id', 'id');
    }

    /**
     * A customer has contact history with vehicles.
     */
    public function contactHistory()
    {
        return $this->hasMany(VehicleContactHistory::class, 'customer_id', 'id');
    }

    /**
     * A customer has service histories linked directly (as the billing customer).
     */
    public function serviceHistories()
    {
        return $this->hasMany(ServiceHistory::class, 'customer_id', 'id');
    }

    /**
     * All service histories performed on the customer's vehicles.
     */
    public function vehicleServiceHistories()
    {
        return $this->hasManyThrough(
            ServiceHistory::class,
            MasterVehicle::class,
            'primary_customer_id', // Foreign key on MasterVehicle table
            'vehicle_id',         // Foreign key on ServiceHistory table
            'id',                  // Local key on MasterCustomer table
            'id'                   // Local key on MasterVehicle table
        );
    }

    /**
     * Normalizes a name for deduplication.
     */
    public static function normalizeName($name)
    {
        return app(CustomerNormalizer::class)->normalizeName($name);
    }

    public static function canonicalPhone($phone)
    {
        return app(CustomerNormalizer::class)->canonicalPhone($phone);
    }

    public static function detectPhoneType($phone)
    {
        return app(CustomerNormalizer::class)->detectPhoneType($phone);
    }

    /**
     * Compute and write the `sources` JSON array from legacy_mappings.
     *
     * Extracts all unique branch codes from legacy_mappings, sorts them,
     * and saves to the `sources` column. Does NOT change `source` (canonical).
     *
     * Usage:
     *   MasterCustomer::syncSourcesFromMappings($customer);
     *   $customer->save();
     */
    public static function syncSourcesFromMappings(self $customer): void
    {
        $mappings = $customer->legacy_mappings ?? [];

        $branches = collect($mappings)
            ->pluck('branch')
            ->filter()                         // remove nulls
            ->unique()
            ->sort()
            ->values()
            ->all();

        $customer->sources = $branches;
    }

    /**
     * Compute sources array from a raw legacy_mappings array (for bulk inserts).
     * Returns a sorted, unique list of branch codes as a JSON string.
     */
    public static function computeSourcesJson(array $legacyMappings): string
    {
        $branches = collect($legacyMappings)
            ->pluck('branch')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return json_encode($branches);
    }
}
