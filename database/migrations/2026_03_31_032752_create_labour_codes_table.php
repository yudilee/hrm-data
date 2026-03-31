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
        Schema::create('labour_codes', function (Blueprint $table) {
            $table->id();
            $table->string('model_prefix', 10)->index();
            $table->string('group_name', 255)->nullable();
            $table->string('labour_key', 50)->nullable();
            $table->string('code', 255)->nullable();
            $table->text('description')->nullable();
            $table->decimal('time_hours', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labour_codes');
    }
};
