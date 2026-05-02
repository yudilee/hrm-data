<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MasterCustomer;
use App\Models\MasterSupplier;
use App\Models\MasterVehicle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'auth_source' => 'local',
            ]
        );

        // Demo regular user
        User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'role' => 'user',
                'auth_source' => 'local',
            ]
        );

        // Sample data only in local/testing
        if (app()->environment('local', 'testing')) {
            $customers = MasterCustomer::factory()
                ->count(20)
                ->sequence(fn (Sequence $sequence) => [
                    'source' => ['HRMSBY CV', 'HRMJKT CV', 'HRMDPS PC', 'HRMSBY PC'][$sequence->index % 4],
                ])
                ->create();

            MasterVehicle::factory()
                ->count(15)
                ->sequence(fn (Sequence $sequence) => [
                    'primary_customer_id' => $customers->random()->id,
                    'source' => ['HRMSBY CV', 'HRMJKT CV'][$sequence->index % 2],
                ])
                ->create();

            MasterSupplier::factory()
                ->count(5)
                ->create();
        }
    }
}
