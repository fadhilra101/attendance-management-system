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
            $table->dropColumn('status');
            $table->enum('status', [
                'PRESENT_ON_TIME',
                'LATE_ARRIVAL',
                'EARLY_DEPARTURE',
                'EXTENDED_HOURS',
                'ABSENT',
                'HALF_DAY',
                'ON_LEAVE'
            ])->after('check_out_time');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->enum('status', ['checked-in', 'checked-out'])->after('check_out_time');
        });
    }
};
