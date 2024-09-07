<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Office extends Model
{
    use HasFactory;

    // Define which attributes are mass assignable
    protected $fillable = [
        'name',
        'address',
        'gps_lat',
        'gps_lng',
    ];

    // Define any relationships, for example, if an office has many attendances
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
