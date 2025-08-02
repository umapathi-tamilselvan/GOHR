<?php

namespace App\Observers;

use App\Models\Leave;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class LeaveObserver
{
    /**
     * Handle the Leave "created" event.
     */
    public function created(Leave $leave): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'model_type' => Leave::class,
            'model_id' => $leave->id,
            'old_values' => null,
            'new_values' => $leave->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle the Leave "updated" event.
     */
    public function updated(Leave $leave): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'updated',
            'model_type' => Leave::class,
            'model_id' => $leave->id,
            'old_values' => $leave->getOriginal(),
            'new_values' => $leave->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle the Leave "deleted" event.
     */
    public function deleted(Leave $leave): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'model_type' => Leave::class,
            'model_id' => $leave->id,
            'old_values' => $leave->toArray(),
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle the Leave "restored" event.
     */
    public function restored(Leave $leave): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'restored',
            'model_type' => Leave::class,
            'model_id' => $leave->id,
            'old_values' => null,
            'new_values' => $leave->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle the Leave "force deleted" event.
     */
    public function forceDeleted(Leave $leave): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'force_deleted',
            'model_type' => Leave::class,
            'model_id' => $leave->id,
            'old_values' => $leave->toArray(),
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
