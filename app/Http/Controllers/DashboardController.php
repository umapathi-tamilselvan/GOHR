<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Organization;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $data = [];

        if ($user->hasRole('Super Admin')) {
            $data['organizations_count'] = Organization::count();
            $data['users_count'] = User::count();
            $data['latest_users'] = User::latest()->take(5)->get();
        } elseif ($user->hasRole('HR')) {
            $data['employees_count'] = User::where('organization_id', $user->organization_id)->count();
            $data['today_present'] = Attendance::where('date', today())
                ->whereHas('user', function ($query) use ($user) {
                    $query->where('organization_id', $user->organization_id);
                })
                ->where('status', '!=', 'Incomplete')
                ->count();
            $data['today_absent'] = $data['employees_count'] - $data['today_present'];
        } elseif ($user->hasRole('Manager')) {
            // Assuming managers manage users in the same organization
            $team_members_id = User::where('organization_id', $user->organization_id)->pluck('id');
            $data['team_count'] = $team_members_id->count();
            $data['team_attendance_today'] = Attendance::whereIn('user_id', $team_members_id)
                ->where('date', today())
                ->get();
        } elseif ($user->hasRole('Employee')) {
            $data['monthly_attendance'] = Attendance::where('user_id', $user->id)
                ->whereMonth('date', now()->month)
                ->get();
            $data['total_worked_hours'] = $data['monthly_attendance']->sum('worked_minutes') / 60;
        }

        return view('dashboard', compact('data'));
    }
}
