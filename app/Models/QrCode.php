<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    use HasFactory;

    public function attendance()
    {
        return $this->hasOne(Attendance::class, 'qr_code_checkin_id')
            ->orWhere('qr_code_checkout_id', $this->id);
    }
}
