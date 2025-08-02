<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any employees.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view employees');
    }

    /**
     * Determine whether the user can view the employee.
     */
    public function view(User $user, Employee $employee): bool
    {
        // Check if user has permission to view employees
        if (!$user->hasPermissionTo('view employees')) {
            return false;
        }

        // Super admin can view all employees
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // HR can view employees in their organization
        if ($user->hasRole('HR')) {
            return $user->organization_id === $employee->organization_id;
        }

        // Manager can view their team members
        if ($user->hasRole('Manager')) {
            return $user->organization_id === $employee->organization_id &&
                   ($employee->manager_id === $user->id || $employee->user_id === $user->id);
        }

        // Employee can view their own profile
        if ($user->hasRole('Employee')) {
            return $employee->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create employees.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create employees');
    }

    /**
     * Determine whether the user can update the employee.
     */
    public function update(User $user, Employee $employee): bool
    {
        // Check if user has permission to edit employees
        if (!$user->hasPermissionTo('edit employees')) {
            return false;
        }

        // Super admin can update all employees
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // HR can update employees in their organization
        if ($user->hasRole('HR')) {
            return $user->organization_id === $employee->organization_id;
        }

        // Manager can update their team members
        if ($user->hasRole('Manager')) {
            return $user->organization_id === $employee->organization_id &&
                   $employee->manager_id === $user->id;
        }

        // Employee can update their own profile
        if ($user->hasRole('Employee')) {
            return $employee->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the employee.
     */
    public function delete(User $user, Employee $employee): bool
    {
        // Check if user has permission to delete employees
        if (!$user->hasPermissionTo('delete employees')) {
            return false;
        }

        // Super admin can delete all employees
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // HR can only delete employees in their organization
        if ($user->hasRole('HR')) {
            return $user->organization_id === $employee->organization_id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the employee.
     */
    public function restore(User $user, Employee $employee): bool
    {
        return $this->delete($user, $employee);
    }

    /**
     * Determine whether the user can permanently delete the employee.
     */
    public function forceDelete(User $user, Employee $employee): bool
    {
        return $this->delete($user, $employee);
    }

    /**
     * Determine whether the user can view employee directory.
     */
    public function viewDirectory(User $user): bool
    {
        return $user->hasPermissionTo('view employee directory');
    }

    /**
     * Determine whether the user can manage employee onboarding.
     */
    public function manageOnboarding(User $user): bool
    {
        return $user->hasPermissionTo('manage employee onboarding');
    }

    /**
     * Determine whether the user can view employee reports.
     */
    public function viewReports(User $user): bool
    {
        return $user->hasPermissionTo('view employee reports');
    }

    /**
     * Determine whether the user can upload documents for the employee.
     */
    public function uploadDocuments(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('upload employee documents') && $this->update($user, $employee);
    }

    /**
     * Determine whether the user can delete documents for the employee.
     */
    public function deleteDocuments(User $user, Employee $employee): bool
    {
        return $user->hasPermissionTo('delete employee documents') && $this->update($user, $employee);
    }
} 