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
        Schema::create('verifiable_credentials', function (Blueprint $table) {
            $table->id();
            
            // VC Identification
            $table->string('vc_id')->unique(); // UUID for the VC
            $table->string('vc_type'); // Type of credential
            
            // Issuer Information
            $table->foreignId('issuer_organization_id')->constrained('organizations');
            $table->string('issuer_did'); // Issuer's DID
            
            // Subject Information
            $table->string('subject_did'); // Subject's DID (user who receives the VC)
            $table->string('subject_name'); // Subject's name for easy reference
            
            // Credential Data
            $table->json('credential_data'); // The actual credential data
            $table->text('credential_hash'); // SHA-256 hash of the credential
            
            // Blockchain Integration
            $table->string('blockchain_hash')->nullable(); // Hash stored on blockchain
            $table->string('blockchain_tx_hash')->nullable(); // Transaction hash on blockchain
            $table->string('blockchain_network')->default('amoy'); // Blockchain network used
            
            // IPFS Integration
            $table->string('ipfs_hash')->nullable(); // IPFS hash where full VC is stored
            $table->string('ipfs_gateway_url')->nullable(); // IPFS gateway URL
            
            // Digital Signature
            $table->text('digital_signature'); // RSA signature of the VC
            $table->string('signature_algorithm')->default('RSA-SHA256'); // Signature algorithm
            
            // Validity and Status
            $table->timestamp('issued_at'); // When the VC was issued
            $table->timestamp('expires_at')->nullable(); // When the VC expires
            $table->enum('status', ['active', 'revoked', 'expired'])->default('active');
            $table->timestamp('revoked_at')->nullable();
            $table->text('revocation_reason')->nullable();
            
            // Verification Tracking
            $table->integer('verification_count')->default(0); // How many times verified
            $table->timestamp('last_verified_at')->nullable(); // Last verification time
            
            // Metadata
            $table->json('metadata')->nullable(); // Additional metadata
            $table->timestamps();
            
            // Indexes for performance
            $table->index('vc_id');
            $table->index('issuer_did');
            $table->index('subject_did');
            $table->index('vc_type');
            $table->index('status');
            $table->index(['issuer_organization_id', 'status']);
            $table->index(['subject_did', 'status']);
            $table->index('blockchain_hash');
            $table->index('ipfs_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifiable_credentials');
    }
};
