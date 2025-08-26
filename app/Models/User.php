<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'notes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];



    
    // Override the default authentication query
    public function newEloquentBuilder($query)
    {
        return parent::newEloquentBuilder($query);
    }

    // Role checking methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin' && $this->is_active;
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru' && $this->is_active;
    }

    public function isSiswa(): bool
    {
        return $this->role === 'siswa' && $this->is_active;
    }

    // Check if user can login
    public function canLogin(): bool
    {
        return $this->is_active;
    }

    // Relationships
    public function guru()
    {
        return $this->hasOne(\App\Models\Guru::class, 'user_id');
    }

    public function siswa()
    {
        return $this->hasOne(\App\Models\Siswa::class, 'user_id');
    }
}
