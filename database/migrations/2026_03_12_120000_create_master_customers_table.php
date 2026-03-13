<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_customers', function (Blueprint $table) {
            $table->unsignedBigInteger('magic_cust')->primary();
            $table->string('name')->nullable()->index();
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('address_3')->nullable();
            $table->string('address_4')->nullable();
            $table->string('address_5')->nullable()->comment('Usually city');
            $table->text('full_address')->nullable()->comment('Compiled from address_1 through address_5');
            $table->string('company_name')->nullable();
            $table->unsignedBigInteger('magic_comp')->default(0);
            $table->string('email')->nullable()->index();
            $table->string('dept', 10)->nullable();
            $table->string('title', 10)->nullable();
            $table->string('telp_1')->nullable();
            $table->string('telp_2')->nullable();
            $table->string('telp_3')->nullable();
            $table->string('telp_4')->nullable();
            $table->date('date_created')->nullable();
            $table->string('source', 20)->default('customer_import')
                  ->comment('customer_import = from customer file, vehicle_import = placeholder from vehicle file');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_customers');
    }
};
