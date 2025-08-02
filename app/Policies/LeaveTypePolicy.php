<?php

namespace App\Policies;

use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LeaveTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'hr', 'manager']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LeaveType $leaveType): bool
    {
        // Super admin can view all leave types
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // HR and Manager can view leave types in their organization
        return $user->organization_id === $leaveType->organization_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'hr']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LeaveType $leaveType): bool
    {
        // Super admin can update any leave type
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // HR can update leave types in their organization
        return $user->hasRole('hr') && $user->organization_id === $leaveType->organization_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LeaveType $leaveType): bool
    {
        // Super admin can delete any leave type
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // HR can delete leave types in their organization (if no associated leaves)
        return $user->hasRole('hr') && $user->organization_id === $leaveType->organization_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LeaveType $leaveType): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LeaveType $leaveType): bool
    {
        return $user->hasRole('super-admin');
    }
}
