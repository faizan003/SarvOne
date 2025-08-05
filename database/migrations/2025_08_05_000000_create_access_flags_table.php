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
        Schema::create('access_flags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('access_log_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('organization_id');
            $table->enum('flag_type', ['unauthorized_access', 'suspicious_activity', 'data_misuse', 'other']);
            $table->text('flag_reason');
            $table->enum('status', ['pending', 'reviewed', 'resolved', 'dismissed'])->default('pending');
            $table->text('government_notes')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable(); // Government user who reviewed
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('access_log_id')->references('id')->on('access_logs')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['organization_id', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_flags');
    }
}; 