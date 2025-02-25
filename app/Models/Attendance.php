<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    // Status constants for check-in
    const STATUS_PRESENT = 'PRESENT_ON_TIME';
    const STATUS_LATE = 'LATE_ARRIVAL';

    // Status constants for check-out
    const STATUS_EARLY_CHECKOUT = 'EARLY_DEPARTURE';
    const STATUS_OVERTIME = 'EXTENDED_HOURS';
    const STATUS_NORMAL_CHECKOUT = 'NORMAL_DEPARTURE';

    // Combined status constants
    const STATUS_LATE_AND_EARLY = 'LATE_AND_EARLY';
    const STATUS_LATE_AND_OVERTIME = 'LATE_AND_OVERTIME';
    const STATUS_ONTIME_AND_EARLY = 'ONTIME_AND_EARLY';
    const STATUS_ONTIME_AND_OVERTIME = 'ONTIME_AND_OVERTIME';
    const STATUS_ONTIME_AND_NORMAL = 'ONTIME_AND_NORMAL';

    // Other statuses
    const STATUS_ABSENT = 'ABSENT';
    const STATUS_LEAVE = 'ON_LEAVE';
    const STATUS_HOLIDAY = 'HOLIDAY';

    public static function getStatuses()
    {
        return [
            // Single status (only check-in)
            self::STATUS_PRESENT => 'Present on Time',
            self::STATUS_LATE => 'Late Arrival',

            // Combined status (check-in and check-out)
            self::STATUS_LATE_AND_EARLY => 'Late Arrival & Early Departure',
            self::STATUS_LATE_AND_OVERTIME => 'Late Arrival & Extended Hours',
            self::STATUS_ONTIME_AND_EARLY => 'On Time & Early Departure',
            self::STATUS_ONTIME_AND_OVERTIME => 'On Time & Extended Hours',
            self::STATUS_ONTIME_AND_NORMAL => 'On Time & Normal Departure',

            // Other statuses
            self::STATUS_ABSENT => 'Absent',
            self::STATUS_LEAVE => 'On Leave',
            self::STATUS_HOLIDAY => 'Holiday',
        ];
    }

    protected $fillable = [
        'user_id',
        'office_id',
        'qr_code_checkin_id',
        'qr_code_checkout_id',
        'check_in_time',
        'check_out_time',
        'status',
    ];

    public function checkinQrCode()
    {
        return $this->belongsTo(QrCode::class, 'qr_code_checkin_id');
    }

    public function checkoutQrCode()
    {
        return $this->belongsTo(QrCode::class, 'qr_code_checkout_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
