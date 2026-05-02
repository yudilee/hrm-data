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
            if (! Schema::hasColumn('personal_access_tokens', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('last_used_at');
            }
            if (! Schema::hasColumn('personal_access_tokens', 'last_used_ip')) {
                $table->string('last_used_ip', 45)->nullable()->after('expires_at');
            }
            if (! Schema::hasColumn('personal_access_tokens', 'allowed_ips')) {
                $table->json('allowed_ips')->nullable()->after('last_used_ip');
            }
            if (! Schema::hasColumn('personal_access_tokens', 'rate_limit')) {
                $table->unsignedInteger('rate_limit')->nullable()->after('allowed_ips')
                    ->comment('Requests per minute override; null = use role default');
            }
        });
    }

    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn(['allowed_ips', 'rate_limit']);
        });
    }
};
