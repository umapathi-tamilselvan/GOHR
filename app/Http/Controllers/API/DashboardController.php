<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Super Admin Dashboard Data
     */
    public function superAdmin(Request $request)
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_organizations' => \App\Models\Organization::count(),
                'total_attendance_records' => Attendance::count(),
                'active_users_today' => Attendance::where('date', today())->distinct('user_id')->count()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Super admin dashboard data retrieved successfully',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * HR Dashboard Data
     */
    public function hr(Request $request)
    {
        try {
            $user = $request->user();
            $organizationId = $user->organization_id;

            $stats = [
                'total_employees' => User::where('organization_id', $organizationId)->count(),
                'present_today' => Attendance::where('organization_id', $organizationId)
                                            ->where('date', today())
                                            ->whereNotNull('check_in')
                                            ->count(),
                'absent_today' => User::where('organization_id', $organizationId)
                                    ->whereDoesntHave('attendances', function($q) {
                                        $q->where('date', today());
                                    })
                                    ->count()
            ];

            return response()->json([
                'success' => true,
                'message' => 'HR dashboard data retrieved successfully',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manager Dashboard Data
     */
    public function manager(Request $request)
    {
        try {
            $user = $request->user();
            $organizationId = $user->organization_id;

            $stats = [
                'team_members' => User::where('organization_id', $organizationId)
                                    ->where('id', '!=', $user->id)
                                    ->count(),
                'team_present_today' => Attendance::where('organization_id', $organizationId)
                                                ->where('date', today())
                                                ->whereNotNull('check_in')
                                                ->where('user_id', '!=', $user->id)
                                                ->count()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Manager dashboard data retrieved successfully',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Employee Dashboard Data
     */
    public function employee(Request $request)
    {
        try {
            $user = $request->user();
            $today = today();

            $stats = [
                'today_attendance' => Attendance::where('user_id', $user->id)
                                              ->where('date', $today)
                                              ->first(),
                'this_week_hours' => $this->calculateWeeklyHours($user->id),
                'monthly_attendance' => $this->calculateMonthlyAttendance($user->id)
            ];

            return response()->json([
                'success' => true,
                'message' => 'Employee dashboard data retrieved successfully',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate weekly hours for a user
     */
    private function calculateWeeklyHours($userId)
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $attendances = Attendance::where('user_id', $userId)
                                ->whereBetween('date', [$startOfWeek, $endOfWeek])
                                ->whereNotNull('check_in')
                                ->whereNotNull('check_out')
                                ->get();

        $totalHours = 0;
        foreach ($attendances as $attendance) {
            $start = \Carbon\Carbon::parse($attendance->check_in);
            $end = \Carbon\Carbon::parse($attendance->check_out);
            $totalHours += $end->diffInHours($start);
        }

        return $totalHours;
    }

    /**
     * Calculate monthly attendance for a user
     */
    private function calculateMonthlyAttendance($userId)
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $totalDays = $startOfMonth->diffInDays($endOfMonth) + 1;
        $presentDays = Attendance::where('user_id', $userId)
                                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                                ->whereNotNull('check_in')
                                ->count();

        return [
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $totalDays - $presentDays,
            'attendance_rate' => round(($presentDays / $totalDays) * 100, 2)
        ];
    }
} 