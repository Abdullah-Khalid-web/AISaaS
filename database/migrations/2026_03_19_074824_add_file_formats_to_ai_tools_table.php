// database/migrations/xxxx_xx_xx_add_file_formats_to_ai_tools_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ai_tools', function (Blueprint $table) {
            $table->json('file_formats')->nullable()->after('metadata');
        });
    }

    public function down()
    {
        Schema::table('ai_tools', function (Blueprint $table) {
            $table->dropColumn('file_formats');
        });
    }
};
