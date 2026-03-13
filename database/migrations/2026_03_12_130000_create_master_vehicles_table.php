<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_vehicles', function (Blueprint $table) {
            $table->unsignedBigInteger('magic')->primary();
            $table->string('registration_no')->nullable()->index();
            $table->string('franc', 1)->nullable()->comment('Brand code: M=Mercedes, V=Vans, Z=?, T=?, S=?');
            $table->string('model')->nullable();
            $table->string('variant')->nullable();
            $table->string('description')->nullable();
            $table->string('chassis_no')->nullable()->index();
            $table->string('mhl_number')->nullable();
            $table->string('engine_no')->nullable()->index();
            $table->string('user_id')->nullable();
            $table->string('status', 1)->nullable()->comment('C=Closed, S=?, O=Open, D=Deleted');
            $table->integer('progress_code')->nullable();
            $table->unsignedBigInteger('customer_magic')->nullable()->index();
            $table->date('reg_date')->nullable();
            $table->date('created_date')->nullable();
            $table->date('last_edited_date')->nullable();
            $table->date('last_service_date')->nullable();
            $table->timestamps();

            $table->foreign('customer_magic')
                  ->references('magic_cust')
                  ->on('master_customers')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_vehicles');
    }
};
