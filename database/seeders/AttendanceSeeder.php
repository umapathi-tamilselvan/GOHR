<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = User::role('Employee')->get();
        $today = Carbon::today();

        foreach ($employees as $employee) {
            for ($i = 0; $i < 30; $i++) {
                $date = $today->copy()->subDays($i);

                // Skip weekends
                if ($date->isWeekend()) {
                    continue;
                }

                $checkIn = $date->copy()->setTime(9, rand(0, 30));
                $checkOut = $date->copy()->setTime(rand(17, 18), rand(0, 59));
                $workedMinutes = $checkIn->diffInMinutes($checkOut);

                $status = 'Incomplete';
                if ($workedMinutes >= 480) {
                    $status = 'Full Day';
                } elseif ($workedMinutes >= 240) {
                    $status = 'Half Day';
                }

                Attendance::create([
                    'user_id' => $employee->id,
                    'date' => $date,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'worked_minutes' => $workedMinutes,
                    'status' => $status,
                ]);
            }
        }
    }
}
