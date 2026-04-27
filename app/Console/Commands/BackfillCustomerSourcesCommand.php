<?php

namespace App\Console\Commands;

use App\Models\MasterCustomer;
use Illuminate\Console\Command;

class BackfillCustomerSourcesCommand extends Command
{
    protected $signature   = 'rts:backfill-sources {--chunk=500 : Records per chunk}';
    protected $description = 'Backfill the sources JSON column on master_customers from legacy_mappings';

    public function handle(): int
    {
        $this->info('Backfilling master_customers.sources from legacy_mappings...');

        $total     = MasterCustomer::count();
        $chunk     = (int) $this->option('chunk');
        $bar       = $this->output->createProgressBar($total);
        $bar->start();

        $updated  = 0;
        $multi    = 0;

        MasterCustomer::chunkById($chunk, function ($customers) use (&$updated, &$multi, $bar) {
            foreach ($customers as $customer) {
                MasterCustomer::syncSourcesFromMappings($customer);
                $customer->saveQuietly(); // skip model events / observers
                $updated++;
                if (count($customer->sources ?? []) > 1) {
                    $multi++;
                }
            }
            $bar->advance($customers->count());
        });

        $bar->finish();
        $this->newLine();
        $this->info("Done. Updated {$updated} customers. {$multi} have multiple sources.");

        return Command::SUCCESS;
    }
}
