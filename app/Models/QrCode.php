<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QrCode extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = Str::random(32); // Generate 32 character random string
        });
    }

    public function attendance()
    {
        return $this->hasOne(Attendance::class, 'qr_code_checkin_id')
            ->orWhere('qr_code_checkout_id', $this->id);
    }
}
