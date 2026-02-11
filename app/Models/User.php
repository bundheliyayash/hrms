<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
        'status',
        'profile_photo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
    // Role Constants
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_EMPLOYEE = 'employee';

    public function employeeDetail()
    {
        return $this->hasOne(EmployeeDetail::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    
    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function roleDetail()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return ($this->role === $role) || ($this->roleDetail && $this->roleDetail->name === $role);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) return true;
        
        return $this->roleDetail ? $this->roleDetail->hasPermission($permission) : false;
    }

    /**
     * Check if user is Admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }
}
