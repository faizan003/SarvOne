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
        Schema::table('access_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('scheme_id')->nullable()->after('organization_did')->comment('ID of the government scheme');
            $table->string('action')->nullable()->after('scheme_id')->comment('Action performed (e.g., scheme_notification)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access_logs', function (Blueprint $table) {
            $table->dropColumn(['scheme_id', 'action']);
        });
    }
};
