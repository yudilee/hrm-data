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
        Schema::create('service_history_labours', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_history_id')->nullable()->index();
            $table->string('CJOBN', 20)->index();
            $table->string('CINVN', 20)->nullable()->index();
            $table->string('CDJOB', 20)->nullable();
            $table->string('EMJOB', 200)->nullable();
            
            // Increased from (8,2) to (15,2) to handle possible legacy data overflow
            $table->decimal('QHOUR', 15, 2)->default(0);
            $table->decimal('TAKEN', 15, 2)->default(0);
            $table->decimal('NET', 15, 2)->default(0);
            $table->decimal('DISC', 15, 2)->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_history_labours');
    }
};
