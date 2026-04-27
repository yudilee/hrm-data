<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_access_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('token_id')->nullable()->index();
            $table->string('token_name', 100)->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('method', 10);
            $table->string('path', 500);
            $table->json('query_params')->nullable();
            $table->string('ip_address', 45);
            $table->string('user_agent', 500)->nullable();
            $table->smallInteger('response_status')->default(200)->index();
            $table->unsignedInteger('response_time_ms')->default(0);
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['token_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['ip_address', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_access_logs');
    }
};
