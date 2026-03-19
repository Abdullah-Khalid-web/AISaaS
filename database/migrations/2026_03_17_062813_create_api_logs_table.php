<?php
// database/migrations/2024_xx_xx_xxxxxx_create_api_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->nullable()->constrained()->onDelete('set null');
            $table->string('endpoint');
            $table->string('method');
            $table->json('request_data')->nullable();
            $table->string('response_status');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_logs');
    }
};
