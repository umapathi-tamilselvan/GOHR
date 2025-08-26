<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'default_days',
        'color',
        'requires_approval',
        'organization_id',
    ];

    protected $casts = [
        'default_days' => 'integer',
        'requires_approval' => 'boolean',
    ];

    /**
     * Get the organization that owns the leave type.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the leaves for this leave type.
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    /**
     * Get the leave balances for this leave type.
     */
    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Get the CSS color class for the leave type.
     */
    public function getColorClassAttribute(): string
    {
        return "bg-[" . $this->color . "]";
    }
} 