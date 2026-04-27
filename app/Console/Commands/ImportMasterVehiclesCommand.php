<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MasterVehicle;
use App\Models\MasterCustomer;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class ImportMasterVehiclesCommand extends Command
{
    protected $signature = 'import:master-vehicles {path=/home/yudi/dev/rts_code/lvs}';
    protected $description = 'Import and merge master vehicle data from LVS files, mapping to global customer IDs';

    /** @var array Pre-built lookup map: "branch|magic" => customer_id */
    private array $customerLookupMap = [];

    /** @var array Ghost customers to create, keyed by "branch|magic" to avoid duplicates */
    private array $pendingGhostCustomers = [];

    /** @var array Statistics for end-of-run summary */
    private array $stats = [
        'vehicles_upserted'    => 0,
        'customers_resolved'   => 0,
        'ghost_customers'      => 0,
        'duplicate_chassis'    => 0,
        'skipped_empty'        => 0,
    ];

    public function handle()
    {
        $path = $this->argument('path');

        if (!File::isDirectory($path)) {
            $this->error("Directory not found: $path");
            return 1;
        }

        $files = File::glob("$path/*.xls");
        $this->info("Found " . count($files) . " XLS files.");

        // ── PHASE 1: Read all XLS files into memory ──────────────────────────
        $this->info('Phase 1/4: Reading XLS files...');
        $allData = $this->readAllFiles($files);
        $this->info("Found " . count($allData) . " unique chassis numbers.");

        // ── PHASE 2: Build customer lookup map ────────────────────────────────
        $this->info('Phase 2/4: Building customer lookup map from database...');
        $this->buildCustomerLookupMap();
        $this->info("Lookup map built with " . count($this->customerLookupMap) . " (branch, magic) entries.");

        // ── PHASE 3: Prepare vehicle rows + collect ghost customers ───────────
        $this->info('Phase 3/4: Preparing vehicle data and resolving customer links...');
        $vehicleRows = $this->prepareVehicleRows($allData);

        // Create ghost customers in a single batch before vehicle upsert
        if (!empty($this->pendingGhostCustomers)) {
            $this->info("Creating " . count($this->pendingGhostCustomers) . " ghost customers...");
            $this->createGhostCustomers();
            // Rebuild the map to include newly created ghosts
            $this->buildCustomerLookupMap();
            // Re-resolve customer_ids for vehicles that had ghost customers
            foreach ($vehicleRows as &$row) {
                if ($row['primary_customer_id'] === null && isset($row['_lookup_key'])) {
                    $row['primary_customer_id'] = $this->customerLookupMap[$row['_lookup_key']] ?? null;
                }
            }
            unset($row);
        }

        // Strip internal keys before upsert
        foreach ($vehicleRows as &$row) {
            unset($row['_lookup_key']);
        }
        unset($row);

        // ── PHASE 4: Batch upsert vehicles ────────────────────────────────────
        $this->info('Phase 4/4: Upserting vehicles in batches of 500...');
        $now = now()->toDateTimeString();
        $chunks = array_chunk($vehicleRows, 500);
        $bar    = $this->output->createProgressBar(count($chunks));
        $bar->start();

        foreach ($chunks as $chunk) {
            // Add timestamps — upsert won't update created_at
            foreach ($chunk as &$r) {
                $r['created_at'] = $now;
                $r['updated_at'] = $now;
            }
            unset($r);

            MasterVehicle::upsert(
                $chunk,
                ['chassis_no'],  // unique key for conflict detection
                [                // columns to update on conflict
                    'legacy_magic', 'registration_no', 'franc', 'model', 'variant',
                    'description', 'mhl_number', 'engine_no', 'user_id', 'status',
                    'progress_code', 'primary_customer_id', 'reg_date', 'created_date',
                    'last_edited_date', 'last_service_date', 'true_franchise',
                    'branches_visited', 'source', 'legacy_mappings', 'is_recovered',
                    'updated_at',
                ]
            );
            $this->stats['vehicles_upserted'] += count($chunk);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // ── Post-import integrity check ───────────────────────────────────────
        $this->info('Running post-import integrity check...');
        $this->runIntegrityCheck();

        // ── Summary ───────────────────────────────────────────────────────────
        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Vehicles Upserted',         $this->stats['vehicles_upserted']],
                ['Customer Links Resolved',    $this->stats['customers_resolved']],
                ['Ghost Customers Created',    $this->stats['ghost_customers']],
                ['Duplicate Chassis (merged)', $this->stats['duplicate_chassis']],
                ['Rows Skipped (no chassis)',  $this->stats['skipped_empty']],
            ]
        );

        $this->info("Import completed successfully.");
        return 0;
    }

    /**
     * Read all XLS files and return data keyed by chassis_no.
     * Multiple records for the same chassis are sorted by last_service_date descending.
     */
    private function readAllFiles(array $files): array
    {
        $allData = [];

        foreach ($files as $file) {
            $filename = basename($file);
            $this->line("  Reading $filename...");

            // Robust extraction: find branch token before (lvs) regardless of date string
            // Handles: 'LVS per 17 apr 26 SBY CV(lvs).xls', 'LVS per 18 apr 26 DPS PC(lvs).xls', etc.
            preg_match('/([A-Z]{2,4}\s+(?:PC|CV))\s*\(lvs\)/i', $filename, $m);
            $branchToken   = isset($m[1]) ? strtoupper(trim($m[1])) : 'Unknown';
            $branchCode    = 'HRM' . $branchToken;  // e.g. 'HRMSBY CV'

            $rows    = Excel::toArray([], $file)[0];
            $headers = array_shift($rows);
            $headers = array_map(fn($h) => trim((string) $h), $headers);

            foreach ($rows as $row) {
                $data    = array_combine($headers, $row);
                $chassis = trim((string) ($data['Chassis No'] ?? ''));

                if (empty($chassis)) {
                    $this->stats['skipped_empty']++;
                    continue;
                }

                $data['_branch']      = $branchCode;
                $data['_source_file'] = $filename;

                if (isset($allData[$chassis])) {
                    $this->stats['duplicate_chassis']++;
                }
                $allData[$chassis][] = $data;
            }
        }

        // Sort each chassis group by last service date desc — pick the most recent as primary
        foreach ($allData as &$records) {
            usort($records, function ($a, $b) {
                $da = $this->parseDate($a['Last Service Date'] ?? null);
                $db = $this->parseDate($b['Last Service Date'] ?? null);
                if (!$da) return 1;
                if (!$db) return -1;
                return $db->timestamp <=> $da->timestamp;
            });
        }
        unset($records);

        return $allData;
    }

    /**
     * Build an in-memory map of "branch|magic" → customer_id.
     * This avoids N × whereJsonContains() calls on the database.
     */
    private function buildCustomerLookupMap(): void
    {
        $this->customerLookupMap = [];

        // Stream through all customers with non-null legacy_mappings
        MasterCustomer::whereNotNull('legacy_mappings')
            ->select('id', 'legacy_mappings')
            ->cursor()
            ->each(function ($customer) {
                $mappings = is_array($customer->legacy_mappings)
                    ? $customer->legacy_mappings
                    : json_decode($customer->legacy_mappings, true) ?? [];

                foreach ($mappings as $m) {
                    if (isset($m['branch'], $m['magic'])) {
                        $key = $m['branch'] . '|' . $m['magic'];
                        $this->customerLookupMap[$key] = $customer->id;
                    }
                }
            });
    }

    /**
     * Prepare vehicle row arrays and collect ghost customers for unresolved references.
     */
    private function prepareVehicleRows(array $allData): array
    {
        $rows = [];

        foreach ($allData as $chassis => $records) {
            $base           = $records[0];
            $branchesVisited = collect($records)->map(fn($r) => $r['_branch'])->unique()->values()->toArray();
            $trueFranchise  = $this->calculateTrueFranchise($base['Model'] ?? '');

            $customerMagic     = (int) ($base['Customer Magic'] ?? 0);
            $primaryCustomerId = null;
            $lookupKey         = null;

            if ($customerMagic > 0) {
                $lookupKey         = $base['_branch'] . '|' . $customerMagic;
                $primaryCustomerId = $this->customerLookupMap[$lookupKey] ?? null;

                if ($primaryCustomerId !== null) {
                    $this->stats['customers_resolved']++;
                    
                    // NEW: If existing customer has no name, backfill it from LVS data
                    $existingName = DB::table('master_customers')->where('id', $primaryCustomerId)->value('name');
                    if (empty($existingName)) {
                        $newName = $this->cleanStr($base['Surname'] ?? null);
                        if ($newName) {
                            DB::table('master_customers')->where('id', $primaryCustomerId)->update([
                                'name' => $newName,
                                'normalized_name' => MasterCustomer::normalizeName($newName)
                            ]);
                        }
                    }
                } else {
                    // Schedule a ghost customer to be created
                    if (!isset($this->pendingGhostCustomers[$lookupKey])) {
                        $this->pendingGhostCustomers[$lookupKey] = [
                            'branch' => $base['_branch'],
                            'magic'  => $customerMagic,
                            'row'    => $base,
                        ];
                    }
                }
            }

            $legacyMagic   = (int) ($base['Magic'] ?? 0);
            $legacyMappings = [];
            if ($legacyMagic > 0) {
                $legacyMappings[] = ['branch' => $base['_branch'], 'magic' => $legacyMagic];
            }

            $rows[] = [
                'chassis_no'          => $chassis,
                'legacy_magic'        => $legacyMagic ?: null,
                'registration_no'     => $this->cleanStr($base['Registration No'] ?? null),
                'franc'               => $this->cleanStr($base['Franc'] ?? null),
                'model'               => $this->cleanStr($base['Model'] ?? null),
                'variant'             => $this->cleanStr($base['Variant'] ?? null),
                'description'         => $this->cleanStr($base['Description'] ?? null),
                'mhl_number'          => $this->cleanStr($base['MHL Number'] ?? null),
                'engine_no'           => $this->cleanStr($base['Engine No'] ?? null),
                'user_id'             => $this->cleanStr($base['User ID'] ?? null),
                'status'              => $this->cleanStr($base['Status'] ?? null),
                'progress_code'       => isset($base['Progress Code']) ? (int) $base['Progress Code'] : null,
                'primary_customer_id' => $primaryCustomerId,
                'reg_date'            => $this->parseDate($base['Reg. Date'] ?? null)?->toDateString(),
                'created_date'        => $this->parseDate($base['Ceated Date'] ?? null)?->toDateString(),
                'last_edited_date'    => $this->parseDate($base['Last Edited Date'] ?? null)?->toDateString(),
                'last_service_date'   => $this->parseDate($base['Last Service Date'] ?? null)?->toDateString(),
                'true_franchise'      => $trueFranchise,
                'branches_visited'    => json_encode($branchesVisited),
                'source'              => $base['_branch'],   // e.g. 'HRMSBY CV' — NOT the raw filename
                'legacy_mappings'     => json_encode($legacyMappings),
                'is_recovered'        => false,
                '_lookup_key'         => $lookupKey, // internal — stripped before upsert
            ];
        }

        return $rows;
    }

    /**
     * Create all pending ghost customers in a batch.
     */
    private function createGhostCustomers(): void
    {
        $now = now()->toDateTimeString();
        $batch = [];

        foreach ($this->pendingGhostCustomers as $key => $ghost) {
            $row    = $ghost['row'];
            $branch = $ghost['branch'];
            $magic  = $ghost['magic'];

            $a1          = $this->cleanStr($row['Address1'] ?? null);
            $a2          = $this->cleanStr($row['Address2'] ?? null);
            $a3          = $this->cleanStr($row['Address3'] ?? null);
            $a4          = $this->cleanStr($row['Address4'] ?? null);
            $a5          = $this->cleanStr($row['Address5'] ?? null);
            $fullAddress = collect([$a1, $a2, $a3, $a4, $a5])->filter()->implode(', ');

            $name        = $this->cleanStr($row['Surname'] ?? 'Unknown LVS Customer');
            $normName    = MasterCustomer::normalizeName($name);
            $phone1      = $this->cleanStr($row['Phone1'] ?? null);
            $fingerprint = MasterCustomer::canonicalPhone($phone1);

            $legacyMappingsArr = [['branch' => $branch, 'magic' => $magic]];

            $batch[] = [
                'name'               => $name,
                'normalized_name'    => $normName,
                'phone_fingerprint'  => $fingerprint,
                'primary_phone_type' => MasterCustomer::detectPhoneType($phone1),
                'address_1'          => $a1,
                'address_2'          => $a2,
                'address_3'          => $a3,
                'address_4'          => $a4,
                'address_5'          => $a5,
                'full_address'       => $fullAddress ?: null,
                'telp_1'             => $phone1,
                'telp_2'             => $this->cleanStr($row['Phone2'] ?? null),
                'telp_3'             => $this->cleanStr($row['Phone3'] ?? null),
                'telp_4'             => $this->cleanStr($row['Phone4'] ?? null),
                'source'             => $branch,
                'sources'            => MasterCustomer::computeSourcesJson($legacyMappingsArr),
                'is_recovered'       => true,
                'legacy_mappings'    => json_encode($legacyMappingsArr),
                'data_quality_score' => 20,
                'created_at'         => $now,
                'updated_at'         => $now,
            ];

            $this->stats['ghost_customers']++;
        }

        // Insert in chunks of 200
        foreach (array_chunk($batch, 200) as $chunk) {
            DB::table('master_customers')->insertOrIgnore($chunk);
        }
    }

    /**
     * Post-import integrity check: report any dangling FKs.
     */
    private function runIntegrityCheck(): void
    {
        $danglingCount = DB::table('master_vehicles as mv')
            ->leftJoin('master_customers as mc', 'mv.primary_customer_id', '=', 'mc.id')
            ->whereNotNull('mv.primary_customer_id')
            ->whereNull('mc.id')
            ->count();

        if ($danglingCount > 0) {
            $this->warn("⚠️  Integrity: {$danglingCount} vehicles have primary_customer_id pointing to non-existent customers.");
        } else {
            $this->info("✅ Integrity check passed: All customer FK references are valid.");
        }

        $noCustomer = DB::table('master_vehicles')->whereNull('primary_customer_id')->count();
        $this->info("ℹ️  {$noCustomer} vehicles have no associated customer (unresolved LVS Customer Magic).");
    }

    private function calculateTrueFranchise(string $model): string
    {
        $cvKeywords = ['Bus', 'Truck', 'Axor', 'Actros', 'Atego', 'O500', 'LO914', 'OF1623', 'FUSO', 'EVOBUS'];
        $pcKeywords = ['Class', 'GLA', 'GLC', 'GLE', 'GLK', 'SLK', 'SLC', 'SLS', 'AMG', 'Vito', 'Viano', 'Sprinter'];

        $m = strtolower($model);
        foreach ($cvKeywords as $k) {
            if (str_contains($m, strtolower($k))) return 'CV';
        }
        foreach ($pcKeywords as $k) {
            if (str_contains($m, strtolower($k))) return 'PC';
        }
        return 'Unknown';
    }

    private function cleanStr($val): ?string
    {
        if (is_null($val)) return null;
        $s = trim((string) $val);
        return $s !== '' ? $s : null;
    }

    private function parseDate($val): ?Carbon
    {
        if (!$val || $val === '  /  /' || $val === '00/00/0000') return null;
        try {
            if (is_numeric($val)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val));
            }
            return Carbon::createFromFormat('d/m/Y', $val);
        } catch (\Exception $e) {
            return null;
        }
    }
}
