<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FULLTEXT indexes for search queries
        Schema::table('master_customers', function (Blueprint $table) {
            if (! $this->indexExists('master_customers', 'master_customers_ft_search')) {
                $table->fullText(['name', 'email', 'telp_1', 'company_name'], 'master_customers_ft_search');
            }
        });

        Schema::table('master_vehicles', function (Blueprint $table) {
            if (! $this->indexExists('master_vehicles', 'master_vehicles_ft_search')) {
                $table->fullText(['registration_no', 'chassis_no', 'description'], 'master_vehicles_ft_search');
            }
        });

        Schema::table('service_histories', function (Blueprint $table) {
            if (! $this->indexExists('service_histories', 'service_histories_ft_search')) {
                $table->fullText(['CINVN', 'CHASN', 'CNPOL'], 'service_histories_ft_search');
            }
        });

        // Composite indexes for common query patterns
        Schema::table('service_histories', function (Blueprint $table) {
            if (! $this->indexExists('service_histories', 'service_histories_vehicle_dinvn')) {
                $table->index(['vehicle_id', 'DINVN'], 'service_histories_vehicle_dinvn');
            }
            if (! $this->indexExists('service_histories', 'service_histories_customer_dinvn')) {
                $table->index(['customer_id', 'DINVN'], 'service_histories_customer_dinvn');
            }
            if (! $this->indexExists('service_histories', 'service_histories_chasn_source')) {
                $table->index(['CHASN', 'source'], 'service_histories_chasn_source');
            }
        });

        // Indexes on service history child tables
        Schema::table('service_history_labours', function (Blueprint $table) {
            if (! $this->indexExists('service_history_labours', 'service_history_labours_cinvn_cjobn')) {
                $table->index(['CINVN', 'CJOBN'], 'service_history_labours_cinvn_cjobn');
            }
        });

        Schema::table('service_history_parts', function (Blueprint $table) {
            if (! $this->indexExists('service_history_parts', 'service_history_parts_cinvn_cjobn')) {
                $table->index(['CINVN', 'CJOBN'], 'service_history_parts_cinvn_cjobn');
            }
        });

        // Indexes for master_customers filters
        Schema::table('master_customers', function (Blueprint $table) {
            if (! $this->indexExists('master_customers', 'master_customers_source')) {
                $table->index('source', 'master_customers_source');
            }
        });

        // Indexes for master_vehicles filters
        Schema::table('master_vehicles', function (Blueprint $table) {
            if (! $this->indexExists('master_vehicles', 'master_vehicles_customer_date')) {
                $table->index(['primary_customer_id', 'last_service_date'], 'master_vehicles_customer_date');
            }
            if (! $this->indexExists('master_vehicles', 'master_vehicles_franchise')) {
                $table->index('true_franchise', 'master_vehicles_franchise');
            }
        });

        // Indexes on deleted_at for soft delete queries
        $softDeleteTables = ['master_customers', 'master_vehicles', 'service_histories', 'master_suppliers'];
        foreach ($softDeleteTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'deleted_at') && ! $this->indexExists($tableName, "{$tableName}_deleted_at")) {
                    $table->index('deleted_at', "{$tableName}_deleted_at");
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('master_customers', function (Blueprint $table) {
            $table->dropFullText('master_customers_ft_search');
            $table->dropIndexIfExists(['source']);
        });

        Schema::table('master_vehicles', function (Blueprint $table) {
            $table->dropFullText('master_vehicles_ft_search');
            $table->dropIndexIfExists(['primary_customer_id', 'last_service_date']);
            $table->dropIndexIfExists(['true_franchise']);
        });

        Schema::table('service_histories', function (Blueprint $table) {
            $table->dropFullText('service_histories_ft_search');
            $table->dropIndexIfExists(['vehicle_id', 'DINVN']);
            $table->dropIndexIfExists(['customer_id', 'DINVN']);
            $table->dropIndexIfExists(['CHASN', 'source']);
        });

        Schema::table('service_history_labours', function (Blueprint $table) {
            $table->dropIndexIfExists(['CINVN', 'CJOBN']);
        });

        Schema::table('service_history_parts', function (Blueprint $table) {
            $table->dropIndexIfExists(['CINVN', 'CJOBN']);
        });

        $softDeleteTables = ['master_customers', 'master_vehicles', 'service_histories', 'master_suppliers'];
        foreach ($softDeleteTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropIndexIfExists(["{$tableName}_deleted_at"]);
            });
        }
    }

    protected function indexExists(string $table, string $name): bool
    {
        return collect(Schema::getIndexes($table))->contains('name', $name);
    }
};
