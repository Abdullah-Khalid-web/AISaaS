<?php
// database/migrations/YYYY_MM_DD_HHMMSS_create_ai_tools_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ai_tools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('sdk_download_url')->nullable();
            $table->string('version')->nullable();
            $table->json('supported_platforms')->nullable(); // ['android', 'ios', 'web']
            $table->json('metadata')->nullable(); // Additional tool-specific data
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes(); // For archiving tools without losing license data

            // Indexes
            $table->index('slug');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_tools');
    }
};
