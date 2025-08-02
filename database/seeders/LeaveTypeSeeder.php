<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizations = Organization::all();

        foreach ($organizations as $organization) {
            // Annual Leave
            LeaveType::create([
                'name' => 'Annual Leave',
                'description' => 'Regular annual vacation leave for employees',
                'default_days' => 21,
                'requires_approval' => true,
                'is_active' => true,
                'color' => '#28a745',
                'organization_id' => $organization->id,
            ]);

            // Sick Leave
            LeaveType::create([
                'name' => 'Sick Leave',
                'description' => 'Medical leave for health-related issues',
                'default_days' => 14,
                'requires_approval' => false,
                'is_active' => true,
                'color' => '#dc3545',
                'organization_id' => $organization->id,
            ]);

            // Personal Leave
            LeaveType::create([
                'name' => 'Personal Leave',
                'description' => 'Personal time off for various reasons',
                'default_days' => 5,
                'requires_approval' => true,
                'is_active' => true,
                'color' => '#ffc107',
                'organization_id' => $organization->id,
            ]);

            // Maternity Leave
            LeaveType::create([
                'name' => 'Maternity Leave',
                'description' => 'Leave for expecting mothers',
                'default_days' => 90,
                'requires_approval' => true,
                'is_active' => true,
                'color' => '#e83e8c',
                'organization_id' => $organization->id,
            ]);

            // Paternity Leave
            LeaveType::create([
                'name' => 'Paternity Leave',
                'description' => 'Leave for new fathers',
                'default_days' => 14,
                'requires_approval' => true,
                'is_active' => true,
                'color' => '#17a2b8',
                'organization_id' => $organization->id,
            ]);

            // Bereavement Leave
            LeaveType::create([
                'name' => 'Bereavement Leave',
                'description' => 'Leave for family bereavement',
                'default_days' => 5,
                'requires_approval' => false,
                'is_active' => true,
                'color' => '#6c757d',
                'organization_id' => $organization->id,
            ]);
        }
    }
}
