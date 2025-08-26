<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Leave::with(['user', 'leaveType', 'approver']);

        // Filter by organization
        if ($user->hasRole('Super Admin')) {
            // Super admin can see all leaves
        } else {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id !== '') {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date !== '') {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date !== '') {
            $query->where('end_date', '<=', $request->end_date);
        }

        // Filter by leave type
        if ($request->has('leave_type_id') && $request->leave_type_id !== '') {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        // Role-based filtering
        if ($user->hasRole('Employee')) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('Manager')) {
            // Managers can see their team members' leaves
            $teamMemberIds = User::where('organization_id', $user->organization_id)
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'Employee');
                })->pluck('id');
            $query->whereIn('user_id', $teamMemberIds);
        }

        $leaves = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $leaves,
            'message' => 'Leaves retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $leaveType = LeaveType::findOrFail($request->leave_type_id);

        // Check if leave type belongs to user's organization
        if ($leaveType->organization_id !== $user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid leave type'
            ], 400);
        }

        // Calculate total days
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Check leave balance
        $currentYear = now()->year;
        $leaveBalance = LeaveBalance::where('user_id', $user->id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', $currentYear)
            ->first();

        if (!$leaveBalance || $leaveBalance->remaining_days < $totalDays) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient leave balance'
            ], 400);
        }

        // Check for overlapping leaves
        $overlappingLeave = Leave::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })->first();

        if ($overlappingLeave) {
            return response()->json([
                'success' => false,
                'message' => 'Leave request overlaps with existing approved or pending leave'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $leave = Leave::create([
                'user_id' => $user->id,
                'leave_type_id' => $request->leave_type_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_days' => $totalDays,
                'reason' => $request->reason,
                'status' => $leaveType->requires_approval ? 'pending' : 'approved',
                'approved_by' => $leaveType->requires_approval ? null : $user->id,
                'approved_at' => $leaveType->requires_approval ? null : now(),
            ]);

            // If auto-approved, update leave balance
            if (!$leaveType->requires_approval) {
                $leaveBalance->decrement('remaining_days', $totalDays);
                $leaveBalance->increment('used_days', $totalDays);
            }

            DB::commit();

            $leave->load(['user', 'leaveType', 'approver']);

            return response()->json([
                'success' => true,
                'data' => $leave,
                'message' => 'Leave request created successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create leave request'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Leave $leave): JsonResponse
    {
        $user = Auth::user();

        // Check access permissions
        if (!$this->canAccessLeave($user, $leave)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $leave->load(['user', 'leaveType', 'approver']);

        return response()->json([
            'success' => true,
            'data' => $leave,
            'message' => 'Leave details retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Leave $leave): JsonResponse
    {
        $user = Auth::user();

        // Check access permissions
        if (!$this->canAccessLeave($user, $leave)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        // Only pending leaves can be updated
        if ($leave->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending leaves can be updated'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'sometimes|required|date|after_or_equal:today',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'reason' => 'sometimes|required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $updateData = [];
            if ($request->has('start_date')) {
                $updateData['start_date'] = $request->start_date;
            }
            if ($request->has('end_date')) {
                $updateData['end_date'] = $request->end_date;
            }
            if ($request->has('reason')) {
                $updateData['reason'] = $request->reason;
            }

            // Recalculate total days if dates changed
            if (isset($updateData['start_date']) || isset($updateData['end_date'])) {
                $startDate = Carbon::parse($updateData['start_date'] ?? $leave->start_date);
                $endDate = Carbon::parse($updateData['end_date'] ?? $leave->end_date);
                $updateData['total_days'] = $startDate->diffInDays($endDate) + 1;
            }

            $leave->update($updateData);

            DB::commit();

            $leave->load(['user', 'leaveType', 'approver']);

            return response()->json([
                'success' => true,
                'data' => $leave,
                'message' => 'Leave request updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update leave request'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Leave $leave): JsonResponse
    {
        $user = Auth::user();

        // Check access permissions
        if (!$this->canAccessLeave($user, $leave)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        // Only pending leaves can be cancelled
        if ($leave->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending leaves can be cancelled'
            ], 400);
        }

        try {
            $leave->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Leave request cancelled successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel leave request'
            ], 500);
        }
    }

    /**
     * Approve a leave request.
     */
    public function approve(Leave $leave): JsonResponse
    {
        $user = Auth::user();

        // Check if user can approve leaves
        if (!$user->hasAnyRole(['HR', 'Manager', 'Super Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        // Check if leave belongs to user's organization
        if ($user->organization_id !== $leave->user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        // Managers can only approve their team members' leaves
        if ($user->hasRole('Manager') && !$this->isTeamMember($user, $leave->user)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        if ($leave->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Leave is not pending approval'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $leave->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            // Update leave balance
            $currentYear = now()->year;
            $leaveBalance = LeaveBalance::where('user_id', $leave->user_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('year', $currentYear)
                ->first();

            if ($leaveBalance) {
                $leaveBalance->decrement('remaining_days', $leave->total_days);
                $leaveBalance->increment('used_days', $leave->total_days);
            }

            DB::commit();

            $leave->load(['user', 'leaveType', 'approver']);

            return response()->json([
                'success' => true,
                'data' => $leave,
                'message' => 'Leave request approved successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve leave request'
            ], 500);
        }
    }

    /**
     * Reject a leave request.
     */
    public function reject(Request $request, Leave $leave): JsonResponse
    {
        $user = Auth::user();

        // Check if user can reject leaves
        if (!$user->hasAnyRole(['HR', 'Manager', 'Super Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        // Check if leave belongs to user's organization
        if ($user->organization_id !== $leave->user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        // Managers can only reject their team members' leaves
        if ($user->hasRole('Manager') && !$this->isTeamMember($user, $leave->user)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($leave->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Leave is not pending approval'
            ], 400);
        }

        try {
            $leave->update([
                'status' => 'rejected',
                'approved_by' => $user->id,
                'approved_at' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            $leave->load(['user', 'leaveType', 'approver']);

            return response()->json([
                'success' => true,
                'data' => $leave,
                'message' => 'Leave request rejected successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject leave request'
            ], 500);
        }
    }

    /**
     * Get leave calendar data.
     */
    public function calendar(Request $request): JsonResponse
    {
        $user = Auth::user();
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $query = Leave::with(['user', 'leaveType'])
            ->whereMonth('start_date', $month)
            ->whereYear('start_date', $year);

        // Filter by organization
        if (!$user->hasRole('Super Admin')) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            });
        }

        // Role-based filtering
        if ($user->hasRole('Employee')) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('Manager')) {
            $teamMemberIds = User::where('organization_id', $user->organization_id)
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'Employee');
                })->pluck('id');
            $query->whereIn('user_id', $teamMemberIds);
        }

        $leaves = $query->get();

        $calendarData = [];
        foreach ($leaves as $leave) {
            $startDate = Carbon::parse($leave->start_date);
            $endDate = Carbon::parse($leave->end_date);
            
            for ($date = $startDate; $date <= $endDate; $date->addDay()) {
                $dayKey = $date->format('Y-m-d');
                if (!isset($calendarData[$dayKey])) {
                    $calendarData[$dayKey] = [];
                }
                $calendarData[$dayKey][] = [
                    'id' => $leave->id,
                    'user_name' => $leave->user->name,
                    'leave_type' => $leave->leaveType->name,
                    'status' => $leave->status,
                    'color' => $leave->leaveType->color,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $calendarData,
            'message' => 'Leave calendar data retrieved successfully'
        ]);
    }

    /**
     * Get leave reports.
     */
    public function report(Request $request): JsonResponse
    {
        $user = Auth::user();
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $query = Leave::with(['user', 'leaveType'])
            ->whereBetween('start_date', [$startDate, $endDate]);

        // Filter by organization
        if (!$user->hasRole('Super Admin')) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            });
        }

        // Role-based filtering
        if ($user->hasRole('Employee')) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('Manager')) {
            $teamMemberIds = User::where('organization_id', $user->organization_id)
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'Employee');
                })->pluck('id');
            $query->whereIn('user_id', $teamMemberIds);
        }

        $leaves = $query->get();

        // Generate report data
        $reportData = [
            'total_leaves' => $leaves->count(),
            'approved_leaves' => $leaves->where('status', 'approved')->count(),
            'pending_leaves' => $leaves->where('status', 'pending')->count(),
            'rejected_leaves' => $leaves->where('status', 'rejected')->count(),
            'cancelled_leaves' => $leaves->where('status', 'cancelled')->count(),
            'total_days' => $leaves->sum('total_days'),
            'by_leave_type' => $leaves->groupBy('leave_type_id')->map(function ($typeLeaves) {
                return [
                    'leave_type' => $typeLeaves->first()->leaveType->name,
                    'count' => $typeLeaves->count(),
                    'total_days' => $typeLeaves->sum('total_days'),
                ];
            })->values(),
            'by_status' => $leaves->groupBy('status')->map(function ($statusLeaves) {
                return [
                    'status' => $statusLeaves->first()->status,
                    'count' => $statusLeaves->count(),
                    'total_days' => $statusLeaves->sum('total_days'),
                ];
            })->values(),
        ];

        return response()->json([
            'success' => true,
            'data' => $reportData,
            'message' => 'Leave report generated successfully'
        ]);
    }

    /**
     * Check if user can access the leave.
     */
    private function canAccessLeave(User $user, Leave $leave): bool
    {
        // Super admin can access all leaves
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Check if leave belongs to user's organization
        if ($user->organization_id !== $leave->user->organization_id) {
            return false;
        }

        // Users can always access their own leaves
        if ($user->id === $leave->user_id) {
            return true;
        }

        // HR can access all leaves in their organization
        if ($user->hasRole('HR')) {
            return true;
        }

        // Managers can access their team members' leaves
        if ($user->hasRole('Manager') && $this->isTeamMember($user, $leave->user)) {
            return true;
        }

        return false;
    }

    /**
     * Check if a user is a team member of the manager.
     */
    private function isTeamMember(User $manager, User $employee): bool
    {
        // For now, we'll consider all employees in the same organization as team members
        // This can be enhanced later with actual team structure
        return $manager->organization_id === $employee->organization_id && $employee->hasRole('Employee');
    }
} 