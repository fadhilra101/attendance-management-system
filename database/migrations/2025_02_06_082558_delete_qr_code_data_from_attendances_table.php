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
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['qr_code_checkin_id']);
            $table->dropForeign(['qr_code_checkout_id']);
            $table->dropColumn(['qr_code_checkin_id', 'qr_code_checkout_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Re-add the columns as unsigned big integers (nullable) matching the previous definition
            $table->unsignedBigInteger('qr_code_checkin_id')->nullable();
            $table->unsignedBigInteger('qr_code_checkout_id')->nullable();

            // Re-add the foreign key constraints
            $table->foreign('qr_code_checkin_id')
                ->references('id')->on('qr_codes')
                ->onDelete('cascade');

            $table->foreign('qr_code_checkout_id')
                ->references('id')->on('qr_codes')
                ->onDelete('cascade');
        });
    }
};
