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
        Schema::create('government_schemes', function (Blueprint $table) {
            $table->id();
            $table->string('scheme_name');
            $table->text('description');
            $table->string('category'); // education, agriculture, employment, etc.
            $table->decimal('max_income', 12, 2)->nullable(); // Maximum family income
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->json('required_credentials')->nullable(); // Array of required VC types
            $table->json('caste_criteria')->nullable(); // Array of eligible castes
            $table->json('education_criteria')->nullable(); // Education requirements
            $table->json('employment_criteria')->nullable(); // Employment status requirements
            $table->decimal('benefit_amount', 12, 2)->nullable(); // Scheme benefit amount
            $table->string('benefit_type'); // scholarship, loan, subsidy, etc.
            $table->date('application_deadline')->nullable();
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active');
            $table->string('created_by'); // Government official who created it
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['status', 'category']);
            $table->index(['application_deadline']);
            $table->index(['max_income']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('government_schemes');
    }
}; 