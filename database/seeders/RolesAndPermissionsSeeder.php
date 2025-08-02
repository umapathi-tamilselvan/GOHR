<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for all modules
        $this->createPermissions();

        // Create or get existing roles
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $hr = Role::firstOrCreate(['name' => 'HR']);
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $employee = Role::firstOrCreate(['name' => 'Employee']);

        // Assign permissions to roles
        $this->assignPermissionsToRoles($superAdmin, $hr, $manager, $employee);
    }

    /**
     * Create all permissions for the system.
     */
    private function createPermissions(): void
    {
        // Define all permissions
        $permissions = [
            // User Management Permissions
            'view users', 'create users', 'edit users', 'delete users', 'assign roles',
            
            // Employee Management Permissions
            'view employees', 'create employees', 'edit employees', 'delete employees',
            'view employee directory', 'manage employee onboarding', 'view employee reports',
            'upload employee documents', 'delete employee documents',
            
            // Attendance Management Permissions
            'view attendances', 'create attendances', 'edit attendances', 'delete attendances',
            'manage attendances', 'view attendance reports',
            
            // Leave Management Permissions
            'view leaves', 'create leaves', 'edit leaves', 'delete leaves',
            'approve leaves', 'reject leaves', 'view leave calendar', 'view leave reports',
            'manage leave types', 'manage leave balances',
            
            // Project Management Permissions
            'view projects', 'create projects', 'edit projects', 'delete projects',
            'assign project managers', 'manage project members', 'view project reports',
            'manage project tasks',
            
            // Task Management Permissions
            'view tasks', 'create tasks', 'edit tasks', 'delete tasks',
            'assign tasks', 'update task status',
            
            // System Permissions
            'view audit log', 'manage system settings', 'view dashboard',
            'export data', 'import data',
            
            // Payroll Management Permissions (for future implementation)
            'view payroll', 'create payroll', 'edit payroll', 'delete payroll',
            'view payroll reports', 'manage salary structures',
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }

    /**
     * Assign permissions to roles based on their responsibilities.
     */
    private function assignPermissionsToRoles(Role $superAdmin, Role $hr, Role $manager, Role $employee): void
    {
        // Clear existing permissions from roles
        $superAdmin->syncPermissions([]);
        $hr->syncPermissions([]);
        $manager->syncPermissions([]);
        $employee->syncPermissions([]);

        // Super Admin - All permissions
        $superAdmin->givePermissionTo(Permission::all());

        // HR Permissions
        $hrPermissions = [
            // User Management (organization-scoped)
            'view users', 'create users', 'edit users', 'delete users', 'assign roles',
            
            // Employee Management (organization-scoped)
            'view employees', 'create employees', 'edit employees', 'delete employees',
            'view employee directory', 'manage employee onboarding', 'view employee reports',
            'upload employee documents', 'delete employee documents',
            
            // Attendance Management (organization-scoped)
            'view attendances', 'create attendances', 'edit attendances', 'delete attendances',
            'manage attendances', 'view attendance reports',
            
            // Leave Management (organization-scoped)
            'view leaves', 'create leaves', 'edit leaves', 'delete leaves',
            'approve leaves', 'reject leaves', 'view leave calendar', 'view leave reports',
            'manage leave types', 'manage leave balances',
            
            // Project Management (organization-scoped)
            'view projects', 'create projects', 'edit projects', 'delete projects',
            'assign project managers', 'manage project members', 'view project reports',
            'manage project tasks',
            
            // Task Management (organization-scoped)
            'view tasks', 'create tasks', 'edit tasks', 'delete tasks',
            'assign tasks', 'update task status',
            
            // System (limited)
            'view dashboard', 'export data', 'import data',
            
            // Payroll Management (organization-scoped)
            'view payroll', 'create payroll', 'edit payroll', 'delete payroll',
            'view payroll reports', 'manage salary structures',
        ];
        $hr->givePermissionTo($hrPermissions);

        // Manager Permissions
        $managerPermissions = [
            // Employee Management (team-scoped)
            'view employees', 'edit employees', 'view employee directory',
            'view employee reports', 'upload employee documents',
            
            // Attendance Management (team-scoped)
            'view attendances', 'create attendances', 'edit attendances',
            'manage attendances', 'view attendance reports',
            
            // Leave Management (team-scoped)
            'view leaves', 'create leaves', 'edit leaves', 'approve leaves',
            'reject leaves', 'view leave calendar', 'view leave reports',
            
            // Project Management (assigned projects)
            'view projects', 'create projects', 'edit projects',
            'manage project members', 'view project reports', 'manage project tasks',
            
            // Task Management (assigned projects)
            'view tasks', 'create tasks', 'edit tasks', 'assign tasks',
            'update task status',
            
            // System
            'view dashboard',
        ];
        $manager->givePermissionTo($managerPermissions);

        // Employee Permissions
        $employeePermissions = [
            // Self-service permissions
            'view employees', 'edit employees', // Own employee record
            'view attendances', 'create attendances', // Own attendance
            'view leaves', 'create leaves', 'edit leaves', // Own leaves
            'view projects', 'view tasks', 'update task status', // Assigned projects/tasks
            
            // System
            'view dashboard',
        ];
        $employee->givePermissionTo($employeePermissions);
    }
}
