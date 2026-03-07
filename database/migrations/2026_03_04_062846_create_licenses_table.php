<?php
// database/migrations/YYYY_MM_DD_HHMMSS_create_licenses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tool_id')->constrained('ai_tools')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
            $table->string('license_key')->unique();
            $table->string('package_name');
            $table->string('bundle_id')->nullable();
            $table->string('domain')->nullable();
            $table->string('environment')->default('production');
            $table->json('allowed_domains')->nullable();
            $table->json('allowed_ips')->nullable();
            $table->json('device_ids')->nullable();
            $table->integer('device_count')->default(0);
            $table->integer('api_calls_used')->default(0);
            $table->integer('api_calls_limit')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->enum('status', [
                'pending',
                'active',
                'suspended',
                'expired',
                'revoked',
                'cancelled'
            ])->default('pending');
            $table->json('metadata')->nullable();
            $table->boolean('is_trial')->default(false);
            $table->boolean('auto_renew')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index('tool_id');
            $table->index('plan_id');
            $table->index('license_key');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('licenses');
    }
};
