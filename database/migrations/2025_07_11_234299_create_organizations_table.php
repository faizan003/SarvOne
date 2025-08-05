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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            
            // Step 1: Legal & Organization Details
            $table->string('legal_name');
            $table->enum('organization_type', [
                'uidai', 'government', 'land_property', 'bank', 'school_university'
            ]);
            $table->string('registration_number');
            
            // Step 2: Contact & Identity Information
            $table->string('official_email')->unique();
            $table->string('official_phone');
            $table->string('website_url')->nullable();
            $table->text('head_office_address');
            $table->text('branch_address')->nullable();
            
            // Step 3: Representative/Authorized Signatory
            $table->string('signatory_name');
            $table->string('signatory_designation');
            $table->string('signatory_email');
            $table->string('signatory_phone');
            $table->string('signatory_id_document')->nullable(); // File path
            
            // Step 4: Technical & Blockchain Details
            $table->string('wallet_address');
            $table->string('technical_contact_name')->nullable();
            $table->string('technical_contact_email')->nullable();
            
            // Step 5: VC/Scope Details
            $table->json('write_scopes')->nullable(); // JSON array of write permissions
            $table->json('read_scopes')->nullable(); // JSON array of read permissions
            $table->enum('expected_volume', ['1-50', '51-200', '201-1000', '1000+']);
            $table->text('use_case_description');
            
            // Step 6: Compliance & Documentation
            $table->string('registration_certificate')->nullable(); // File path
            $table->string('authorization_proof')->nullable(); // File path
            $table->boolean('terms_agreement')->default(false);
            
            // DID and Cryptographic Keys
            $table->string('did')->unique()->nullable(); // SarvOne DID: did:sarvone:org:id
            $table->text('public_key')->nullable(); // For VC signing
            $table->text('private_key')->nullable(); // Encrypted private key
            $table->string('key_algorithm')->default('RSA-2048'); // Key algorithm used
            
            // Verification and Status
            $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable(); // Admin remarks
            $table->boolean('is_active')->default(true);
            
            // Statistics and Trust
            $table->integer('vcs_issued')->default(0); // Count of VCs issued
            $table->integer('vcs_verified')->default(0); // Count of VCs verified
            $table->decimal('trust_score', 5, 2)->default(0.00); // Organization trust score (0-100)
            
            // Authentication
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('did');
            $table->index('organization_type');
            $table->index('verification_status');
            $table->index('wallet_address');
            $table->index(['is_active', 'verification_status']);
            $table->index('official_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
