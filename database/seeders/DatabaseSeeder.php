<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Generate default Admin user
        User::updateOrCreate(
            ['email' => 'yudi.it@hrmsby.co.id'],
            [
                'name' => 'Yudi (Admin)',
                'password' => \Illuminate\Support\Facades\Hash::make('lcs119'),
                'role' => 'admin',
                'auth_source' => 'local',
            ]
        );
    }
}
