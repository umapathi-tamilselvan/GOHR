<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'approver_id',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public static function leaveTypes(): array
    {
        return [
            'Sick Leave',
            'Casual Leave',
            'Paid Leave',
            'Maternity Leave',
            'Work From Home',
        ];
    }

    /**
     * Get the next approver based on the applicant's role
     */
    public function getNextApproverRole(): string
    {
        if ($this->user->hasRole('Employee')) {
            return 'Manager';
        } elseif ($this->user->hasRole('Manager')) {
            return 'HR';
        }
        
        return 'Super Admin';
    }

    /**
     * Check if the given user can approve this leave request
     */
    public function canBeApprovedBy(User $user): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($this->status !== 'pending') {
            return false;
        }

        if ($this->user->hasRole('Employee') && $user->hasRole('Manager')) {
            return $user->organization_id === $this->user->organization_id;
        }

        if ($this->user->hasRole('Manager') && $user->hasRole('HR')) {
            return $user->organization_id === $this->user->organization_id;
        }

        return false;
    }
}
