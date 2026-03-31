<?php

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
        Schema::create('service_history_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_history_id')->nullable()->index();
            $table->string('CJOBN', 20)->index();
            $table->string('CINVN', 20)->nullable()->index();
            $table->string('CVCHR', 50)->nullable();
            $table->string('CPART', 50)->nullable();
            $table->string('EDESC', 100)->nullable();
            
            // Increased to (15,2) for consistency and safety
            $table->decimal('QRECV', 15, 2)->default(0);
            $table->decimal('ASPPRC', 15, 2)->default(0);
            $table->decimal('AFIFO', 15, 2)->default(0);
            $table->decimal('ADISCG', 8, 2)->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_history_parts');
    }
};
