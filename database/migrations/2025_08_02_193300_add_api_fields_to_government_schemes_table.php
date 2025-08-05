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
            // Organization details
            $table->string('organization_name')->nullable()->after('created_by');
            $table->string('organization_did')->nullable()->after('organization_name');
            
            // Contact information
            $table->string('contact_email')->nullable()->after('organization_did');
            $table->string('contact_phone')->nullable()->after('contact_email');
            $table->string('website_url')->nullable()->after('contact_phone');
            $table->string('application_url')->nullable()->after('website_url');
            
            // Additional scheme details
            $table->json('documents_required')->nullable()->after('application_url');
            $table->text('additional_info')->nullable()->after('documents_required');
            $table->enum('priority_level', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('additional_info');
            $table->string('target_audience')->nullable()->after('priority_level');
            $table->enum('implementation_phase', ['planning', 'active', 'completed', 'discontinued'])->default('active')->after('target_audience');
            
            // API tracking
            $table->string('submitted_via')->default('web')->after('implementation_phase'); // 'web' or 'api'
            $table->string('api_key_used')->nullable()->after('submitted_via');
            $table->timestamp('last_api_sync')->nullable()->after('api_key_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('government_schemes', function (Blueprint $table) {
            $table->dropColumn([
                'organization_name',
                'organization_did',
                'contact_email',
                'contact_phone',
                'website_url',
                'application_url',
                'documents_required',
                'additional_info',
                'priority_level',
                'target_audience',
                'implementation_phase',
                'submitted_via',
                'api_key_used',
                'last_api_sync'
            ]);
        });
    }
};
