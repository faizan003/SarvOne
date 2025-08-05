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
        // Add 'uidai' to the organization_type ENUM
        DB::statement("ALTER TABLE organizations MODIFY COLUMN organization_type ENUM('bank', 'company', 'school', 'college', 'hospital', 'government', 'uidai', 'ngo', 'fintech', 'scholarship_board', 'welfare_board', 'scheme_partner', 'hr_agency', 'training_provider', 'other')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'uidai' from the organization_type ENUM
        DB::statement("ALTER TABLE organizations MODIFY COLUMN organization_type ENUM('bank', 'company', 'school', 'college', 'hospital', 'government', 'ngo', 'fintech', 'scholarship_board', 'welfare_board', 'scheme_partner', 'hr_agency', 'training_provider', 'other')");
    }
};
