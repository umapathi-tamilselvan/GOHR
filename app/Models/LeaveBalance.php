<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'organization_id',
        'year',
        'total_days',
        'used_days',
        'remaining_days',
    ];

    protected $casts = [
        'year' => 'integer',
        'total_days' => 'integer',
        'used_days' => 'integer',
        'remaining_days' => 'integer',
    ];

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the leave type.
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Get the organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope to get balances for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get balances for a specific year.
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope to get balances for a specific organization.
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Check if user has sufficient leave balance.
     */
    public function hasSufficientBalance(int $requestedDays): bool
    {
        return $this->remaining_days >= $requestedDays;
    }

    /**
     * Get the usage percentage.
     */
    public function getUsagePercentageAttribute(): float
    {
        if ($this->total_days === 0) {
            return 0;
        }

        return round(($this->used_days / $this->total_days) * 100, 2);
    }

    /**
     * Get the remaining percentage.
     */
    public function getRemainingPercentageAttribute(): float
    {
        if ($this->total_days === 0) {
            return 0;
        }

        return round(($this->remaining_days / $this->total_days) * 100, 2);
    }

    /**
     * Update the balance when leave is used.
     */
    public function useLeave(int $days): void
    {
        $this->used_days += $days;
        $this->remaining_days = $this->total_days - $this->used_days;
        $this->save();
    }

    /**
     * Restore the balance when leave is cancelled.
     */
    public function restoreLeave(int $days): void
    {
        $this->used_days = max(0, $this->used_days - $days);
        $this->remaining_days = $this->total_days - $this->used_days;
        $this->save();
    }
}
