<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class EmployeeObserver
{
    /**
     * Handle the Employee "created" event.
     */
    public function created(Employee $employee): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'auditable_type' => Employee::class,
            'auditable_id' => $employee->id,
            'old_values' => null,
            'new_values' => $employee->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle the Employee "updated" event.
     */
    public function updated(Employee $employee): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'auditable_type' => Employee::class,
            'auditable_id' => $employee->id,
            'old_values' => $employee->getOriginal(),
            'new_values' => $employee->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle the Employee "deleted" event.
     */
    public function deleted(Employee $employee): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'auditable_type' => Employee::class,
            'auditable_id' => $employee->id,
            'old_values' => $employee->toArray(),
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle the Employee "restored" event.
     */
    public function restored(Employee $employee): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'restored',
            'auditable_type' => Employee::class,
            'auditable_id' => $employee->id,
            'old_values' => null,
            'new_values' => $employee->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle the Employee "force deleted" event.
     */
    public function forceDeleted(Employee $employee): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'force_deleted',
            'auditable_type' => Employee::class,
            'auditable_id' => $employee->id,
            'old_values' => $employee->toArray(),
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
} 