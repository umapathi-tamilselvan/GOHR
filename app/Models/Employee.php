<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'user_id',
        'organization_id',
        'designation',
        'department',
        'location',
        'employment_type',
        'joining_date',
        'confirmation_date',
        'resignation_date',
        'last_working_day',
        'status',
        'manager_id',
        'basic_salary',
        'gross_salary',
        'bank_name',
        'bank_account_number',
        'ifsc_code',
        'pan_number',
        'aadhaar_number',
        'pf_number',
        'esi_number',
        'uan_number',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'confirmation_date' => 'date',
        'resignation_date' => 'date',
        'last_working_day' => 'date',
        'basic_salary' => 'decimal:2',
        'gross_salary' => 'decimal:2',
    ];

    protected $dates = [
        'joining_date',
        'confirmation_date',
        'resignation_date',
        'last_working_day',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function performanceReviews(): HasMany
    {
        return $this->hasMany(PerformanceReview::class);
    }

    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    public function onboardingTasks(): HasMany
    {
        return $this->hasMany(EmployeeOnboarding::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', $location);
    }

    public function scopeByEmploymentType($query, $type)
    {
        return $query->where('employment_type', $type);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->user->name ?? 'N/A';
    }

    public function getEmailAttribute(): string
    {
        return $this->user->email ?? 'N/A';
    }

    public function getCurrentShiftAttribute()
    {
        return $this->shiftAssignments()
            ->where('is_active', true)
            ->where('effective_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->with('shift')
            ->first();
    }

    public function getCurrentLeaveBalanceAttribute()
    {
        return $this->leaveBalances()
            ->where('leave_type_id', 1) // Assuming 1 is for annual leave
            ->first();
    }

    // Methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isManager(): bool
    {
        return $this->subordinates()->exists();
    }

    public function hasSubordinates(): bool
    {
        return $this->subordinates()->exists();
    }

    public function getSubordinatesCount(): int
    {
        return $this->subordinates()->count();
    }

    public function getTenureInYears(): int
    {
        return $this->joining_date->diffInYears(now());
    }

    public function getTenureInMonths(): int
    {
        return $this->joining_date->diffInMonths(now());
    }

    public function isOnProbation(): bool
    {
        return $this->confirmation_date === null || $this->confirmation_date > now();
    }

    public function getCurrentSalary(): float
    {
        return $this->gross_salary ?? 0;
    }

    public function getBasicSalary(): float
    {
        return $this->basic_salary ?? 0;
    }

    // Static methods
    public static function generateEmployeeId(): string
    {
        $prefix = 'EMP';
        $year = date('Y');
        $lastEmployee = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastEmployee ? (intval(substr($lastEmployee->employee_id, -4)) + 1) : 1;
        
        return sprintf('%s%s%04d', $prefix, $year, $sequence);
    }

    public static function getActiveEmployeesCount(): int
    {
        return self::where('status', 'active')->count();
    }

    public static function getEmployeesByDepartment(): array
    {
        return self::selectRaw('department, COUNT(*) as count')
            ->where('status', 'active')
            ->groupBy('department')
            ->pluck('count', 'department')
            ->toArray();
    }

    public static function getEmployeesByLocation(): array
    {
        return self::selectRaw('location, COUNT(*) as count')
            ->where('status', 'active')
            ->whereNotNull('location')
            ->groupBy('location')
            ->pluck('count', 'location')
            ->toArray();
    }
} 