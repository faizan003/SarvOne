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
        Schema::table('users', function (Blueprint $table) {
            // Make email nullable since we're using phone-based registration
            $table->string('email')->nullable()->change();
            
            // Add phone number field
            $table->string('phone', 15)->unique()->after('name');
            
            // Add SecureVerify specific fields
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->string('verification_status')->default('pending')->after('phone_verified_at'); // pending, in_progress, verified, failed
            $table->integer('trust_score')->nullable()->after('verification_status');
            $table->string('did')->nullable()->unique()->after('trust_score'); // Decentralized Identifier
            $table->string('ipfs_metadata_cid')->nullable()->after('did'); // IPFS Content ID for metadata
            $table->string('blockchain_hash')->nullable()->after('ipfs_metadata_cid'); // SHA256 hash stored on blockchain
            $table->string('blockchain_tx_id')->nullable()->after('blockchain_hash'); // Transaction ID on Polygon
            $table->timestamp('verified_at')->nullable()->after('blockchain_tx_id');
            
            // Make password nullable since it's not required for initial registration
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove added columns
            $table->dropColumn([
                'phone',
                'phone_verified_at',
                'verification_status',
                'trust_score',
                'did',
                'ipfs_metadata_cid',
                'blockchain_hash',
                'blockchain_tx_id',
                'verified_at'
            ]);
            
            // Restore original constraints
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};
