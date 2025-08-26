<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $query = LeaveType::query();

        // Filter by organization
        if (!$user->hasRole('Super Admin')) {
            $query->where('organization_id', $user->organization_id);
        }

        // Search by name
        if ($request->has('search') && $request->search !== '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $leaveTypes = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $leaveTypes,
            'message' => 'Leave types retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Check if user can create leave types
        if (!Auth::user()->hasAnyRole(['HR', 'Super Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'default_days' => 'required|integer|min:0|max:365',
            'color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'requires_approval' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        // Check if leave type name already exists in organization
        $existingLeaveType = LeaveType::where('name', $request->name)
            ->where('organization_id', $user->organization_id)
            ->first();

        if ($existingLeaveType) {
            return response()->json([
                'success' => false,
                'message' => 'Leave type with this name already exists'
            ], 400);
        }

        try {
            $leaveType = LeaveType::create([
                'name' => $request->name,
                'description' => $request->description,
                'default_days' => $request->default_days,
                'color' => $request->color,
                'requires_approval' => $request->get('requires_approval', true),
                'organization_id' => $user->organization_id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $leaveType,
                'message' => 'Leave type created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create leave type'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveType $leaveType): JsonResponse
    {
        $user = Auth::user();

        // Check if user can access this leave type
        if (!$user->hasRole('Super Admin') && $leaveType->organization_id !== $user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $leaveType,
            'message' => 'Leave type retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveType $leaveType): JsonResponse
    {
        // Check if user can update leave types
        if (!Auth::user()->hasAnyRole(['HR', 'Super Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $user = Auth::user();

        // Check if user can access this leave type
        if (!$user->hasRole('Super Admin') && $leaveType->organization_id !== $user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string|max:500',
            'default_days' => 'sometimes|required|integer|min:0|max:365',
            'color' => 'sometimes|required|string|regex:/^#[0-9A-F]{6}$/i',
            'requires_approval' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if leave type name already exists in organization (excluding current one)
        if ($request->has('name') && $request->name !== $leaveType->name) {
            $existingLeaveType = LeaveType::where('name', $request->name)
                ->where('organization_id', $user->organization_id)
                ->where('id', '!=', $leaveType->id)
                ->first();

            if ($existingLeaveType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave type with this name already exists'
                ], 400);
            }
        }

        try {
            $leaveType->update($request->only([
                'name', 'description', 'default_days', 'color', 'requires_approval'
            ]));

            return response()->json([
                'success' => true,
                'data' => $leaveType,
                'message' => 'Leave type updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update leave type'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveType $leaveType): JsonResponse
    {
        // Check if user can delete leave types
        if (!Auth::user()->hasAnyRole(['HR', 'Super Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $user = Auth::user();

        // Check if user can access this leave type
        if (!$user->hasRole('Super Admin') && $leaveType->organization_id !== $user->organization_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        // Check if leave type is being used
        if ($leaveType->leaves()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete leave type that has associated leaves'
            ], 400);
        }

        if ($leaveType->leaveBalances()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete leave type that has associated leave balances'
            ], 400);
        }

        try {
            $leaveType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Leave type deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete leave type'
            ], 500);
        }
    }
} 