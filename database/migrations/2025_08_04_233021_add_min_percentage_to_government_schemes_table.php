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
        Schema::table('government_schemes', function (Blueprint $table) {
            $table->decimal('min_percentage', 5, 2)->nullable()->after('max_age')->comment('Minimum percentage required for education-based schemes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('government_schemes', function (Blueprint $table) {
            $table->dropColumn('min_percentage');
        });
    }
};
