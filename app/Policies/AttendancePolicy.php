<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttendancePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'HR', 'Manager']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        // Users can view their own attendance
        if ($user->id === $attendance->user_id) {
            return true;
        }

        // Super Admin can view all attendance records
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // HR can view attendance for their organization
        if ($user->hasRole('HR')) {
            return $user->organization_id === $attendance->user->organization_id;
        }

        // Manager can view attendance for their organization
        if ($user->hasRole('Manager')) {
            return $user->organization_id === $attendance->user->organization_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['Super Admin', 'HR']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Attendance $attendance): bool
    {
        // Users can update their own attendance (check-out)
        // HR can update attendance for their organization
        return $user->id === $attendance->user_id || $user->hasRole('HR');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        return $user->hasRole(['Super Admin', 'HR']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Attendance $attendance): bool
    {
        return $user->hasRole('Super Admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Attendance $attendance): bool
    {
        return $user->hasRole('Super Admin');
    }
}
