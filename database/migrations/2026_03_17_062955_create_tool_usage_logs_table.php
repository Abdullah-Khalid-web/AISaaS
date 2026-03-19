<?php
// database/migrations/2026_03_17_062955_create_tool_usage_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tool_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Change this line - use 'ai_tools' instead of 'tools'
            $table->foreignId('tool_id')->constrained('ai_tools')->onDelete('cascade');

            $table->string('feature');
            $table->json('input_summary')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tool_usage_logs');
    }
};
