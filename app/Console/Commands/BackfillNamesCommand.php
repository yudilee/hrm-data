<?php

namespace App\Console\Commands;

use App\Models\MasterCustomer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillNamesCommand extends Command
{
    protected $signature = 'rts:backfill-names {--dry-run : Only show what would be updated}';
    protected $description = 'Fill in missing customer names from their vehicle service history.';

    public function handle()
    {
        $this->info('Starting Name Backfill Process...');

        // Find customers with missing names who have vehicle service history
        $customers = MasterCustomer::whereNull('name')
            ->orWhere('name', '')
            ->get();

        $this->info("Found {$customers->count()} customers with missing names.");

        $bar = $this->output->createProgressBar($customers->count());
        $updated = 0;

        foreach ($customers as $customer) {
            // Find the latest service history record for any of their vehicles
            $latestHistory = $customer->vehicleServiceHistories()
                ->whereNotNull('ENAME')
                ->where('ENAME', '!=', '')
                ->orderBy('DINVN', 'desc')
                ->first();

            if ($latestHistory && $latestHistory->ENAME) {
                if ($this->option('dry-run')) {
                    $this->info("Would update ID {$customer->id} with name '{$latestHistory->ENAME}'");
                } else {
                    $customer->name = $latestHistory->ENAME;
                    $customer->normalized_name = MasterCustomer::normalizeName($customer->name);
                    
                    // Also backfill address if missing
                    if (!$customer->full_address && $latestHistory->EADDR) {
                        $customer->address_1 = $latestHistory->EADDR;
                        $customer->address_5 = $latestHistory->ECITY;
                        $customer->full_address = collect([$latestHistory->EADDR, $latestHistory->ECITY])->filter()->implode(', ');
                    }
                    
                    $customer->save();
                }
                $updated++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully updated {$updated} customer names.");
    }
}
