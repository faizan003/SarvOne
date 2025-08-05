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
        Schema::create('credential_access_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->string('organization_name');
            $table->unsignedBigInteger('user_id');
            $table->string('user_did');
            $table->string('user_name');
            $table->string('credential_type');
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->text('purpose')->nullable();
            $table->json('verification_details')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['organization_id', 'created_at']);
            $table->index(['user_did', 'created_at']);
            $table->index(['credential_type', 'status']);
            
            // Foreign key constraints
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credential_access_logs');
    }
};
