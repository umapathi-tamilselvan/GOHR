<?php

namespace App\Policies;

use App\Models\Leave;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LeavePolicy
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
    public function view(User $user, Leave $leave): bool
    {
        // Super admin can view all leaves
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // HR can view leaves in their organization
        if ($user->hasRole('hr') && $user->organization_id === $leave->organization_id) {
            return true;
        }

        // Manager can view leaves from their team members
        if ($user->hasRole('manager')) {
            $teamMemberIds = User::where('manager_id', $user->id)->pluck('id');
            return $teamMemberIds->contains($leave->user_id) || $leave->user_id === $user->id;
        }

        // Employee can only view their own leaves
        return $leave->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'hr', 'manager', 'employee']);
    }

    /**
     * Determine whether the user can create leaves for other users.
     */
    public function createForOthers(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'hr', 'manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Leave $leave): bool
    {
        // Only pending leaves can be updated
        if ($leave->status !== 'pending') {
            return false;
        }

        // Super admin can update any leave
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // HR can update leaves in their organization
        if ($user->hasRole('hr') && $user->organization_id === $leave->organization_id) {
            return true;
        }

        // Manager can update leaves from their team members
        if ($user->hasRole('manager')) {
            $teamMemberIds = User::where('manager_id', $user->id)->pluck('id');
            return $teamMemberIds->contains($leave->user_id);
        }

        // Employee can only update their own pending leaves
        return $leave->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Leave $leave): bool
    {
        // Only pending leaves can be cancelled
        if ($leave->status !== 'pending') {
            return false;
        }

        // Super admin can cancel any pending leave
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // HR can cancel pending leaves in their organization
        if ($user->hasRole('hr') && $user->organization_id === $leave->organization_id) {
            return true;
        }

        // Manager can cancel pending leaves from their team members
        if ($user->hasRole('manager')) {
            $teamMemberIds = User::where('manager_id', $user->id)->pluck('id');
            return $teamMemberIds->contains($leave->user_id);
        }

        // Employee can only cancel their own pending leaves
        return $leave->user_id === $user->id;
    }

    /**
     * Determine whether the user can approve the leave.
     */
    public function approve(User $user, Leave $leave): bool
    {
        // Only pending leaves can be approved
        if ($leave->status !== 'pending') {
            return false;
        }

        // Super admin can approve any leave
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // HR can approve leaves in their organization
        if ($user->hasRole('hr') && $user->organization_id === $leave->organization_id) {
            return true;
        }

        // Manager can approve leaves from their team members
        if ($user->hasRole('manager')) {
            $teamMemberIds = User::where('manager_id', $user->id)->pluck('id');
            return $teamMemberIds->contains($leave->user_id);
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Leave $leave): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Leave $leave): bool
    {
        return $user->hasRole('super-admin');
    }
}
