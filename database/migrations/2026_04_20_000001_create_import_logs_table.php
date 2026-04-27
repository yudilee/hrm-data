<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('import_type', 50)
                  ->comment('customers | vehicles | lvs_vehicles | service_history | suppliers');
            $table->enum('status', ['running', 'completed', 'failed'])->default('running');
            $table->unsignedInteger('total_records')->nullable();
            $table->unsignedInteger('processed_records')->default(0);
            $table->unsignedInteger('failed_records')->default(0);
            $table->json('meta')->nullable()
                  ->comment('Extra stats: inserted, merged, ghost_customers, etc.');
            $table->text('error_message')->nullable();
            $table->string('triggered_by', 100)->nullable()
                  ->comment('Username or "system"');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
