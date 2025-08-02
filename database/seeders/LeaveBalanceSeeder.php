<?php

namespace Database\Seeders;

use App\Models\LeaveBalance;
use App\Models\User;
use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $currentYear = date('Y');

        foreach ($users as $user) {
            $leaveTypes = LeaveType::where('organization_id', $user->organization_id)->get();

            foreach ($leaveTypes as $leaveType) {
                // Create leave balance for current year
                LeaveBalance::create([
                    'user_id' => $user->id,
                    'leave_type_id' => $leaveType->id,
                    'organization_id' => $user->organization_id,
                    'year' => $currentYear,
                    'total_days' => $leaveType->default_days,
                    'used_days' => rand(0, min(5, $leaveType->default_days)), // Random usage
                    'remaining_days' => $leaveType->default_days - rand(0, min(5, $leaveType->default_days)),
                ]);

                // Create leave balance for next year
                LeaveBalance::create([
                    'user_id' => $user->id,
                    'leave_type_id' => $leaveType->id,
                    'organization_id' => $user->organization_id,
                    'year' => $currentYear + 1,
                    'total_days' => $leaveType->default_days,
                    'used_days' => 0,
                    'remaining_days' => $leaveType->default_days,
                ]);
            }
        }
    }
}
