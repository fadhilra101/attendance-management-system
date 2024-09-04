<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    // Menentukan nama tabel jika tidak menggunakan konvensi Laravel
    protected $table = 'roles';

    // Menentukan primary key dan tipe datanya
    protected $primaryKey = 'id';
    public $incrementing = false; // Non-incrementing karena menggunakan string
    protected $keyType = 'string'; // Tipe data primary key adalah string

    // Definisikan atribut yang dapat diisi secara massal
    protected $fillable = ['id', 'name'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($role) {
            // Generate a unique 10-character ID if not set
            if (empty($role->id)) {
                $role->id = Str::random(10);
            }
        });
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
