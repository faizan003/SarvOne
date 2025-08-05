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
        Schema::table('verifiable_credentials', function (Blueprint $table) {
            // Add new fields for blockchain verification and better data storage
            // Only add fields that don't already exist
            if (!Schema::hasColumn('verifiable_credentials', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('issued_at');
            }
            if (!Schema::hasColumn('verifiable_credentials', 'subject_name')) {
                $table->string('subject_name')->nullable()->after('expires_at');
            }
            if (!Schema::hasColumn('verifiable_credentials', 'issuer_name')) {
                $table->string('issuer_name')->nullable()->after('subject_name');
            }
            if (!Schema::hasColumn('verifiable_credentials', 'blockchain_verified')) {
                $table->boolean('blockchain_verified')->default(false)->after('issuer_name');
            }
            if (!Schema::hasColumn('verifiable_credentials', 'last_blockchain_sync')) {
                $table->timestamp('last_blockchain_sync')->nullable()->after('blockchain_verified');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verifiable_credentials', function (Blueprint $table) {
            $table->dropColumn([
                'issued_at',
                'expires_at', 
                'subject_name',
                'issuer_name',
                'blockchain_verified',
                'last_blockchain_sync'
            ]);
        });
    }
}; 