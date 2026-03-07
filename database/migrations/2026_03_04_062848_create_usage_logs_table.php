<?php
// database/migrations/YYYY_MM_DD_HHMMSS_create_usage_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('event_type');
            $table->string('event_name')->nullable();
            $table->string('device_id')->nullable();
            $table->string('device_model')->nullable();
            $table->string('device_platform')->nullable();
            $table->string('platform_version')->nullable();
            $table->string('app_version')->nullable();
            $table->string('sdk_version')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('location')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->boolean('was_successful')->default(true);
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('license_id');
            $table->index('event_type');
            $table->index('device_id');
            $table->index('created_at');
            $table->index('was_successful');
        });
    }

    public function down()
    {
        Schema::dropIfExists('usage_logs');
    }
};
