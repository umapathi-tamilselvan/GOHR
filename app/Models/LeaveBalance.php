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
        'year',
        'total_days',
        'used_days',
        'remaining_days',
    ];

    protected $casts = [
        'year' => 'integer',
        'total_days' => 'decimal:2',
        'used_days' => 'decimal:2',
        'remaining_days' => 'decimal:2',
    ];

    /**
     * Get the user that owns the leave balance.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the leave type for this balance.
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by year.
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope to filter by leave type.
     */
    public function scopeForLeaveType($query, $leaveTypeId)
    {
        return $query->where('leave_type_id', $leaveTypeId);
    }

    /**
     * Check if user has remaining leave days.
     */
    public function hasRemainingDays(): bool
    {
        return $this->remaining_days > 0;
    }

    /**
     * Get the percentage of leave used.
     */
    public function getUsagePercentageAttribute(): float
    {
        if ($this->total_days == 0) {
            return 0;
        }
        return round(($this->used_days / $this->total_days) * 100, 2);
    }

    /**
     * Get the usage status color.
     */
    public function getUsageColorAttribute(): string
    {
        $percentage = $this->usage_percentage;
        
        if ($percentage >= 80) {
            return 'text-red-600';
        } elseif ($percentage >= 60) {
            return 'text-yellow-600';
        } else {
            return 'text-green-600';
        }
    }
} 