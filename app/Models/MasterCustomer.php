<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterCustomer extends Model
{
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
        'sources'         => 'array',
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
        if (!$name) return '';
        
        $n = strtoupper(trim($name));
        
        // Remove common titles and salutations (Indonesian and English)
        // Includes: MR, MRS, MS, H (Haji), HJ (Hajjah), DR (Doctor), DRS (Doktorandus), IR (Insinyur), 
        // PROF, KOL (Kolonel), MAY (Mayor), CAPT (Captain)
        $titles = ['MR', 'MRS', 'MS', 'H', 'HJ', 'DR', 'DRS', 'IR', 'PROF', 'KOL', 'MAY', 'CAPT', 'IBU', 'BPK', 'BAPAK'];
        $pattern = '/^(' . implode('|', $titles) . ')\.?\s+/i';
        $n = preg_replace($pattern, '', $n);
        
        // Also strip common legal entity prefixes if they are at the start
        $entities = ['PT', 'CV', 'UD', 'PO'];
        $entityPattern = '/^(' . implode('|', $entities) . ')\.?\s+/i';
        $n = preg_replace($entityPattern, '', $n);
        
        return preg_replace('/[.,\s]/', '', $n);
    }

    /**
     * Canonicalizes an Indonesian phone number.
     */
    public static function canonicalPhone($phone)
    {
        if (!$phone) return null;
        
        $p = preg_replace('/[^0-9+]/', '', $phone);
        
        if (str_starts_with($p, '+62')) {
            $p = '0' . substr($p, 3);
        } elseif (str_starts_with($p, '62') && strlen($p) > 9) {
            $p = '0' . substr($p, 2);
        }
        
        return $p !== '' ? $p : null;
    }

    /**
     * Detects if phone is mobile, landline, or unknown.
     */
    public static function detectPhoneType($phone)
    {
        $p = self::canonicalPhone($phone);
        if (!$p) return 'unknown';

        if (str_starts_with($p, '08')) {
            return 'mobile';
        }

        // Common area codes (simplified check)
        if (preg_match('/^0[2-9][0-9]/', $p)) {
            return 'landline';
        }

        return 'unknown';
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
