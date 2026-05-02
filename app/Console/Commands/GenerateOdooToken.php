<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateOdooToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:generate-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new Sanctum API token for Odoo Synchronization';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $admin = User::where('role', 'admin')->first();

        if (! $admin) {
            $this->error('No admin user found. Please create an admin user first.');

            return Command::FAILURE;
        }

        $tokenName = 'Odoo Sync Token - '.now()->format('Y-m-d H:i');
        $token = $admin->createToken($tokenName);

        $this->info("Successfully generated new Odoo API Token for user: {$admin->name}");
        $this->warn('Store this token securely. It will not be shown again!');
        $this->line('');
        $this->info($token->plainTextToken);
        $this->line('');

        return Command::SUCCESS;
    }
}
