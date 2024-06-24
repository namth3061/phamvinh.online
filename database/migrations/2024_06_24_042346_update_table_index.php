<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('table_index', function (Blueprint $table) {
            $table->integer('vertical_column')->nullable()->after('column');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('table_index', function (Blueprint $table) {
            $table->dropColumn('vertical_column');
        });
    }
};
