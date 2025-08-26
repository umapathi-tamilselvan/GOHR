<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'holiday_name',
        'holiday_date',
        'holiday_type',
        'region',
        'description',
        'is_active',
        'is_optional',
        'year',
    ];

    protected $casts = [
        'holiday_date' => 'date',
        'is_active' => 'boolean',
        'is_optional' => 'boolean',
        'year' => 'integer',
    ];

    protected $dates = [
        'holiday_date',
    ];

    // Accessors
    public function getFormattedDateAttribute(): string
    {
        return $this->holiday_date ? $this->holiday_date->format('d M Y') : 'N/A';
    }

    public function getDayOfWeekAttribute(): string
    {
        return $this->holiday_date ? $this->holiday_date->format('l') : 'N/A';
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->holiday_date && $this->holiday_date->isFuture();
    }

    public function getDaysUntilAttribute(): int
    {
        if (!$this->holiday_date || !$this->isUpcoming) {
            return 0;
        }
        return now()->diffInDays($this->holiday_date, false);
    }

    public function getIsTodayAttribute(): bool
    {
        return $this->holiday_date && $this->holiday_date->isToday();
    }

    public function getIsPastAttribute(): bool
    {
        return $this->holiday_date && $this->holiday_date->isPast();
    }

    // Methods
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isNational(): bool
    {
        return $this->holiday_type === 'national';
    }

    public function isRegional(): bool
    {
        return $this->holiday_type === 'regional';
    }

    public function isCompany(): bool
    {
        return $this->holiday_type === 'optional';
    }

    public function getHolidayTypeLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->holiday_type));
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('holiday_type', $type);
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }

    public function scopeByRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    public function scopeOptional($query)
    {
        return $query->where('is_optional', true);
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_optional', false);
    }

    public function scopeUpcoming($query, $days = 30)
    {
        return $query->where('holiday_date', '>=', now())
            ->where('holiday_date', '<=', now()->addDays($days));
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('holiday_date', now()->month)
            ->whereYear('holiday_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('holiday_date', now()->year);
    }

    public function scopeByMonth($query, $month)
    {
        return $query->whereMonth('holiday_date', $month);
    }

    // Static methods
    public static function getActiveHolidaysCount(): int
    {
        return self::where('is_active', true)->count();
    }

    public static function getHolidaysByType(): array
    {
        return self::selectRaw('holiday_type, COUNT(*) as count')
            ->where('is_active', true)
            ->groupBy('holiday_type')
            ->pluck('count', 'holiday_type')
            ->toArray();
    }

    public static function getUpcomingHolidays(int $days = 30): array
    {
        return self::active()
            ->upcoming($days)
            ->orderBy('holiday_date')
            ->get()
            ->toArray();
    }

    public static function getHolidaysThisMonth(): array
    {
        return self::active()
            ->thisMonth()
            ->orderBy('holiday_date')
            ->get()
            ->toArray();
    }

    public static function getHolidaysThisYear(): array
    {
        return self::active()
            ->thisYear()
            ->orderBy('holiday_date')
            ->get()
            ->toArray();
    }

    public static function isHoliday(Carbon $date, $region = null): bool
    {
        $query = self::where('holiday_date', $date->format('Y-m-d'))
            ->where('is_active', true);

        if ($region) {
            $query->where(function ($q) use ($region) {
                $q->where('region', $region)
                  ->orWhereNull('region');
            });
        }

        return $query->exists();
    }

    public static function getHolidayName(Carbon $date, $region = null): ?string
    {
        $query = self::where('holiday_date', $date->format('Y-m-d'))
            ->where('is_active', true);

        if ($region) {
            $query->where(function ($q) use ($region) {
                $q->where('region', $region)
                  ->orWhereNull('region');
            });
        }

        $holiday = $query->first();
        return $holiday ? $holiday->holiday_name : null;
    }

    public static function getWorkingDaysInMonth(int $month, int $year, $region = null): int
    {
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        $workingDays = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if ($currentDate->dayOfWeek !== 6 && $currentDate->dayOfWeek !== 0) {
                // Check if it's not a holiday
                if (!self::isHoliday($currentDate, $region)) {
                    $workingDays++;
                }
            }
            $currentDate->addDay();
        }

        return $workingDays;
    }

    public static function getHolidayCalendar(int $year): array
    {
        $holidays = self::where('year', $year)
            ->where('is_active', true)
            ->orderBy('holiday_date')
            ->get();

        $calendar = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthHolidays = $holidays->filter(function ($holiday) use ($month) {
                return $holiday->holiday_date->month === $month;
            });

            $calendar[$month] = [
                'month_name' => Carbon::createFromDate($year, $month, 1)->format('F'),
                'holidays' => $monthHolidays->values()->toArray(),
                'total_holidays' => $monthHolidays->count(),
                'working_days' => self::getWorkingDaysInMonth($month, $year),
            ];
        }

        return $calendar;
    }
} 