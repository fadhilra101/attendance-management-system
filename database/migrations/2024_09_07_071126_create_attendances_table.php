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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('office_id')->constrained()->onDelete('cascade');
            $table->foreignId('qr_code_checkin_id')->nullable()->constrained('qr_codes')->onDelete('set null'); // Link to QR code for check-in
            $table->foreignId('qr_code_checkout_id')->nullable()->constrained('qr_codes')->onDelete('set null'); // Link to QR code for check-out
            $table->timestamp('check_in_time')->nullable(); // Nullable, check-in first
            $table->timestamp('check_out_time')->nullable(); // Nullable, check-out later
            $table->enum('status', ['checked-in', 'checked-out']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
