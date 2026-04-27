<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_customers', function (Blueprint $table) {
            // --- Global Identity ---
            $table->id();                                            // Auto-increment global PK
            $table->string('name')->nullable()->index();
            $table->string('normalized_name')->nullable()->index(); // Pre-computed for fast dedup
            $table->boolean('is_company')->default(false)->index(); // true for PT/CV/PO/UD
            $table->string('customer_type', 20)->nullable()         // 'individual' | 'company'
                  ->comment('individual or company, derived from Title field');

            // --- Phone Normalization (for CRM & dedup) ---
            $table->string('phone_fingerprint', 30)->nullable()->index()
                  ->comment('Canonical normalized phone: strip +62/62-, leading 0, spaces, dashes');
            $table->enum('primary_phone_type', ['mobile', 'landline', 'unknown'])
                  ->default('unknown')
                  ->comment('mobile: 08xx/628xx, landline: area code prefix');

            // --- Address ---
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('address_3')->nullable();
            $table->string('address_4')->nullable();
            $table->string('address_5')->nullable()->comment('Usually city');
            $table->text('full_address')->nullable()->comment('Compiled from address_1 through address_5');

            // --- Company Link ---
            $table->string('company_name')->nullable()->comment('Employer/affiliated company name');
            $table->unsignedBigInteger('magic_comp')->default(0)
                  ->comment('Legacy FoxPro company magic ID for cross-reference');

            // --- Contact ---
            $table->string('email')->nullable()->index();
            $table->string('dept', 10)->nullable()->comment('SLS=Sales, WRK=Workshop, MAD=Marketing, SPR=Spareparts');
            $table->string('title', 10)->nullable()->comment('Mr/Mrs/PT/CV/PO etc.');
            $table->string('telp_1')->nullable();
            $table->string('telp_2')->nullable();
            $table->string('telp_3')->nullable();
            $table->string('telp_4')->nullable();
            $table->date('date_created')->nullable();

            // --- Source & Legacy Tracking ---
            $table->string('source', 100)->default('dms_import')
                  ->comment('dms_import, foxpro_recovery, lvs_recovery, history_import');
            $table->json('legacy_mappings')->nullable()
                  ->comment('Array of {branch, magic} pairs from all source systems');

            // --- Data Quality ---
            $table->tinyInteger('data_quality_score')->unsigned()->default(0)
                  ->comment('0-100 completeness score: name+20, phone+20, email+15, address+15, company+10, dept+10, title+10');
            $table->boolean('is_recovered')->default(false)->index()
                  ->comment('true if auto-recovered from FoxPro embedded data — review manually');

            // Odoo columns are added in a later migration
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_customers');
    }
};
