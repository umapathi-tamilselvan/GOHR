<?php

namespace App\Listeners;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogoutSuccessful
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        if ($event->user->hasRole('Super Admin')) {
            return;
        }

        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('user_id', $event->user->id)
            ->where('date', $today)
            ->whereNull('check_out')
            ->first();

        if ($attendance) {
            $attendance->check_out = now();
            $checkInTime = Carbon::parse($attendance->check_in);
            
            $durationInMinutes = (int) $checkInTime->diffInMinutes($attendance->check_out, true);
            $durationInHours = $durationInMinutes / 60;

            $attendance->worked_minutes = $durationInMinutes;

            if ($durationInHours >= 8) {
                $attendance->status = 'Full Day';
            } elseif ($durationInHours >= 4) {
                $attendance->status = 'Half Day';
            } else {
                $attendance->status = 'Incomplete';
            }

            $attendance->save();
        }
    }
}
