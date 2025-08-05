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
        // First, update existing organizations to use new types
        DB::table('organizations')->where('organization_type', 'government')->update(['organization_type' => 'uidai']);
        
        // Then modify the ENUM to include new types
        DB::statement("ALTER TABLE organizations MODIFY COLUMN organization_type ENUM('uidai', 'government', 'land_property', 'bank', 'school_university')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old ENUM values
        DB::statement("ALTER TABLE organizations MODIFY COLUMN organization_type ENUM('bank', 'company', 'school', 'college', 'hospital', 'government', 'uidai', 'ngo', 'fintech', 'scholarship_board', 'welfare_board', 'scheme_partner', 'hr_agency', 'training_provider', 'other')");
    }
};
