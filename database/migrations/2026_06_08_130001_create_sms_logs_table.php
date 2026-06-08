<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Journal des SMS envoyés (via Twilio).
     * Consultable dans l'admin pour tracer les envois et les échecs.
     */
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('to_number');                 // Numéro destinataire (format E.164)
            $table->text('message');
            $table->string('status')->default('pending'); // sent | failed
            $table->string('twilio_sid')->nullable();
            $table->text('error')->nullable();
            $table->string('context')->nullable();        // admin_bulk | test | reminder | result ...
            $table->unsignedBigInteger('sent_by')->nullable(); // user_id de l'admin (si applicable)
            $table->timestamps();

            $table->index('status');
            $table->index('context');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
