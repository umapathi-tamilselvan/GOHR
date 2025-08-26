<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LeaveBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $year = $request->get('year', now()->year);
        
        $query = LeaveBalance::with(['user', 'leaveType'])
            ->where('year', $year);

        // Filter by organization
        if (!$user->hasRole('Super Admin')) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id);
            });
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id !== '') {
            $query->where('user_id', $request->user_id);
        }

        // Filter by leave type
        if ($request->has('leave_type_id') && $request->leave_type_id !== '') {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        // Role-based filtering
        if ($user->hasRole('Employee')) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('Manager')) {
            // Managers can see their team members' balances
            $teamMemberIds = User::where('organization_id', $user->organization_id)
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'Employee');
                })->pluck('id');
            $query->whereIn('user_id', $teamMemberIds);
        }

        $leaveBalances = $query->orderBy('user_id')->orderBy('leave_type_id')->get();

        return response()->json([
            'success' => true,
            'data' => $leaveBalances,
            'message' => 'Leave balances retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Check if user can create leave balances
        if (!Auth::user()->hasAnyRole(['HR', 'Super Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'year' => 'required|integer|min:2020|max:2030',
            'total_days' => 'required|numeric|min:0|max:365',
            'used_days' => 'sometimes|numeric|min:0|max:365',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $targetUser = User::findOrFail($request->user_id);
        $leaveType = LeaveType::findOrFail($request->leave_type_id);

        // Check if user can manage this user's leave balance
        if (!$user->hasRole('Super Admin') && $targetUser->organization_id !== $user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        // Check if leave type belongs to user's organization
        if (!$user->hasRole('Super Admin') && $leaveType->organization_id !== $user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid leave type'
            ], 400);
        }

        // Check if leave balance already exists
        $existingBalance = LeaveBalance::where('user_id', $request->user_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', $request->year)
            ->first();

        if ($existingBalance) {
            return response()->json([
                'success' => false,
                'message' => 'Leave balance already exists for this user, leave type, and year'
            ], 400);
        }

        try {
            $usedDays = $request->get('used_days', 0);
            $remainingDays = $request->total_days - $usedDays;

            if ($remainingDays < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Used days cannot exceed total days'
                ], 400);
            }

            $leaveBalance = LeaveBalance::create([
                'user_id' => $request->user_id,
                'leave_type_id' => $request->leave_type_id,
                'year' => $request->year,
                'total_days' => $request->total_days,
                'used_days' => $usedDays,
                'remaining_days' => $remainingDays,
            ]);

            $leaveBalance->load(['user', 'leaveType']);

            return response()->json([
                'success' => true,
                'data' => $leaveBalance,
                'message' => 'Leave balance created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create leave balance'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveBalance $leaveBalance): JsonResponse
    {
        $user = Auth::user();

        // Check if user can access this leave balance
        if (!$user->hasRole('Super Admin') && $leaveBalance->user->organization_id !== $user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        // Role-based access control
        if ($user->hasRole('Employee') && $leaveBalance->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $leaveBalance->load(['user', 'leaveType']);

        return response()->json([
            'success' => true,
            'data' => $leaveBalance,
            'message' => 'Leave balance retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveBalance $leaveBalance): JsonResponse
    {
        // Check if user can update leave balances
        if (!Auth::user()->hasAnyRole(['HR', 'Super Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $user = Auth::user();

        // Check if user can access this leave balance
        if (!$user->hasRole('Super Admin') && $leaveBalance->user->organization_id !== $user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'total_days' => 'sometimes|required|numeric|min:0|max:365',
            'used_days' => 'sometimes|numeric|min:0|max:365',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = [];

            if ($request->has('total_days')) {
                $updateData['total_days'] = $request->total_days;
            }

            if ($request->has('used_days')) {
                $updateData['used_days'] = $request->used_days;
            }

            // Recalculate remaining days
            if (isset($updateData['total_days']) || isset($updateData['used_days'])) {
                $totalDays = $updateData['total_days'] ?? $leaveBalance->total_days;
                $usedDays = $updateData['used_days'] ?? $leaveBalance->used_days;
                $remainingDays = $totalDays - $usedDays;

                if ($remainingDays < 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Used days cannot exceed total days'
                    ], 400);
                }

                $updateData['remaining_days'] = $remainingDays;
            }

            $leaveBalance->update($updateData);

            $leaveBalance->load(['user', 'leaveType']);

            return response()->json([
                'success' => true,
                'data' => $leaveBalance,
                'message' => 'Leave balance updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update leave balance'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveBalance $leaveBalance): JsonResponse
    {
        // Check if user can delete leave balances
        if (!Auth::user()->hasAnyRole(['HR', 'Super Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $user = Auth::user();

        // Check if user can access this leave balance
        if (!$user->hasRole('Super Admin') && $leaveBalance->user->organization_id !== $user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        try {
            $leaveBalance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Leave balance deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete leave balance'
            ], 500);
        }
    }

    /**
     * Initialize leave balances for all users in an organization for a specific year.
     */
    public function initializeYear(Request $request): JsonResponse
    {
        // Check if user can initialize leave balances
        if (!Auth::user()->hasAnyRole(['HR', 'Super Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2020|max:2030',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $year = $request->year;

        try {
            DB::beginTransaction();

            // Get all leave types for the organization
            $leaveTypes = LeaveType::where('organization_id', $user->organization_id)->get();

            // Get all users in the organization
            $users = User::where('organization_id', $user->organization_id)->get();

            $createdCount = 0;
            $updatedCount = 0;

            foreach ($users as $targetUser) {
                foreach ($leaveTypes as $leaveType) {
                    $existingBalance = LeaveBalance::where('user_id', $targetUser->id)
                        ->where('leave_type_id', $leaveType->id)
                        ->where('year', $year)
                        ->first();

                    if (!$existingBalance) {
                        LeaveBalance::create([
                            'user_id' => $targetUser->id,
                            'leave_type_id' => $leaveType->id,
                            'year' => $year,
                            'total_days' => $leaveType->default_days,
                            'used_days' => 0,
                            'remaining_days' => $leaveType->default_days,
                        ]);
                        $createdCount++;
                    } else {
                        // Update existing balance with new default days
                        $existingBalance->update([
                            'total_days' => $leaveType->default_days,
                            'remaining_days' => $leaveType->default_days - $existingBalance->used_days,
                        ]);
                        $updatedCount++;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Leave balances initialized for year $year",
                'data' => [
                    'year' => $year,
                    'created_count' => $createdCount,
                    'updated_count' => $updatedCount,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize leave balances'
            ], 500);
        }
    }

    /**
     * Get leave balance summary for a user.
     */
    public function userSummary(Request $request): JsonResponse
    {
        $user = Auth::user();
        $targetUserId = $request->get('user_id', $user->id);
        $year = $request->get('year', now()->year);

        // Check if user can access this summary
        if ($user->id !== $targetUserId) {
            if (!$user->hasAnyRole(['HR', 'Manager', 'Super Admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $targetUser = User::findOrFail($targetUserId);
            if (!$user->hasRole('Super Admin') && $targetUser->organization_id !== $user->organization_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            // Managers can only see their team members' summaries
            if ($user->hasRole('Manager') && !$this->isTeamMember($user, $targetUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }
        }

        $leaveBalances = LeaveBalance::with(['leaveType'])
            ->where('user_id', $targetUserId)
            ->where('year', $year)
            ->get();

        $summary = [
            'user_id' => $targetUserId,
            'year' => $year,
            'total_leave_types' => $leaveBalances->count(),
            'total_available_days' => $leaveBalances->sum('remaining_days'),
            'total_used_days' => $leaveBalances->sum('used_days'),
            'leave_types' => $leaveBalances->map(function ($balance) {
                return [
                    'leave_type' => $balance->leaveType->name,
                    'total_days' => $balance->total_days,
                    'used_days' => $balance->used_days,
                    'remaining_days' => $balance->remaining_days,
                    'usage_percentage' => $balance->usage_percentage,
                    'usage_color' => $balance->usage_color,
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $summary,
            'message' => 'Leave balance summary retrieved successfully'
        ]);
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