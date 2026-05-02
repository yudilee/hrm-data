<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_histories', function (Blueprint $table) {
            $table->id();
            // Removed UNIQUE because WIP numbers can wrap/reset over the years in legacy FoxPro data
            $table->string('CJOBN', 20)->index();
            $table->string('CINVN', 20)->nullable()->index();
            $table->string('CNPOL', 20)->nullable()->index();
            $table->string('CHASN', 50)->nullable()->index();
            $table->string('CENGN', 50)->nullable();

            $table->date('DRECV')->nullable();
            $table->date('DINVN')->nullable();

            $table->string('CCUST', 20)->nullable();
            $table->string('ENAME', 100)->nullable();
            $table->string('EADDR', 150)->nullable();
            $table->string('ECITY', 50)->nullable();
            $table->string('EPHON', 50)->nullable();

            $table->string('ETYPE', 50)->nullable();
            $table->date('DSTNK')->nullable();
            $table->unsignedInteger('EKMPOS')->nullable();

            $table->decimal('ALBRS', 15, 2)->default(0);
            $table->decimal('ASPTS', 15, 2)->default(0);
            $table->decimal('ASSPS', 15, 2)->default(0); // Added for Sublet
            $table->decimal('ASUBS', 15, 2)->default(0); // Subtotal (Labour + Sparepart + Sublet)
            $table->decimal('AOTHS1', 15, 2)->default(0);
            $table->decimal('AOTHS2', 15, 2)->default(0);
            $table->decimal('DISC', 15, 2)->default(0);
            $table->decimal('ATAXS', 15, 2)->default(0);
            $table->decimal('AMTRS', 15, 2)->default(0);
            $table->decimal('PTAX', 8, 2)->nullable();

            $table->unsignedBigInteger('customer_id')->nullable()->index()
                ->comment('Resolved global customer ID');
            $table->unsignedBigInteger('vehicle_id')->nullable()->index()
                ->comment('Resolved global vehicle ID');
            $table->string('source', 100)->nullable()
                ->comment('Branch where this invoice was issued, e.g. HRMSBY PC');

            $table->timestamps();

            // Note: FKs are not strict here because service histories might be imported
            // before master data in some flows, but logically they point to the master tables.
            $table->foreign('customer_id')
                ->references('id')
                ->on('master_customers')
                ->nullOnDelete();

            $table->foreign('vehicle_id')
                ->references('id')
                ->on('master_vehicles')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_histories');
    }
};
