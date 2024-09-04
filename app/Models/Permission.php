<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    // Menentukan nama tabel jika tidak menggunakan konvensi Laravel
    protected $table = 'permissions';

    // Menentukan primary key dan tipe datanya
    protected $primaryKey = 'id';
    public $incrementing = true; // Auto-incrementing ID
    protected $keyType = 'int'; // Tipe data primary key adalah integer

    // Definisikan atribut yang dapat diisi secara massal
    protected $fillable = ['name', 'desc'];

    // Definisikan relasi dengan model Role (nullable)
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role', 'permission_id', 'role_id');
    }
}
