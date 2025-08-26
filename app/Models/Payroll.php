<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Payroll extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'basic_salary',
        'gross_salary',
        'net_salary',
        'tds_amount',
        'pf_amount',
        'esi_amount',
        'professional_tax',
        'total_earnings',
        'total_deductions',
        'status',
        'payment_date',
        'payment_reference',
        'remarks',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'basic_salary' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'tds_amount' => 'decimal:2',
        'pf_amount' => 'decimal:2',
        'esi_amount' => 'decimal:2',
        'professional_tax' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected $dates = [
        'payment_date',
        'approved_at',
    ];

    // Relationships
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function salaryComponents(): HasMany
    {
        return $this->hasMany(SalaryComponent::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(SalaryDeduction::class);
    }

    // Accessors
    public function getPeriodAttribute(): string
    {
        $monthName = Carbon::createFromDate($this->year, $this->month, 1)->format('F');
        return "{$monthName} {$this->year}";
    }

    public function getFormattedBasicSalaryAttribute(): string
    {
        return '₹' . number_format($this->basic_salary, 2);
    }

    public function getFormattedGrossSalaryAttribute(): string
    {
        return '₹' . number_format($this->gross_salary, 2);
    }

    public function getFormattedNetSalaryAttribute(): string
    {
        return '₹' . number_format($this->net_salary, 2);
    }

    public function getFormattedTotalEarningsAttribute(): string
    {
        return '₹' . number_format($this->total_earnings, 2);
    }

    public function getFormattedTotalDeductionsAttribute(): string
    {
        return '₹' . number_format($this->total_deductions, 2);
    }

    public function getFormattedPaymentDateAttribute(): string
    {
        return $this->payment_date ? $this->payment_date->format('d M Y') : 'N/A';
    }

    public function getFormattedApprovedAtAttribute(): string
    {
        return $this->approved_at ? $this->approved_at->format('d M Y H:i') : 'N/A';
    }

    // Methods
    public function calculateNetSalary(): float
    {
        $this->net_salary = $this->gross_salary - $this->total_deductions;
        return $this->net_salary;
    }

    public function calculateTotalEarnings(): float
    {
        $this->total_earnings = $this->salaryComponents()->sum('amount');
        return $this->total_earnings;
    }

    public function calculateTotalDeductions(): float
    {
        $this->total_deductions = $this->deductions()->sum('amount');
        return $this->total_deductions;
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeProcessed(): bool
    {
        return $this->status === 'approved';
    }

    public function canBePaid(): bool
    {
        return $this->status === 'processed';
    }

    public function approve(User $user): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }

        $this->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return true;
    }

    public function process(): bool
    {
        if (!$this->canBeProcessed()) {
            return false;
        }

        $this->update(['status' => 'processed']);
        return true;
    }

    public function markAsPaid(string $paymentReference = null): bool
    {
        if (!$this->canBePaid()) {
            return false;
        }

        $this->update([
            'status' => 'paid',
            'payment_date' => now(),
            'payment_reference' => $paymentReference,
        ]);

        return true;
    }

    public function getSalaryBreakdown(): array
    {
        $components = $this->salaryComponents()->get();
        $deductions = $this->deductions()->get();

        return [
            'earnings' => $components->groupBy('component_type')->map(function ($group) {
                return [
                    'type' => $group->first()->component_type,
                    'amount' => $group->sum('amount'),
                    'description' => $group->first()->description,
                ];
            })->values(),
            'deductions' => $deductions->groupBy('deduction_type')->map(function ($group) {
                return [
                    'type' => $group->first()->deduction_type,
                    'amount' => $group->sum('amount'),
                    'description' => $group->first()->description,
                    'is_statutory' => $group->first()->is_statutory,
                ];
            })->values(),
        ];
    }

    // Scopes
    public function scopeByPeriod($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }

    public function scopeByMonth($query, $month)
    {
        return $query->where('month', $month);
    }

    // Static methods
    public static function getTotalPayrollByPeriod(int $month, int $year): float
    {
        return self::byPeriod($month, $year)->sum('net_salary');
    }

    public static function getTotalPayrollByYear(int $year): float
    {
        return self::byYear($year)->sum('net_salary');
    }

    public static function getPayrollStatusDistribution(): array
    {
        return self::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public static function getMonthlyPayrollTrend(int $year): array
    {
        $data = self::byYear($year)
            ->selectRaw('month, SUM(net_salary) as total_salary, COUNT(*) as employee_count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $trend = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthData = $data->where('month', $month)->first();
            $trend[$month] = [
                'total_salary' => $monthData ? $monthData->total_salary : 0,
                'employee_count' => $monthData ? $monthData->employee_count : 0,
                'average_salary' => $monthData && $monthData->employee_count > 0 
                    ? $monthData->total_salary / $monthData->employee_count 
                    : 0,
            ];
        }

        return $trend;
    }

    public static function getPendingApprovalsCount(): int
    {
        return self::where('status', 'draft')->count();
    }

    public static function getPendingProcessingCount(): int
    {
        return self::where('status', 'approved')->count();
    }

    public static function getPendingPaymentsCount(): int
    {
        return self::where('status', 'processed')->count();
    }
} 