<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MasterCustomer;
use App\Models\MasterVehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecoverGhostsCommand extends Command
{
    protected $signature = 'rts:recover-ghosts {--dry-run : Preview changes without writing to the database}';

    protected $description = 'Recover ghost vehicles and orphan customers from Service History';

    private bool $isDryRun = false;

    private int $customerRecovered = 0;

    private int $customerMerged = 0;

    private int $vehicleRecovered = 0;

    public function handle()
    {
        $this->isDryRun = $this->option('dry-run');

        if ($this->isDryRun) {
            $this->warn('DRY RUN MODE — No changes will be written to the database.');
        }

        $this->info('Starting Ghost Recovery Process...');

        $this->recoverCustomers();
        $this->recoverVehicles();

        // Link histories only once at the very end (was called redundantly after each sub-step)
        if (! $this->isDryRun) {
            $this->info('Running final history linking pass...');
            $this->call('rts:link-history');
        }

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Customers Recovered (new)', $this->customerRecovered],
                ['Customers Merged (existing match)', $this->customerMerged],
                ['Ghost Vehicles Recovered', $this->vehicleRecovered],
            ]
        );

        $this->info('Ghost Recovery Completed.');

        return Command::SUCCESS;
    }

    private function recoverCustomers(): void
    {
        $this->info('Finding orphan customers in Service History...');

        $orphans = DB::table('service_histories')
            ->select('CCUST', 'ENAME', 'EADDR', 'ECITY', 'EPHON', 'ETYPE', 'source', DB::raw('MAX(DINVN) as latest_invoice'))
            ->whereNull('customer_id')
            ->whereNotNull('CCUST')
            ->where('CCUST', '!=', '')
            ->whereNotNull('ENAME')
            ->where('ENAME', '!=', '')
            ->groupBy('CCUST', 'ENAME', 'EADDR', 'ECITY', 'EPHON', 'ETYPE', 'source')
            ->get();

        $this->info("Found {$orphans->count()} potential orphan records to recover.");

        $bar = $this->output->createProgressBar($orphans->count());
        $bar->start();

        foreach ($orphans as $orphan) {
            $normName = MasterCustomer::normalizeName($orphan->ENAME);
            $phone1 = $this->cleanStr($orphan->EPHON);
            $fingerprint = MasterCustomer::canonicalPhone($phone1);
            $phoneType = MasterCustomer::detectPhoneType($phone1);
            $numericMagic = ctype_digit($orphan->CCUST) ? (int) $orphan->CCUST : 0;

            // 1. Primary Strategy: Name + Phone match (Strongest)
            $existing = null;
            if ($normName && $fingerprint) {
                $existing = MasterCustomer::where('normalized_name', $normName)
                    ->where('phone_fingerprint', $fingerprint)
                    ->first();
            }

            // 2. Secondary Strategy: Name + City match (Fallback)
            // Only attempt if Name is reasonably long (at least 5 chars) to avoid over-merging short names like "BUDI"
            if (! $existing && $normName && strlen($normName) >= 5 && $orphan->ECITY) {
                $existing = MasterCustomer::where('normalized_name', $normName)
                    ->where(function ($q) use ($orphan) {
                        $q->where('address_3', $orphan->ECITY)
                            ->orWhere('address_5', $orphan->ECITY);
                    })
                    ->first();
            }

            if ($existing) {
                // Merge legacy mapping into the existing customer
                $mappings = $existing->legacy_mappings ?? [];
                $newMappings = [['branch' => $orphan->source, 'magic' => $orphan->CCUST]];
                if ($numericMagic > 0 && (string) $numericMagic !== (string) $orphan->CCUST) {
                    $newMappings[] = ['branch' => $orphan->source, 'magic' => $numericMagic];
                }
                foreach ($newMappings as $m) {
                    if (! $this->hasMapping($mappings, $m)) {
                        $mappings[] = $m;
                    }
                }

                // Append new phone number if it doesn't exist in telp_1..4
                if ($phone1) {
                    $phones = [$existing->telp_1, $existing->telp_2, $existing->telp_3, $existing->telp_4];
                    if (! in_array($phone1, array_filter($phones))) {
                        // Find first empty slot
                        if (! $existing->telp_2) {
                            $existing->telp_2 = $phone1;
                        } elseif (! $existing->telp_3) {
                            $existing->telp_3 = $phone1;
                        } elseif (! $existing->telp_4) {
                            $existing->telp_4 = $phone1;
                        }
                    }
                }

                if (! $this->isDryRun) {
                    $existing->legacy_mappings = $mappings;
                    // Upgrade source from generic value to a real branch code if we now know it
                    if (in_array($existing->source, ['foxpro_recovery', null, '']) && $orphan->source) {
                        $existing->source = $orphan->source;
                    }
                    MasterCustomer::syncSourcesFromMappings($existing);
                    $existing->save();
                }
                $this->customerMerged++;
            } else {
                // Create new ghost customer — use the actual branch source from service history,
                // NOT the generic 'foxpro_recovery' label
                $branchSource = $orphan->source ?? 'foxpro_recovery';

                $mappings = [['branch' => $branchSource, 'magic' => $orphan->CCUST]];
                if ($numericMagic > 0 && (string) $numericMagic !== (string) $orphan->CCUST) {
                    $mappings[] = ['branch' => $branchSource, 'magic' => $numericMagic];
                }

                if (! $this->isDryRun) {
                    $customer = MasterCustomer::create([
                        'name' => $orphan->ENAME,
                        'normalized_name' => $normName,
                        'phone_fingerprint' => $fingerprint,
                        'primary_phone_type' => $phoneType,
                        'address_1' => $this->cleanStr($orphan->EADDR),
                        'address_3' => $this->cleanStr($orphan->ECITY),
                        'full_address' => collect([$this->cleanStr($orphan->EADDR), $this->cleanStr($orphan->ECITY)])->filter()->implode(', '),
                        'telp_1' => $phone1,
                        'is_company' => in_array($orphan->ETYPE, ['PT', 'CV', 'PO', 'UD']),
                        'customer_type' => in_array($orphan->ETYPE, ['PT', 'CV', 'PO', 'UD']) ? 'company' : 'individual',
                        'source' => $branchSource,
                        'is_recovered' => true,
                        'legacy_mappings' => $mappings,
                        'data_quality_score' => 20,
                        'date_created' => $orphan->latest_invoice,
                    ]);
                    MasterCustomer::syncSourcesFromMappings($customer);
                    $customer->saveQuietly();
                }
                $this->customerRecovered++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Recovered {$this->customerRecovered} new orphan customers. Merged {$this->customerMerged} into existing records.");
    }

    private function recoverVehicles(): void
    {
        $this->info('Finding ghost vehicles in Service History...');

        $ghosts = DB::table('service_histories')
            ->select('CHASN', 'CENGN', 'CNPOL', 'source', 'customer_id', DB::raw('MAX(DINVN) as latest_invoice'))
            ->whereNull('vehicle_id')
            ->whereNotNull('CHASN')
            ->where('CHASN', '!=', '')
            ->groupBy('CHASN', 'CENGN', 'CNPOL', 'source', 'customer_id')
            ->get();

        $this->info("Found {$ghosts->count()} potential ghost vehicle records.");

        $groupedGhosts = $ghosts->groupBy('CHASN');
        $this->info("Unique ghost chassis numbers: {$groupedGhosts->count()}");

        $bar = $this->output->createProgressBar($groupedGhosts->count());
        $bar->start();

        foreach ($groupedGhosts as $chassis => $records) {
            $sorted = $records->sortByDesc('latest_invoice');
            $latest = $sorted->first();

            if (! $this->isDryRun) {
                try {
                    MasterVehicle::firstOrCreate(
                        ['chassis_no' => $chassis],
                        [
                            'engine_no' => $this->cleanStr($latest->CENGN),
                            'registration_no' => $this->cleanStr($latest->CNPOL),
                            'primary_customer_id' => $latest->customer_id,
                            'source' => 'foxpro_recovery',
                            'is_recovered' => true,
                            'last_service_date' => $latest->latest_invoice,
                            'branches_visited' => $sorted->pluck('source')->unique()->values()->toArray(),
                        ]
                    );
                } catch (\Exception $e) {
                    $this->warn("  Skipping chassis {$chassis}: {$e->getMessage()}");
                    $bar->advance();

                    continue;
                }
            }
            $this->vehicleRecovered++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Recovered {$this->vehicleRecovered} ghost vehicles.");
    }

    private function hasMapping(array $mappings, array $newMapping): bool
    {
        foreach ($mappings as $m) {
            if ($m['branch'] === $newMapping['branch'] && $m['magic'] == $newMapping['magic']) {
                return true;
            }
        }

        return false;
    }

    private function cleanStr($val): ?string
    {
        $val = trim((string) $val);

        return $val === '' ? null : $val;
    }
}
