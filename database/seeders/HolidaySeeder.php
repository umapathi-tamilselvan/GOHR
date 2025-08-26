<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Holiday;
use Carbon\Carbon;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = now()->year;
        
        $holidays = [
            // National Holidays
            [
                'holiday_name' => 'Republic Day',
                'holiday_date' => "{$currentYear}-01-26",
                'holiday_type' => 'national',
                'region' => null,
                'description' => 'Celebration of the adoption of the Indian Constitution',
                'is_active' => true,
                'is_optional' => false,
                'year' => $currentYear,
            ],
            [
                'holiday_name' => 'Independence Day',
                'holiday_date' => "{$currentYear}-08-15",
                'holiday_type' => 'national',
                'region' => null,
                'description' => 'Celebration of India\'s independence from British rule',
                'is_active' => true,
                'is_optional' => false,
                'year' => $currentYear,
            ],
            [
                'holiday_name' => 'Gandhi Jayanti',
                'holiday_date' => "{$currentYear}-10-02",
                'holiday_type' => 'national',
                'region' => null,
                'description' => 'Birth anniversary of Mahatma Gandhi',
                'is_active' => true,
                'is_optional' => false,
                'year' => $currentYear,
            ],
            
            // Religious Holidays
            [
                'holiday_name' => 'Holi',
                'holiday_date' => "{$currentYear}-03-25",
                'holiday_type' => 'company',
                'region' => null,
                'description' => 'Festival of colors',
                'is_active' => true,
                'is_optional' => false,
                'year' => $currentYear,
            ],
            [
                'holiday_name' => 'Diwali',
                'holiday_date' => "{$currentYear}-11-12",
                'holiday_type' => 'company',
                'region' => null,
                'description' => 'Festival of lights',
                'is_active' => true,
                'is_optional' => false,
                'year' => $currentYear,
            ],
            [
                'holiday_name' => 'Eid al-Fitr',
                'holiday_date' => "{$currentYear}-04-10",
                'holiday_type' => 'company',
                'region' => null,
                'description' => 'End of Ramadan',
                'is_active' => true,
                'is_optional' => false,
                'year' => $currentYear,
            ],
            [
                'holiday_name' => 'Christmas',
                'holiday_date' => "{$currentYear}-12-25",
                'holiday_type' => 'company',
                'region' => null,
                'description' => 'Christmas Day',
                'is_active' => true,
                'is_optional' => false,
                'year' => $currentYear,
            ],
            
            // Regional Holidays (Maharashtra)
            [
                'holiday_name' => 'Gudi Padwa',
                'holiday_date' => "{$currentYear}-04-09",
                'holiday_type' => 'regional',
                'region' => 'Maharashtra',
                'description' => 'Maharashtrian New Year',
                'is_active' => true,
                'is_optional' => false,
                'year' => $currentYear,
            ],
            [
                'holiday_name' => 'Ganesh Chaturthi',
                'holiday_date' => "{$currentYear}-09-07",
                'holiday_type' => 'regional',
                'region' => 'Maharashtra',
                'description' => 'Birth of Lord Ganesha',
                'is_active' => true,
                'is_optional' => false,
                'year' => $currentYear,
            ],
            
            // Company Holidays
            [
                'holiday_name' => 'New Year\'s Day',
                'holiday_date' => "{$currentYear}-01-01",
                'holiday_type' => 'company',
                'region' => null,
                'description' => 'New Year celebration',
                'is_active' => true,
                'is_optional' => false,
                'year' => $currentYear,
            ],
            [
                'holiday_name' => 'Good Friday',
                'holiday_date' => "{$currentYear}-03-29",
                'holiday_type' => 'company',
                'region' => null,
                'description' => 'Christian observance',
                'is_active' => true,
                'is_optional' => false,
                'year' => $currentYear,
            ],
            
            // Optional Holidays
            [
                'holiday_name' => 'Makar Sankranti',
                'holiday_date' => "{$currentYear}-01-15",
                'holiday_type' => 'optional',
                'region' => null,
                'description' => 'Harvest festival',
                'is_active' => true,
                'is_optional' => true,
                'year' => $currentYear,
            ],
            [
                'holiday_name' => 'Raksha Bandhan',
                'holiday_date' => "{$currentYear}-08-26",
                'holiday_type' => 'optional',
                'region' => null,
                'description' => 'Sibling bond festival',
                'is_active' => true,
                'is_optional' => true,
                'year' => $currentYear,
            ],
            [
                'holiday_name' => 'Janmashtami',
                'holiday_date' => "{$currentYear}-08-26",
                'holiday_type' => 'optional',
                'region' => null,
                'description' => 'Birth of Lord Krishna',
                'is_active' => true,
                'is_optional' => true,
                'year' => $currentYear,
            ],
        ];

        foreach ($holidays as $holidayData) {
            Holiday::create($holidayData);
        }

        $this->command->info('Holidays seeded successfully!');
    }
}
