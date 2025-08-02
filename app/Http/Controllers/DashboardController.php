<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Organization;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AuditLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $data = [];

        if ($user->hasRole('Super Admin')) {
            $data = $this->getSuperAdminData();
        } elseif ($user->hasRole('HR')) {
            $data = $this->getHRData($user);
        } elseif ($user->hasRole('Manager')) {
            $data = $this->getManagerData($user);
        } elseif ($user->hasRole('Employee')) {
            $data = $this->getEmployeeData($user);
        }

        return view('dashboard', compact('data'));
    }

    private function getSuperAdminData()
    {
        $data = [];
        
        // Basic counts
        $data['organizations_count'] = Organization::count();
        $data['users_count'] = User::count();
        $data['latest_users'] = User::with('organization')->latest()->take(5)->get();
        
        // Monthly user growth
        $data['monthly_user_growth'] = User::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        // Recent activities
        $data['recent_activities'] = AuditLog::with('user')
            ->latest()
            ->take(10)
            ->get();
        
        // Organization statistics
        $data['organizations'] = Organization::withCount('users')->get();
        
        // Today's system activity
        $data['today_activities'] = AuditLog::whereDate('created_at', today())->count();
        
        return $data;
    }

    private function getHRData($user)
    {
        $data = [];
        
        // Employee counts
        $data['employees_count'] = User::where('organization_id', $user->organization_id)->count();
        $data['today_present'] = Attendance::where('date', today())
            ->whereHas('user', function ($query) use ($user) {
                $query->where('organization_id', $user->organization_id);
            })
            ->where('status', '!=', 'Incomplete')
            ->count();
        $data['today_absent'] = $data['employees_count'] - $data['today_present'];
        
        // Monthly attendance trends
        $data['monthly_attendance'] = Attendance::whereHas('user', function ($query) use ($user) {
                $query->where('organization_id', $user->organization_id);
            })
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->get();
        
        // Recent attendance issues
        $data['recent_issues'] = Attendance::whereHas('user', function ($query) use ($user) {
                $query->where('organization_id', $user->organization_id);
            })
            ->where('status', 'Incomplete')
            ->where('date', '>=', now()->subDays(7))
            ->with('user')
            ->get();
        
        // Employee list for quick access
        $data['employees'] = User::where('organization_id', $user->organization_id)
            ->with('roles')
            ->get();
        
        // Weekly attendance summary
        $data['weekly_summary'] = $this->getWeeklyAttendanceSummary($user->organization_id);
        
        return $data;
    }

    private function getManagerData($user)
    {
        $data = [];
        
        // Team information
        $team_members = User::where('organization_id', $user->organization_id)->get();
        $data['team_count'] = $team_members->count();
        $data['team_members'] = $team_members;
        
        // Today's team attendance
        $data['team_attendance_today'] = Attendance::whereIn('user_id', $team_members->pluck('id'))
            ->where('date', today())
            ->with('user')
            ->get();
        
        // Team performance this month
        $data['team_performance'] = Attendance::whereIn('user_id', $team_members->pluck('id'))
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->get();
        
        // Team members with attendance issues
        $data['team_issues'] = Attendance::whereIn('user_id', $team_members->pluck('id'))
            ->where('status', 'Incomplete')
            ->where('date', '>=', now()->subDays(7))
            ->with('user')
            ->get();
        
        // Weekly team summary
        $data['weekly_team_summary'] = $this->getWeeklyAttendanceSummary($user->organization_id, $team_members->pluck('id'));
        
        return $data;
    }

    private function getEmployeeData($user)
    {
        $data = [];
        
        // Monthly attendance
        $data['monthly_attendance'] = Attendance::where('user_id', $user->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->get();
        
        // Total worked hours
        $data['total_worked_hours'] = $data['monthly_attendance']->sum('worked_minutes') / 60;
        
        // Attendance statistics
        $data['attendance_stats'] = [
            'present_days' => $data['monthly_attendance']->where('status', '!=', 'Incomplete')->count(),
            'absent_days' => $data['monthly_attendance']->where('status', 'Incomplete')->count(),
            'full_days' => $data['monthly_attendance']->where('status', 'Full Day')->count(),
            'half_days' => $data['monthly_attendance']->where('status', 'Half Day')->count(),
        ];
        
        // Recent attendance (last 7 days)
        $data['recent_attendance'] = Attendance::where('user_id', $user->id)
            ->where('date', '>=', now()->subDays(7))
            ->orderBy('date', 'desc')
            ->get();
        
        // Today's attendance
        $data['today_attendance'] = Attendance::where('user_id', $user->id)
            ->where('date', today())
            ->first();
        
        // Weekly summary
        $data['weekly_summary'] = $this->getWeeklyAttendanceSummary(null, [$user->id]);
        
        // Attendance trends
        $data['attendance_trends'] = Attendance::where('user_id', $user->id)
            ->where('date', '>=', now()->subDays(30))
            ->orderBy('date')
            ->get();
        
        return $data;
    }

    private function getWeeklyAttendanceSummary($organizationId = null, $userIds = null)
    {
        $query = Attendance::where('date', '>=', now()->startOfWeek())
            ->where('date', '<=', now()->endOfWeek());
        
        if ($organizationId) {
            $query->whereHas('user', function ($q) use ($organizationId) {
                $q->where('organization_id', $organizationId);
            });
        }
        
        if ($userIds) {
            $query->whereIn('user_id', $userIds);
        }
        
        return $query->selectRaw('DATE(date) as date, COUNT(*) as total, 
                SUM(CASE WHEN status != "Incomplete" THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = "Incomplete" THEN 1 ELSE 0 END) as absent')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}
