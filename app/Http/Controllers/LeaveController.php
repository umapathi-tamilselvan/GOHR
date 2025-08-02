<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Leave::with(['user', 'leaveType', 'approvedBy']);

        // Filter by organization
        if ($user->hasRole('super-admin')) {
            // Super admin can see all leaves
        } else {
            $query->where('organization_id', $user->organization_id);
        }

        // Filter by user role
        if ($user->hasRole('employee')) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('manager')) {
            // Managers can see leaves from their team members
            $teamMemberIds = User::where('manager_id', $user->id)->pluck('id');
            $query->whereIn('user_id', $teamMemberIds->push($user->id));
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $leaves = $query->orderBy('created_at', 'desc')->paginate(15);
        $leaveTypes = LeaveType::active()->get();

        return view('leaves.index', compact('leaves', 'leaveTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $user = Auth::user();
        $leaveTypes = LeaveType::active()
            ->where('organization_id', $user->organization_id)
            ->get();

        // Get target user for leave application
        $targetUserId = $request->get('user_id', $user->id);
        $targetUser = User::findOrFail($targetUserId);

        // Check if current user can apply leave for target user
        if ($targetUserId != $user->id) {
            if ($user->hasRole('super-admin')) {
                // Super admin can apply leave for anyone
            } elseif ($user->hasRole('hr') && $targetUser->organization_id === $user->organization_id) {
                // HR can apply leave for users in their organization
            } elseif ($user->hasRole('manager')) {
                // Manager can apply leave for team members
                $teamMemberIds = User::where('manager_id', $user->id)->pluck('id');
                if (!$teamMemberIds->contains($targetUserId)) {
                    abort(403, 'You can only apply leave for your team members.');
                }
            } else {
                abort(403, 'You can only apply leave for yourself.');
            }
        }

        $leaveBalances = LeaveBalance::with('leaveType')
            ->forUser($targetUserId)
            ->forYear(date('Y'))
            ->get();

        // Get available users for selection (for HR/Manager/Super Admin)
        $availableUsers = collect();
        if ($user->hasRole('super-admin')) {
            $availableUsers = User::with('organization')->get();
        } elseif ($user->hasRole('hr')) {
            $availableUsers = User::where('organization_id', $user->organization_id)->get();
        } elseif ($user->hasRole('manager')) {
            $availableUsers = User::where('manager_id', $user->id)->orWhere('id', $user->id)->get();
        }

        return view('leaves.create', compact('leaveTypes', 'leaveBalances', 'targetUser', 'availableUsers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        $currentUser = Auth::user();
        $targetUser = User::findOrFail($request->user_id);
        $leaveType = LeaveType::findOrFail($request->leave_type_id);

        // Check if current user can apply leave for target user
        if ($request->user_id != $currentUser->id) {
            if ($currentUser->hasRole('super-admin')) {
                // Super admin can apply leave for anyone
            } elseif ($currentUser->hasRole('hr') && $targetUser->organization_id === $currentUser->organization_id) {
                // HR can apply leave for users in their organization
            } elseif ($currentUser->hasRole('manager')) {
                // Manager can apply leave for team members
                $teamMemberIds = User::where('manager_id', $currentUser->id)->pluck('id');
                if (!$teamMemberIds->contains($request->user_id)) {
                    return back()->withErrors(['user_id' => 'You can only apply leave for your team members.']);
                }
            } else {
                return back()->withErrors(['user_id' => 'You can only apply leave for yourself.']);
            }
        }

        // Calculate total days
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Check leave balance
        $leaveBalance = LeaveBalance::forUser($targetUser->id)
            ->forYear(date('Y'))
            ->where('leave_type_id', $request->leave_type_id)
            ->first();

        if (!$leaveBalance || !$leaveBalance->hasSufficientBalance($totalDays)) {
            return back()->withErrors(['leave_balance' => 'Insufficient leave balance for the selected employee.']);
        }

        // Check for overlapping leaves
        $overlappingLeave = Leave::where('user_id', $targetUser->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->first();

        if ($overlappingLeave) {
            return back()->withErrors(['date_range' => 'Leave request overlaps with existing leave for the selected employee.']);
        }

        DB::transaction(function () use ($request, $currentUser, $targetUser, $leaveType, $totalDays) {
            $leave = Leave::create([
                'user_id' => $targetUser->id,
                'leave_type_id' => $request->leave_type_id,
                'organization_id' => $targetUser->organization_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_days' => $totalDays,
                'status' => $leaveType->requires_approval ? 'pending' : 'approved',
                'reason' => $request->reason,
                'approved_by' => $leaveType->requires_approval ? null : $currentUser->id,
                'approved_at' => $leaveType->requires_approval ? null : now(),
            ]);

            // If auto-approved, update leave balance
            if (!$leaveType->requires_approval) {
                $leaveBalance = LeaveBalance::forUser($targetUser->id)
                    ->forYear(date('Y'))
                    ->where('leave_type_id', $request->leave_type_id)
                    ->first();
                
                if ($leaveBalance) {
                    $leaveBalance->useLeave($totalDays);
                }
            }
        });

        $successMessage = $request->user_id == $currentUser->id 
            ? 'Leave request submitted successfully.'
            : "Leave request submitted successfully for {$targetUser->name}.";

        return redirect()->route('leaves.index')
            ->with('success', $successMessage);
    }

    /**
     * Display the specified resource.
     */
    public function show(Leave $leave): View
    {
        $this->authorize('view', $leave);
        
        $leave->load(['user', 'leaveType', 'approvedBy', 'organization']);
        
        return view('leaves.show', compact('leave'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Leave $leave): View
    {
        $this->authorize('update', $leave);
        
        $user = Auth::user();
        $leaveTypes = LeaveType::active()
            ->where('organization_id', $user->organization_id)
            ->get();

        return view('leaves.edit', compact('leave', 'leaveTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Leave $leave): RedirectResponse
    {
        $this->authorize('update', $leave);

        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        $leaveType = LeaveType::findOrFail($request->leave_type_id);
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Check for overlapping leaves (excluding current leave)
        $overlappingLeave = Leave::where('user_id', $leave->user_id)
            ->where('id', '!=', $leave->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->first();

        if ($overlappingLeave) {
            return back()->withErrors(['date_range' => 'Leave request overlaps with existing leave.']);
        }

        $leave->update([
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
        ]);

        return redirect()->route('leaves.show', $leave)
            ->with('success', 'Leave request updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Leave $leave): RedirectResponse
    {
        $this->authorize('delete', $leave);

        if ($leave->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending leaves can be cancelled.']);
        }

        $leave->update(['status' => 'cancelled']);

        return redirect()->route('leaves.index')
            ->with('success', 'Leave request cancelled successfully.');
    }

    /**
     * Approve a leave request.
     */
    public function approve(Leave $leave): RedirectResponse
    {
        $this->authorize('approve', $leave);

        if ($leave->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending leaves can be approved.']);
        }

        DB::transaction(function () use ($leave) {
            $leave->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Update leave balance
            $leaveBalance = LeaveBalance::forUser($leave->user_id)
                ->forYear(date('Y'))
                ->where('leave_type_id', $leave->leave_type_id)
                ->first();

            if ($leaveBalance) {
                $leaveBalance->useLeave($leave->total_days);
            }
        });

        return back()->with('success', 'Leave request approved successfully.');
    }

    /**
     * Reject a leave request.
     */
    public function reject(Request $request, Leave $leave): RedirectResponse
    {
        $this->authorize('approve', $leave);

        $request->validate([
            'manager_comment' => 'required|string|max:500',
        ]);

        if ($leave->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending leaves can be rejected.']);
        }

        $leave->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'manager_comment' => $request->manager_comment,
        ]);

        return back()->with('success', 'Leave request rejected successfully.');
    }

    /**
     * Show leave calendar view.
     */
    public function calendar(): View
    {
        $user = Auth::user();
        $query = Leave::with(['user', 'leaveType'])
            ->whereIn('status', ['approved', 'pending']);

        if (!$user->hasRole('super-admin')) {
            $query->where('organization_id', $user->organization_id);
        }

        if ($user->hasRole('employee')) {
            $query->where('user_id', $user->id);
        }

        $leaves = $query->get();

        return view('leaves.calendar', compact('leaves'));
    }

    /**
     * Show leave reports.
     */
    public function report(Request $request): View
    {
        $user = Auth::user();
        $query = Leave::with(['user', 'leaveType', 'approvedBy']);

        if (!$user->hasRole('super-admin')) {
            $query->where('organization_id', $user->organization_id);
        }

        // Apply date filters
        $year = $request->get('year', date('Y'));
        $month = $request->get('month');

        $query->whereYear('start_date', $year);
        if ($month) {
            $query->whereMonth('start_date', $month);
        }

        $leaves = $query->get();

        // Calculate statistics
        $stats = [
            'total_leaves' => $leaves->count(),
            'approved_leaves' => $leaves->where('status', 'approved')->count(),
            'pending_leaves' => $leaves->where('status', 'pending')->count(),
            'rejected_leaves' => $leaves->where('status', 'rejected')->count(),
            'total_days' => $leaves->sum('total_days'),
            'approved_days' => $leaves->where('status', 'approved')->sum('total_days'),
        ];

        $leaveTypes = LeaveType::active()
            ->where('organization_id', $user->organization_id)
            ->get();

        return view('leaves.report', compact('leaves', 'stats', 'leaveTypes', 'year', 'month'));
    }
}
