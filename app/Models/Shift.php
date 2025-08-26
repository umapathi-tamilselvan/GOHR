<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'break_duration',
        'overtime_threshold',
        'grace_period',
        'is_night_shift',
        'is_active',
        'color_code',
        'description',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_night_shift' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $dates = [
        'start_time',
        'end_time',
    ];

    // Relationships
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    // Accessors
    public function getFormattedStartTimeAttribute(): string
    {
        return $this->start_time ? $this->start_time->format('H:i') : 'N/A';
    }

    public function getFormattedEndTimeAttribute(): string
    {
        return $this->end_time ? $this->end_time->format('H:i') : 'N/A';
    }

    public function getDurationAttribute(): int
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }
        return $this->start_time->diffInHours($this->end_time);
    }

    public function getFormattedDurationAttribute(): string
    {
        $hours = $this->duration;
        return "{$hours} hours";
    }

    public function getBreakDurationFormattedAttribute(): string
    {
        $minutes = $this->break_duration;
        if ($minutes >= 60) {
            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;
            return $remainingMinutes > 0 ? "{$hours}h {$remainingMinutes}m" : "{$hours}h";
        }
        return "{$minutes}m";
    }

    public function getGracePeriodFormattedAttribute(): string
    {
        $minutes = $this->grace_period;
        return "{$minutes} minutes";
    }

    public function getShiftTypeAttribute(): string
    {
        if ($this->is_night_shift) {
            return 'Night Shift';
        }
        return 'Day Shift';
    }

    // Methods
    public function calculateShiftHours(): int
    {
        return $this->duration;
    }

    public function calculateWorkingHours(): int
    {
        return $this->duration - ($this->break_duration / 60);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isNightShift(): bool
    {
        return $this->is_night_shift;
    }

    public function hasBreak(): bool
    {
        return $this->break_duration > 0;
    }

    public function getOvertimeStartTime(): Carbon
    {
        $overtimeHours = $this->overtime_threshold;
        return $this->start_time->copy()->addHours($overtimeHours);
    }

    public function isWithinGracePeriod(Carbon $time): bool
    {
        $graceStart = $this->start_time->copy()->subMinutes($this->grace_period);
        $graceEnd = $this->start_time->copy()->addMinutes($this->grace_period);
        
        return $time->between($graceStart, $graceEnd);
    }

    public function isLate(Carbon $time): bool
    {
        return $time->gt($this->start_time->copy()->addMinutes($this->grace_period));
    }

    public function isEarly(Carbon $time): bool
    {
        return $time->lt($this->start_time->copy()->subMinutes($this->grace_period));
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNightShifts($query)
    {
        return $query->where('is_night_shift', true);
    }

    public function scopeDayShifts($query)
    {
        return $query->where('is_night_shift', false);
    }

    public function scopeByDuration($query, $minHours, $maxHours = null)
    {
        if ($maxHours) {
            return $query->whereRaw('TIMESTAMPDIFF(HOUR, start_time, end_time) BETWEEN ? AND ?', [$minHours, $maxHours]);
        }
        return $query->whereRaw('TIMESTAMPDIFF(HOUR, start_time, end_time) >= ?', [$minHours]);
    }

    // Static methods
    public static function getActiveShiftsCount(): int
    {
        return self::where('is_active', true)->count();
    }

    public static function getNightShiftsCount(): int
    {
        return self::where('is_night_shift', true)->where('is_active', true)->count();
    }

    public static function getDayShiftsCount(): int
    {
        return self::where('is_night_shift', false)->where('is_active', true)->count();
    }

    public static function getShiftDurationDistribution(): array
    {
        $shifts = self::active()->get();
        
        $distribution = [
            '4-6 hours' => 0,
            '6-8 hours' => 0,
            '8-10 hours' => 0,
            '10+ hours' => 0,
        ];

        foreach ($shifts as $shift) {
            $duration = $shift->duration;
            
            if ($duration >= 4 && $duration < 6) {
                $distribution['4-6 hours']++;
            } elseif ($duration >= 6 && $duration < 8) {
                $distribution['6-8 hours']++;
            } elseif ($duration >= 8 && $duration < 10) {
                $distribution['8-10 hours']++;
            } else {
                $distribution['10+ hours']++;
            }
        }

        return $distribution;
    }

    public static function getAverageShiftDuration(): float
    {
        return self::active()->get()->avg('duration') ?? 0;
    }
} 