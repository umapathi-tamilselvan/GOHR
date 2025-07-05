<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Organization;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [];

        if ($user->hasRole('Super Admin')) {
            $data['total_organizations'] = Organization::count();
            $data['total_users'] = User::count();
            $data['pending_leave_requests'] = LeaveRequest::where('status', 'pending')->count();
        } elseif ($user->hasRole('HR')) {
            $data['total_employees'] = User::where('organization_id', $user->organization_id)->count();
            // HR sees pending Manager leave requests
            $organizationUsers = User::where('organization_id', $user->organization_id)->pluck('id');
            $data['pending_leave_requests'] = LeaveRequest::whereIn('user_id', $organizationUsers)
                ->whereHas('user', function ($q) {
                    $q->role('Manager');
                })
                ->where('status', 'pending')
                ->count();
        } elseif ($user->hasRole('Manager')) {
            $data['total_employees'] = User::where('organization_id', $user->organization_id)->count();
            // Managers see pending Employee leave requests
            $organizationUsers = User::where('organization_id', $user->organization_id)->pluck('id');
            $data['pending_leave_requests'] = LeaveRequest::whereIn('user_id', $organizationUsers)
                ->whereHas('user', function ($q) {
                    $q->role('Employee');
                })
                ->where('status', 'pending')
                ->count();
        } else {
            $data['monthly_attendance'] = Attendance::where('user_id', $user->id)
                ->whereMonth('date', Carbon::now()->month)
                ->count();
            $data['my_leave_requests'] = LeaveRequest::where('user_id', $user->id)->count();
            $data['pending_my_requests'] = LeaveRequest::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count();
        }

        return view('dashboard', compact('data'));
    }
}
