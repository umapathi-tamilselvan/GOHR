<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'organization_id',
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

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function approvedLeaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'approved_by');
    }

    public function getPermissionTeamId(): int | string | null
    {
        return $this->organization_id;
    }

    public function getPrimaryRoleAttribute()
    {
        $roles = $this->getRoleNames();
        $order = ['Super Admin', 'HR', 'Manager', 'Employee'];

        if ($roles->isEmpty()) {
            return null;
        }

        foreach ($order as $role) {
            if ($roles->contains($role)) {
                return $role;
            }
        }

        return $roles->first();
    }

    /**
     * The "booted" method of the model.
     */
    public static function boot()
    {
        parent::boot();

        // set team id when creating user
        static::creating(function ($user) {
            if (session()->has('organization_id')) {
                $user->organization_id = session('organization_id');
            }
        });
    }
}
