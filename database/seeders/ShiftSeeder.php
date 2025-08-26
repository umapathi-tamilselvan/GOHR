<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Shift;
use Carbon\Carbon;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'General Shift',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'break_duration' => 60, // 1 hour
                'overtime_threshold' => 8,
                'grace_period' => 15,
                'is_night_shift' => false,
                'is_active' => true,
                'color_code' => '#10B981',
                'description' => 'Standard 9 AM to 6 PM shift with 1 hour lunch break',
            ],
            [
                'name' => 'Morning Shift',
                'start_time' => '06:00:00',
                'end_time' => '15:00:00',
                'break_duration' => 45,
                'overtime_threshold' => 8,
                'grace_period' => 15,
                'is_night_shift' => false,
                'is_active' => true,
                'color_code' => '#3B82F6',
                'description' => 'Early morning shift from 6 AM to 3 PM',
            ],
            [
                'name' => 'Evening Shift',
                'start_time' => '14:00:00',
                'end_time' => '23:00:00',
                'break_duration' => 45,
                'overtime_threshold' => 8,
                'grace_period' => 15,
                'is_night_shift' => false,
                'is_active' => true,
                'color_code' => '#F59E0B',
                'description' => 'Evening shift from 2 PM to 11 PM',
            ],
            [
                'name' => 'Night Shift',
                'start_time' => '22:00:00',
                'end_time' => '07:00:00',
                'break_duration' => 45,
                'overtime_threshold' => 8,
                'grace_period' => 15,
                'is_night_shift' => true,
                'is_active' => true,
                'color_code' => '#8B5CF6',
                'description' => 'Night shift from 10 PM to 7 AM (next day)',
            ],
            [
                'name' => 'Part Time Morning',
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
                'break_duration' => 0,
                'overtime_threshold' => 4,
                'grace_period' => 10,
                'is_night_shift' => false,
                'is_active' => true,
                'color_code' => '#06B6D4',
                'description' => 'Part-time morning shift from 9 AM to 1 PM',
            ],
            [
                'name' => 'Part Time Evening',
                'start_time' => '14:00:00',
                'end_time' => '18:00:00',
                'break_duration' => 0,
                'overtime_threshold' => 4,
                'grace_period' => 10,
                'is_night_shift' => false,
                'is_active' => true,
                'color_code' => '#EC4899',
                'description' => 'Part-time evening shift from 2 PM to 6 PM',
            ],
            [
                'name' => 'Flexible Shift',
                'start_time' => '10:00:00',
                'end_time' => '19:00:00',
                'break_duration' => 60,
                'overtime_threshold' => 8,
                'grace_period' => 30,
                'is_night_shift' => false,
                'is_active' => true,
                'color_code' => '#84CC16',
                'description' => 'Flexible shift with extended grace period',
            ],
            [
                'name' => 'Weekend Shift',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'break_duration' => 45,
                'overtime_threshold' => 8,
                'grace_period' => 15,
                'is_night_shift' => false,
                'is_active' => true,
                'color_code' => '#EF4444',
                'description' => 'Weekend shift with shorter hours',
            ],
        ];

        foreach ($shifts as $shiftData) {
            Shift::create($shiftData);
        }

        $this->command->info('Shifts seeded successfully!');
    }
}
