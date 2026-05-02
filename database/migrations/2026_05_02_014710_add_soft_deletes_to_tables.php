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
        Schema::table('service_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('service_histories', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('master_customers', function (Blueprint $table) {
            if (!Schema::hasColumn('master_customers', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('master_vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('master_vehicles', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('master_suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('master_suppliers', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_histories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('master_customers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('master_vehicles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('master_suppliers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
