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
            // Add Aadhaar number field for Indian identity verification
            if (!Schema::hasColumn('users', 'aadhaar_number')) {
                $table->string('aadhaar_number', 12)->nullable()->after('phone');
            }
            
            // Add verification step tracking
            if (!Schema::hasColumn('users', 'verification_step')) {
                $table->string('verification_step')->default('phone')->after('verification_status');
            }
            
            // Add selfie path if not already exists
            if (!Schema::hasColumn('users', 'selfie_path')) {
                $table->string('selfie_path')->nullable()->after('verification_step');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['aadhaar_number', 'verification_step']);
            
            // Only drop selfie_path if it was added by this migration
            if (Schema::hasColumn('users', 'selfie_path')) {
                $table->dropColumn('selfie_path');
            }
        });
    }
};
