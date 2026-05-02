<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_customers', function (Blueprint $table) {
            // JSON array of all unique branch codes this customer appears in,
            // derived from legacy_mappings. e.g. ["HRMSBY CV","HRMDPS PC","HRMSMG CV"]
            $table->json('sources')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('master_customers', function (Blueprint $table) {
            $table->dropColumn('sources');
        });
    }
};
