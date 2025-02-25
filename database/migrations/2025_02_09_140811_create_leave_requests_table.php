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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id(); // Primary key (id)
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key to users table
            $table->string('leave_type'); // Type of leave (e.g., sick, vacation)
            $table->date('start_date'); // Start date of the leave
            $table->date('end_date'); // End date of the leave
            $table->text('reason')->nullable(); // Reason for the leave
            $table->string('image'); // Image attachment for leave request
            $table->string('status')->default('pending'); // Status of the leave request
            $table->timestamps(); // created_at and updated_at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
