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
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('organization_id');
            $table->string('access_type'); // 'profile', 'vc_details', 'employment_data', etc.
            $table->text('details')->nullable(); // JSON or text describing what was accessed
            $table->string('organization_type'); // bank, employer, college, etc.
            $table->string('organization_name');
            $table->string('user_did');
            $table->string('organization_did');
            $table->json('accessed_vcs')->nullable(); // Array of VC IDs that were accessed
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            
            $table->index(['user_id', 'created_at']);
            $table->index(['organization_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_logs');
    }
};
