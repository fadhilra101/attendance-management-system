<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'verified_at',
        'role_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Mengatur format date untuk atribut `verified_at`
    protected $dates = ['verified_at'];

    // Definisikan relasi dengan model Role
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Get all permissions assigned to the user through roles
    public function permissions()
    {
        return $this->role->permissions();
    }

    // User.php (Model)
    public function hasRole($role)
    {
        return $this->role && $this->role->name === $role;
    }



    // Check if the user has a specific permission by permission name
    public function hasPermission($permissionName)
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
