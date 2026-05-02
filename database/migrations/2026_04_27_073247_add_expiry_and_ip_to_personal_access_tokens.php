<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->json('allowed_ips')->nullable()->after('expires_at');
            $table->unsignedInteger('rate_limit')->nullable()->after('allowed_ips')
                ->comment('Requests per minute override; null = use role default');
        });
    }

    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn(['allowed_ips', 'rate_limit']);
        });
    }
};
