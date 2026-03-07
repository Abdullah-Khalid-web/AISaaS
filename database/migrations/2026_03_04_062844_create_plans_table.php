<?php
// database/migrations/YYYY_MM_DD_HHMMSS_create_plans_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained('ai_tools')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('billing_cycle', ['monthly', 'yearly', 'one_time'])->default('monthly');
            $table->integer('duration_days')->nullable();
            $table->integer('device_limit')->default(1);
            $table->integer('api_call_limit')->nullable();
            $table->integer('concurrent_users')->default(1);
            $table->json('feature_flags')->nullable();
            $table->json('limitations')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('tool_id');
            $table->index('price');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('plans');
    }
};
