<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use App\Models\Organization;

class LeaveBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first organization
        $organization = Organization::first();
        
        if (!$organization) {
            $this->command->error('No organization found. Please run LeaveTypeSeeder first.');
            return;
        }

        // Get all leave types for the organization
        $leaveTypes = LeaveType::where('organization_id', $organization->id)->get();
        
        if ($leaveTypes->isEmpty()) {
            $this->command->error('No leave types found. Please run LeaveTypeSeeder first.');
            return;
        }

        // Get all users in the organization
        $users = User::where('organization_id', $organization->id)->get();
        
        if ($users->isEmpty()) {
            $this->command->error('No users found in the organization.');
            return;
        }

        $currentYear = now()->year;

        foreach ($users as $user) {
            foreach ($leaveTypes as $leaveType) {
                // Check if leave balance already exists
                $existingBalance = LeaveBalance::where('user_id', $user->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('year', $currentYear)
                    ->first();

                if (!$existingBalance) {
                    LeaveBalance::create([
                        'user_id' => $user->id,
                        'leave_type_id' => $leaveType->id,
                        'year' => $currentYear,
                        'total_days' => $leaveType->default_days,
                        'used_days' => 0,
                        'remaining_days' => $leaveType->default_days,
                    ]);
                }
            }
        }

        $this->command->info('Leave balances created successfully for ' . $users->count() . ' users and ' . $leaveTypes->count() . ' leave types.');
    }
} 