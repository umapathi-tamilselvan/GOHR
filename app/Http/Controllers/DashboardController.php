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
    public function index()
    {
        $user = Auth::user();
        $data = [];

        if ($user->hasRole('Super Admin')) {
            $data['total_organizations'] = Organization::count();
            $data['total_users'] = User::count();
        } elseif ($user->hasRole('HR') || $user->hasRole('Manager')) {
            $data['total_employees'] = User::where('organization_id', $user->organization_id)->count();
        } else {
            $data['monthly_attendance'] = Attendance::where('user_id', $user->id)
                ->whereMonth('date', Carbon::now()->month)
                ->count();
        }

        return view('dashboard', compact('data'));
    }
}
