<?php

namespace App\Console\Commands;

use App\Models\MasterCustomer;
use App\Models\MasterVehicle;
use App\Models\ServiceHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeDuplicatesCommand extends Command
{
    protected $signature = 'rts:merge-duplicates {--dry-run : Only show what would be merged}';
    protected $description = 'Find and merge customer records with the same normalized name and city.';

    public function handle()
    {
        $this->info('Starting Customer Deduplication & Merge...');

        // Find groups of customers with same normalized name and city (address_3 or address_5)
        // We exclude records where name is too short to avoid over-merging
        $duplicates = DB::table('master_customers')
            ->select('normalized_name', DB::raw('COALESCE(address_3, address_5) as city'), DB::raw('COUNT(*) as count'))
            ->whereNotNull('normalized_name')
            ->whereRaw('LENGTH(normalized_name) >= 5')
            ->groupBy('normalized_name', 'city')
            ->having('count', '>', 1)
            ->get();

        $this->info("Found {$duplicates->count()} groups of potential duplicates.");

        $bar = $this->output->createProgressBar($duplicates->count());
        $totalMerged = 0;

        foreach ($duplicates as $group) {
            $customers = MasterCustomer::withCount('vehicles')
                ->where('normalized_name', $group->normalized_name)
                ->where(function($q) use ($group) {
                    $q->where('address_3', $group->city)
                      ->orWhere('address_5', $group->city);
                })
                ->orderBy('vehicles_count', 'desc') // Keep the one with most vehicles as primary
                ->orderBy('id', 'asc')
                ->get();

            if ($customers->count() <= 1) {
                $bar->advance();
                continue;
            }

            $primary = $customers->shift(); // First one is primary
            
            foreach ($customers as $secondary) {
                if ($this->option('dry-run')) {
                    $this->info("Would merge {$secondary->name} (ID: {$secondary->id}) into {$primary->name} (ID: {$primary->id})");
                    continue;
                }

                // 1. Re-assign Vehicles
                MasterVehicle::where('primary_customer_id', $secondary->id)
                    ->update(['primary_customer_id' => $primary->id]);

                // 2. Re-assign Service History
                ServiceHistory::where('customer_id', $secondary->id)
                    ->update(['customer_id' => $primary->id]);

                // 3. Merge Legacy Mappings
                $primaryMappings = $primary->legacy_mappings ?? [];
                $secondaryMappings = $secondary->legacy_mappings ?? [];
                foreach ($secondaryMappings as $m) {
                    if (!$this->hasMapping($primaryMappings, $m)) {
                        $primaryMappings[] = $m;
                    }
                }
                $primary->legacy_mappings = $primaryMappings;

                // 4. Merge Phones
                $secondaryPhones = array_filter([$secondary->telp_1, $secondary->telp_2, $secondary->telp_3, $secondary->telp_4]);
                foreach ($secondaryPhones as $phone) {
                    $primaryPhones = [$primary->telp_1, $primary->telp_2, $primary->telp_3, $primary->telp_4];
                    if (!in_array($phone, array_filter($primaryPhones))) {
                        if (!$primary->telp_2) $primary->telp_2 = $phone;
                        elseif (!$primary->telp_3) $primary->telp_3 = $phone;
                        elseif (!$primary->telp_4) $primary->telp_4 = $phone;
                    }
                }

                $primary->save();
                $secondary->delete();
                $totalMerged++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Merged {$totalMerged} duplicate records.");
    }

    private function hasMapping($mappings, $new)
    {
        foreach ($mappings as $m) {
            if ($m['branch'] === $new['branch'] && $m['magic'] === $new['magic']) {
                return true;
            }
        }
        return false;
    }
}
