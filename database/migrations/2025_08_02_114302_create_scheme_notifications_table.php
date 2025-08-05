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
        Schema::create('scheme_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('scheme_id')->constrained('government_schemes')->onDelete('cascade');
            $table->enum('notification_type', ['new_scheme', 'eligibility_update', 'deadline_reminder'])->default('new_scheme');
            $table->text('message');
            $table->timestamp('sent_at')->nullable();
            $table->boolean('sms_sent')->default(false);
            $table->string('sms_status')->nullable(); // 'sent', 'failed', 'pending'
            $table->json('eligibility_details')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'scheme_id', 'notification_type']);
            $table->index(['sent_at']);
            $table->index(['sms_sent']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheme_notifications');
    }
};
