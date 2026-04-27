<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\VehicleContactHistory;
use App\Models\ServiceHistory;
use App\Models\MasterVehicle;

class BuildContactHistoryCommand extends Command
{
    protected $signature = 'rts:build-contact-history';
    protected $description = 'Build vehicle contact history from service histories';

    public function handle()
    {
        $this->info('Starting Vehicle Contact History Build Process...');

        // 1. Clear existing contact history
        DB::table('vehicle_contact_history')->truncate();

        // 2. Insert primary owners from master_vehicles
        $this->info('Inserting primary owners...');
        $vehicles = DB::table('master_vehicles')
            ->whereNotNull('primary_customer_id')
            ->select('id', 'primary_customer_id', 'source', 'last_service_date', 'is_recovered')
            ->get();

        $contactData = [];
        foreach ($vehicles as $v) {
            $contactData[] = [
                'vehicle_id' => $v->id,
                'customer_id' => $v->primary_customer_id,
                'role' => 'owner',
                'source' => $v->source,
                'observed_at' => $v->last_service_date ?? now()->toDateString(),
                'evidence_type' => $v->is_recovered ? 'service_invoice' : 'lvs',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($contactData, 1000) as $chunk) {
            DB::table('vehicle_contact_history')->insertOrIgnore($chunk);
        }

        // 3. Insert service requesters from service histories
        $this->info('Extracting service requesters from service history...');
        $histories = DB::table('service_histories')
            ->select('vehicle_id', 'customer_id', 'source', 'DINVN', 'CINVN')
            ->whereNotNull('vehicle_id')
            ->whereNotNull('customer_id')
            ->get();

        $historyData = [];
        foreach ($histories as $h) {
            $historyData[] = [
                'vehicle_id' => $h->vehicle_id,
                'customer_id' => $h->customer_id,
                'role' => 'service_requester',
                'source' => $h->source,
                'observed_at' => $h->DINVN,
                'evidence_type' => 'service_invoice',
                'invoice_ref' => $h->CINVN,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->info('Inserting ' . count($historyData) . ' service requester records...');
        $bar = $this->output->createProgressBar(count($historyData));
        $bar->start();

        foreach (array_chunk($historyData, 1000) as $chunk) {
            DB::table('vehicle_contact_history')->insertOrIgnore($chunk);
            $bar->advance(count($chunk));
        }
        $bar->finish();

        $this->info("\nVehicle Contact History Build Completed.");
        return Command::SUCCESS;
    }
}
