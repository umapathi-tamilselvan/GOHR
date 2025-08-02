<?php

namespace App\Http\Controllers;

use App\Models\LeaveBalance;
use App\Models\User;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveBalanceController extends Controller
{
    /**
     * Display leave balances for the current user.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $year = $request->get('year', date('Y'));

        $query = LeaveBalance::with('leaveType')
            ->forYear($year);

        if ($user->hasRole('super-admin')) {
            // Super admin can see all balances
        } elseif ($user->hasRole('hr') || $user->hasRole('manager')) {
            $query->forOrganization($user->organization_id);
        } else {
            // Employee can only see their own balances
            $query->forUser($user->id);
        }

        $leaveBalances = $query->get();
        $years = range(date('Y') - 2, date('Y') + 1);

        return view('leave-balances.index', compact('leaveBalances', 'year', 'years'));
    }

    /**
     * Show leave balances for a specific user (HR/Manager only).
     */
    public function show(Request $request, User $user): View
    {
        $this->authorize('viewLeaveBalance', $user);

        $year = $request->get('year', date('Y'));
        $leaveBalances = LeaveBalance::with('leaveType')
            ->forUser($user->id)
            ->forYear($year)
            ->get();

        $years = range(date('Y') - 2, date('Y') + 1);

        return view('leave-balances.show', compact('leaveBalances', 'user', 'year', 'years'));
    }

    /**
     * Initialize leave balances for all users in an organization.
     */
    public function initialize(Request $request): RedirectResponse
    {
        $this->authorize('initializeLeaveBalances');

        $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        $year = $request->year;
        $organizationId = $request->organization_id;

        $users = User::where('organization_id', $organizationId)->get();
        $leaveTypes = LeaveType::active()->where('organization_id', $organizationId)->get();

        DB::transaction(function () use ($users, $leaveTypes, $year) {
            foreach ($users as $user) {
                foreach ($leaveTypes as $leaveType) {
                    // Check if balance already exists
                    $existingBalance = LeaveBalance::forUser($user->id)
                        ->forYear($year)
                        ->where('leave_type_id', $leaveType->id)
                        ->first();

                    if (!$existingBalance) {
                        LeaveBalance::create([
                            'user_id' => $user->id,
                            'leave_type_id' => $leaveType->id,
                            'organization_id' => $user->organization_id,
                            'year' => $year,
                            'total_days' => $leaveType->default_days,
                            'used_days' => 0,
                            'remaining_days' => $leaveType->default_days,
                        ]);
                    }
                }
            }
        });

        return back()->with('success', "Leave balances initialized for year {$year}.");
    }

    /**
     * Update leave balance for a specific user.
     */
    public function update(Request $request, LeaveBalance $leaveBalance): RedirectResponse
    {
        $this->authorize('update', $leaveBalance);

        $request->validate([
            'total_days' => 'required|integer|min:0|max:365',
            'used_days' => 'required|integer|min:0|max:365',
        ]);

        $totalDays = $request->total_days;
        $usedDays = $request->used_days;

        if ($usedDays > $totalDays) {
            return back()->withErrors(['used_days' => 'Used days cannot exceed total days.']);
        }

        $leaveBalance->update([
            'total_days' => $totalDays,
            'used_days' => $usedDays,
            'remaining_days' => $totalDays - $usedDays,
        ]);

        return back()->with('success', 'Leave balance updated successfully.');
    }

    /**
     * Bulk update leave balances.
     */
    public function bulkUpdate(Request $request): RedirectResponse
    {
        $this->authorize('bulkUpdateLeaveBalances');

        $request->validate([
            'balances' => 'required|array',
            'balances.*.id' => 'required|exists:leave_balances,id',
            'balances.*.total_days' => 'required|integer|min:0|max:365',
            'balances.*.used_days' => 'required|integer|min:0|max:365',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->balances as $balanceData) {
                $leaveBalance = LeaveBalance::find($balanceData['id']);
                $totalDays = $balanceData['total_days'];
                $usedDays = $balanceData['used_days'];

                if ($usedDays <= $totalDays) {
                    $leaveBalance->update([
                        'total_days' => $totalDays,
                        'used_days' => $usedDays,
                        'remaining_days' => $totalDays - $usedDays,
                    ]);
                }
            }
        });

        return back()->with('success', 'Leave balances updated successfully.');
    }

    /**
     * Export leave balances report.
     */
    public function export(Request $request)
    {
        $this->authorize('exportLeaveBalances');

        $year = $request->get('year', date('Y'));
        $organizationId = $request->get('organization_id', Auth::user()->organization_id);

        $leaveBalances = LeaveBalance::with(['user', 'leaveType'])
            ->forYear($year)
            ->forOrganization($organizationId)
            ->get();

        // Generate CSV or Excel export
        $filename = "leave_balances_{$year}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($leaveBalances) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Employee Name',
                'Email',
                'Leave Type',
                'Total Days',
                'Used Days',
                'Remaining Days',
                'Usage %'
            ]);

            foreach ($leaveBalances as $balance) {
                fputcsv($file, [
                    $balance->user->name,
                    $balance->user->email,
                    $balance->leaveType->name,
                    $balance->total_days,
                    $balance->used_days,
                    $balance->remaining_days,
                    $balance->usage_percentage . '%'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get leave balances for a specific user (AJAX endpoint).
     */
    public function getUserBalances(User $user)
    {
        $this->authorize('viewLeaveBalance', $user);

        $leaveBalances = LeaveBalance::with('leaveType')
            ->forUser($user->id)
            ->forYear(date('Y'))
            ->get();

        $html = view('leave-balances.partials.balance-cards', compact('leaveBalances'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }
}
