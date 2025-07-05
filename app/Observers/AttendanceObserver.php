<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AttendanceObserver
{
    /**
     * Handle the Attendance "created" event.
     */
    public function created(Attendance $attendance): void
    {
        $this->logAction('created', $attendance);
    }

    /**
     * Handle the Attendance "updated" event.
     */
    public function updated(Attendance $attendance): void
    {
        if ($attendance->wasRecentlyCreated) {
            return;
        }
        $this->logAction('updated', $attendance);
    }

    /**
     * Handle the Attendance "deleted" event.
     */
    public function deleted(Attendance $attendance): void
    {
        $this->logAction('deleted', $attendance);
    }

    /**
     * Handle the Attendance "restored" event.
     */
    public function restored(Attendance $attendance): void
    {
        //
    }

    /**
     * Handle the Attendance "force deleted" event.
     */
    public function forceDeleted(Attendance $attendance): void
    {
        //
    }

    private function logAction(string $action, Attendance $attendance): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_id' => $attendance->id,
            'auditable_type' => Attendance::class,
            'old_values' => $action === 'updated' ? $attendance->getOriginal() : null,
            'new_values' => $action !== 'deleted' ? ($action === 'created' ? $attendance->getAttributes() : $attendance->getChanges()) : null,
        ]);
    }
}
