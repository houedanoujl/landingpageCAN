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
        Schema::create('admin_otp_logs', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->string('code');
            $table->enum('status', ['sent', 'verified', 'expired', 'failed'])->default('sent');
            $table->string('whatsapp_number');
            $table->integer('verification_attempts')->default(0);
            $table->timestamp('otp_sent_at');
            $table->timestamp('otp_verified_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index('phone');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_otp_logs');
    }
};
