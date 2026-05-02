<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MasterCustomer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LinkHistoryCommand extends Command
{
    protected $signature = 'rts:link-history';

    protected $description = 'Link service history records to global vehicle and customer records';

    public function handle()
    {
        $this->info('Starting Service History Linking...');
        $startTime = microtime(true);

        $vehicleLinked = $this->linkVehicles();
        $customerLinked = $this->linkCustomers();

        $elapsed = round(microtime(true) - $startTime, 1);

        // Summary report
        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Histories linked to vehicles',  $vehicleLinked],
                ['Histories linked to customers', $customerLinked],
                ['Unlinked (no vehicle)',          DB::table('service_histories')->whereNull('vehicle_id')->where('CHASN', '!=', '')->count()],
                ['Unlinked (no customer)',         DB::table('service_histories')->whereNull('customer_id')->whereNotNull('CCUST')->where('CCUST', '!=', '')->count()],
            ]
        );

        $this->info("Linking completed in {$elapsed}s.");

        return Command::SUCCESS;
    }

    /**
     * Link vehicles via chassis_no → vehicle_id (single SQL JOIN UPDATE).
     */
    private function linkVehicles(): int
    {
        $unlinked = DB::table('service_histories')
            ->whereNull('vehicle_id')
            ->where('CHASN', '!=', '')
            ->count();

        if ($unlinked === 0) {
            $this->info('✅ All vehicles already linked.');

            return 0;
        }

        $this->info("Linking {$unlinked} service histories to vehicles...");
        $affected = DB::update("
            UPDATE service_histories sh
            JOIN master_vehicles mv ON sh.CHASN = mv.chassis_no
            SET sh.vehicle_id = mv.id
            WHERE sh.vehicle_id IS NULL AND sh.CHASN != ''
        ");

        $this->info("  ✓ Linked {$affected} histories to vehicles.");

        return $affected;
    }

    /**
     * Link customers via legacy_mappings lookup.
     * Uses an in-memory map to avoid N × whereJsonContains() scans.
     */
    private function linkCustomers(): int
    {
        // 1. Get all distinct unlinked (source, CCUST) pairs
        $unlinkedPairs = DB::table('service_histories')
            ->select('source', 'CCUST')
            ->whereNull('customer_id')
            ->whereNotNull('CCUST')
            ->where('CCUST', '!=', '')
            ->distinct()
            ->get();

        $totalPairs = $unlinkedPairs->count();

        if ($totalPairs === 0) {
            $this->info('✅ All customers already linked.');

            return 0;
        }

        $this->info("Building customer lookup map for {$totalPairs} unlinked (source, CCUST) pairs...");

        // 2. Build in-memory map: "branch|magic" → customer_id
        $lookupMap = [];
        MasterCustomer::whereNotNull('legacy_mappings')
            ->select('id', 'legacy_mappings')
            ->cursor()
            ->each(function ($customer) use (&$lookupMap) {
                $mappings = is_array($customer->legacy_mappings)
                    ? $customer->legacy_mappings
                    : json_decode($customer->legacy_mappings, true) ?? [];
                foreach ($mappings as $m) {
                    if (isset($m['branch'], $m['magic'])) {
                        $key = $m['branch'].'|'.$m['magic'];
                        $lookupMap[$key] = $customer->id;
                    }
                }
            });

        $this->info('  Lookup map has '.count($lookupMap).' entries. Resolving and linking...');

        // 3. Resolve each pair using the map, then batch-update
        $totalLinked = 0;
        $unresolved = 0;
        $bar = $this->output->createProgressBar($totalPairs);
        $bar->start();

        // Group pairs by resolved customer_id for batch updating
        $updates = []; // customer_id => [[source, ccust], ...]

        foreach ($unlinkedPairs as $pair) {
            $source = $pair->source;
            $rawCust = trim($pair->CCUST);

            // Fix: Only treat it as numeric magic if it's purely digits (or has leading zeros)
            // Do NOT strip letters, e.g. B0000011 should NOT become 11.
            $numericMagic = ctype_digit($rawCust) ? (int) $rawCust : 0;

            // Strategy A: exact raw string match
            $customerId = $lookupMap[$source.'|'.$rawCust] ?? null;

            // Strategy B: numeric magic match
            if ($customerId === null && $numericMagic > 0) {
                $customerId = $lookupMap[$source.'|'.$numericMagic] ?? null;
            }

            if ($customerId !== null) {
                $updates[$customerId][] = ['source' => $source, 'ccust' => $pair->CCUST];
            } else {
                $unresolved++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // 4. Perform batched updates grouped by customer_id
        foreach ($updates as $customerId => $pairs) {
            foreach ($pairs as $p) {
                $affected = DB::table('service_histories')
                    ->where('source', $p['source'])
                    ->where('CCUST', $p['ccust'])
                    ->whereNull('customer_id')
                    ->update(['customer_id' => $customerId]);
                $totalLinked += $affected;
            }
        }

        $this->info("  ✓ Linked {$totalLinked} histories to customers. ({$unresolved} pairs unresolvable — run rts:recover-ghosts)");

        return $totalLinked;
    }
}
