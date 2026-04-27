<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_contact_history', function (Blueprint $table) {
            $table->id();

            // --- Links ---
            $table->unsignedBigInteger('vehicle_id')->index()
                  ->comment('FK to master_vehicles.id');
            $table->unsignedBigInteger('customer_id')->index()
                  ->comment('FK to master_customers.id');

            // --- Relationship Type ---
            $table->enum('role', ['owner', 'driver', 'contact', 'service_requester', 'past_owner'])
                  ->default('service_requester')
                  ->comment('owner=legal owner, driver=company employee driver, contact=fleet manager, service_requester=brought car for service, past_owner=historical');

            // --- Evidence ---
            $table->string('source', 30)->nullable()
                  ->comment('Branch where this was observed, e.g. HRMSBY PC');
            $table->date('observed_at')->nullable()
                  ->comment('Date this relationship was observed (invoice date)');
            $table->enum('evidence_type', ['dms', 'service_invoice', 'lvs'])
                  ->default('service_invoice')
                  ->comment('How this relationship was discovered');
            $table->string('invoice_ref', 40)->nullable()
                  ->comment('The specific invoice (CJOBN or CINVN) that shows this relationship');

            $table->timestamps();

            // FKs
            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('master_vehicles')
                  ->cascadeOnDelete();

            $table->foreign('customer_id')
                  ->references('id')
                  ->on('master_customers')
                  ->cascadeOnDelete();

            // Unique constraint to avoid duplicate entries for the same observation
            $table->unique(['vehicle_id', 'customer_id', 'role', 'observed_at', 'source'],
                           'unique_vehicle_contact_observation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_contact_history');
    }
};
