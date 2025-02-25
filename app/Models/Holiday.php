<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'name',
        'date',
        'type',
        'description',
        'is_recurring'
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
    ];

    public static function isHoliday($date)
    {
        $dateToCheck = \Carbon\Carbon::parse($date);

        return static::where(function ($query) use ($dateToCheck) {
            $query->where('date', $dateToCheck->format('Y-m-d'))
                ->orWhere(function ($q) use ($dateToCheck) {
                    $q->where('is_recurring', true)
                        ->whereMonth('date', $dateToCheck->month)
                        ->whereDay('date', $dateToCheck->day);
                });
        })->exists();
    }
}
