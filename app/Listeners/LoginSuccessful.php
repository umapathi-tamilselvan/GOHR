<?php

namespace App\Listeners;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LoginSuccessful
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
    public function handle(Login $event): void
    {
        if ($event->user->hasRole('Super Admin')) {
            return;
        }

        $today = Carbon::today();
        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $event->user->id,
                'date' => $today->toDateString(),
            ],
            [
                'check_in' => now(),
                'status' => 'Incomplete',
                'worked_minutes' => 0,
            ]
        );
    }
}
