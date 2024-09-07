<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

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
