<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_vehicles', function (Blueprint $table) {
            // --- Global Identity ---
            $table->id();                                            // Auto-increment global PK
            $table->unsignedBigInteger('legacy_magic')->nullable()->index()
                ->comment('Original FoxPro/LVS magic ID, preserved for reference only');

            // --- Vehicle Identifiers (chassis is the true global key) ---
            $table->string('registration_no')->nullable()->index();
            $table->string('chassis_no')->nullable()->unique()->index()
                ->comment('VIN/Chassis — used as global dedup key across all sources');
            $table->string('mhl_number')->nullable();
            $table->string('engine_no')->nullable()->index();

            // --- Vehicle Specs ---
            $table->string('franc', 1)->nullable()
                ->comment('Brand franchise code: M=Mercedes PC, V=Vans/CV, Z=?, T=?, S=?');
            $table->string('model')->nullable();
            $table->string('variant')->nullable();
            $table->string('description')->nullable();

            // --- Status ---
            $table->string('user_id')->nullable();
            $table->string('status', 1)->nullable()
                ->comment('C=Closed, S=Suspended, O=Open, D=Deleted');
            $table->integer('progress_code')->nullable();

            // --- Owner Link ---
            $table->unsignedBigInteger('primary_customer_id')->nullable()->index()
                ->comment('FK to master_customers.id — the primary known owner/operator');

            // --- Dates ---
            $table->date('reg_date')->nullable();
            $table->date('created_date')->nullable();
            $table->date('last_edited_date')->nullable();
            $table->date('last_service_date')->nullable();

            // --- Multi-branch Metadata ---
            $table->string('source', 100)->nullable()
                ->comment('Primary source branch, e.g. HRMSBY PC');
            $table->string('true_franchise')->nullable()
                ->comment('Computed true franchise from all branch visits');
            $table->json('branches_visited')->nullable()
                ->comment('Array of branch codes where this vehicle has been seen');
            $table->json('legacy_mappings')->nullable()
                ->comment('Array of {branch, magic} pairs from all source systems');

            // --- Data Recovery Flag ---
            $table->boolean('is_recovered')->default(false)->index()
                ->comment('true if auto-recovered from FoxPro history — not in original LVS/DMS master');

            // Odoo columns are added in a later migration
            $table->timestamps();

            // FK defined after customer table is created (same migration order)
            $table->foreign('primary_customer_id')
                ->references('id')
                ->on('master_customers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_vehicles');
    }
};
