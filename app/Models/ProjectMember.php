<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'role',
        'joined_date',
        'left_date'
    ];

    protected $casts = [
        'joined_date' => 'date',
        'left_date' => 'date'
    ];

    /**
     * Get the project that the member belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who is a member.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter active members (not left)
     */
    public function scopeActive($query)
    {
        return $query->whereNull('left_date');
    }

    /**
     * Scope to filter by role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Check if member is active
     */
    public function isActive()
    {
        return is_null($this->left_date);
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayNameAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->role));
    }
}
