<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'HR', 'Manager', 'Employee']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        // Super Admin can view all projects
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // HR and Manager can view projects in their organization
        if ($user->hasAnyRole(['HR', 'Manager'])) {
            return $user->organization_id === $project->organization_id;
        }

        // Employee can view projects they are members of
        if ($user->hasRole('Employee')) {
            return $user->organization_id === $project->organization_id &&
                   $project->members()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'HR', 'Manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        // Super Admin can update all projects
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // HR can update projects in their organization
        if ($user->hasRole('HR')) {
            return $user->organization_id === $project->organization_id;
        }

        // Manager can update projects they manage
        if ($user->hasRole('Manager')) {
            return $user->organization_id === $project->organization_id &&
                   $project->manager_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        // Super Admin can delete all projects
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // HR can delete projects in their organization
        if ($user->hasRole('HR')) {
            return $user->organization_id === $project->organization_id;
        }

        // Manager can delete projects they manage
        if ($user->hasRole('Manager')) {
            return $user->organization_id === $project->organization_id &&
                   $project->manager_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return $this->delete($user, $project);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $this->delete($user, $project);
    }

    /**
     * Determine whether the user can manage project members.
     */
    public function manageMembers(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }

    /**
     * Determine whether the user can manage project tasks.
     */
    public function manageTasks(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }

    /**
     * Determine whether the user can view project reports.
     */
    public function viewReports(User $user, Project $project): bool
    {
        return $this->view($user, $project);
    }
}
