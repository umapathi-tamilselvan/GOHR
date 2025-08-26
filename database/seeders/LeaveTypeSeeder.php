<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;
use App\Models\Organization;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first organization (or create one if none exists)
        $organization = Organization::first();
        
        if (!$organization) {
            $organization = Organization::create([
                'name' => 'Default Organization',
                'description' => 'Default organization for testing',
            ]);
        }

        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'description' => 'Regular annual vacation leave',
                'default_days' => 20,
                'color' => '#3B82F6',
                'requires_approval' => true,
            ],
            [
                'name' => 'Sick Leave',
                'description' => 'Medical leave for illness or injury',
                'default_days' => 15,
                'color' => '#EF4444',
                'requires_approval' => false,
            ],
            [
                'name' => 'Personal Leave',
                'description' => 'Personal time off for personal matters',
                'default_days' => 5,
                'color' => '#10B981',
                'requires_approval' => true,
            ],
            [
                'name' => 'Maternity Leave',
                'description' => 'Leave for expecting mothers',
                'default_days' => 90,
                'color' => '#F59E0B',
                'requires_approval' => true,
            ],
            [
                'name' => 'Paternity Leave',
                'description' => 'Leave for new fathers',
                'default_days' => 14,
                'color' => '#8B5CF6',
                'requires_approval' => true,
            ],
            [
                'name' => 'Bereavement Leave',
                'description' => 'Leave for family bereavement',
                'default_days' => 3,
                'color' => '#6B7280',
                'requires_approval' => false,
            ],
        ];

        foreach ($leaveTypes as $leaveTypeData) {
            LeaveType::updateOrCreate(
                ['name' => $leaveTypeData['name'], 'organization_id' => $organization->id],
                array_merge($leaveTypeData, ['organization_id' => $organization->id])
            );
        }
    }
} 