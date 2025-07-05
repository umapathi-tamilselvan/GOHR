<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Organization;
use App\Models\Designation;
use App\Models\Project;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

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
        'designation_id',
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

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function getPermissionTeamId(): int | string | null
    {
        return $this->organization_id;
    }

    /**
     * Get the user's primary role.
     */
    public function getPrimaryRoleAttribute(): string
    {
        $roles = $this->getRoleNames();
        if ($roles->contains('Super Admin')) {
            return 'Super Admin';
        }
        if ($roles->contains('HR')) {
            return 'HR';
        }
        if ($roles->contains('Manager')) {
            return 'Manager';
        }
        return 'Employee';
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
