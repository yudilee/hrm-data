<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convert any existing 'invoice' role users to 'user'.
     * The 'invoice' role is being removed in favour of the simpler admin/user model.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'invoice')
            ->update(['role' => 'user']);
    }

    public function down(): void
    {
        // Cannot reverse: we don't know which users were originally 'invoice'.
        // This migration is intentionally irreversible.
    }
};
