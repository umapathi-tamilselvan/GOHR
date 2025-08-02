<?php

namespace App\Policies;

use App\Models\LeaveBalance;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LeaveBalancePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'hr', 'manager', 'employee']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LeaveBalance $leaveBalance): bool
    {
        // Super admin can view all leave balances
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // HR can view leave balances in their organization
        if ($user->hasRole('hr') && $user->organization_id === $leaveBalance->organization_id) {
            return true;
        }

        // Manager can view leave balances of their team members
        if ($user->hasRole('manager')) {
            $teamMemberIds = User::where('manager_id', $user->id)->pluck('id');
            return $teamMemberIds->contains($leaveBalance->user_id) || $leaveBalance->user_id === $user->id;
        }

        // Employee can only view their own leave balances
        return $leaveBalance->user_id === $user->id;
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
    public function update(User $user, LeaveBalance $leaveBalance): bool
    {
        // Super admin can update any leave balance
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // HR can update leave balances in their organization
        return $user->hasRole('hr') && $user->organization_id === $leaveBalance->organization_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LeaveBalance $leaveBalance): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LeaveBalance $leaveBalance): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LeaveBalance $leaveBalance): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can view leave balance for a specific user.
     */
    public function viewLeaveBalance(User $user, User $targetUser): bool
    {
        // Super admin can view any user's leave balance
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // HR can view leave balances of users in their organization
        if ($user->hasRole('hr') && $user->organization_id === $targetUser->organization_id) {
            return true;
        }

        // Manager can view leave balances of their team members
        if ($user->hasRole('manager')) {
            $teamMemberIds = User::where('manager_id', $user->id)->pluck('id');
            return $teamMemberIds->contains($targetUser->id) || $targetUser->id === $user->id;
        }

        // Employee can only view their own leave balance
        return $targetUser->id === $user->id;
    }

    /**
     * Determine whether the user can initialize leave balances.
     */
    public function initializeLeaveBalances(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'hr']);
    }

    /**
     * Determine whether the user can bulk update leave balances.
     */
    public function bulkUpdateLeaveBalances(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'hr']);
    }

    /**
     * Determine whether the user can export leave balances.
     */
    public function exportLeaveBalances(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'hr', 'manager']);
    }
}
