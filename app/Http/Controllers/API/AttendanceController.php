<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    /**
     * Get current user's attendance
     */
    public function currentUser(Request $request)
    {
        try {
            $user = $request->user();
            $today = now()->toDateString();

            $attendance = Attendance::where('user_id', $user->id)
                                  ->where('date', $today)
                                  ->first();

            return response()->json([
                'success' => true,
                'message' => 'Attendance retrieved successfully',
                'data' => $attendance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of attendance records
     */
    public function index(Request $request)
    {
        try {
            $query = Attendance::with(['user', 'user.roles', 'user.organization']);

            // Filter by user if specified
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Filter by date range
            if ($request->has('start_date')) {
                $query->where('date', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->where('date', '<=', $request->end_date);
            }

            $attendances = $query->orderBy('date', 'desc')
                                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'message' => 'Attendance records retrieved successfully',
                'data' => $attendances->items(),
                'meta' => [
                    'current_page' => $attendances->currentPage(),
                    'per_page' => $attendances->perPage(),
                    'total' => $attendances->total(),
                    'last_page' => $attendances->lastPage()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attendance records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check in
     */
    public function checkIn(Request $request)
    {
        try {
            $user = $request->user();
            $today = now()->toDateString();

            // Check if already checked in today
            $existing = Attendance::where('user_id', $user->id)
                                 ->where('date', $today)
                                 ->first();

            if ($existing && $existing->check_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already checked in today'
                ], 422);
            }

            if ($existing) {
                // Update existing record
                $existing->update([
                    'check_in' => now()
                ]);
                $attendance = $existing;
            } else {
                // Create new record
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => $today,
                    'check_in' => now(),
                    'organization_id' => $user->organization_id
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Check-in successful',
                'data' => $attendance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Check-in failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check out
     */
    public function checkOut(Request $request)
    {
        try {
            $user = $request->user();
            $today = now()->toDateString();

            $attendance = Attendance::where('user_id', $user->id)
                                  ->where('date', $today)
                                  ->first();

            if (!$attendance || !$attendance->check_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'No check-in record found for today'
                ], 422);
            }

            if ($attendance->check_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already checked out today'
                ], 422);
            }

            $attendance->update([
                'check_out' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Check-out successful',
                'data' => $attendance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Check-out failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store manual attendance entry
     */
    public function storeManual(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'date' => 'required|date',
                'check_in' => 'required|date_format:H:i',
                'check_out' => 'nullable|date_format:H:i|after:check_in',
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $targetUser = \App\Models\User::find($request->user_id);

            // Check permissions
            if ($user->hasRole('employee') && $user->id !== $targetUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient permissions'
                ], 403);
            }

            $attendance = Attendance::create([
                'user_id' => $request->user_id,
                'date' => $request->date,
                'check_in' => $request->date . ' ' . $request->check_in,
                'check_out' => $request->check_out ? $request->date . ' ' . $request->check_out : null,
                'notes' => $request->notes,
                'organization_id' => $targetUser->organization_id,
                'is_manual' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Manual attendance entry created successfully',
                'data' => $attendance
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create manual attendance entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update attendance record
     */
    public function update(Request $request, Attendance $attendance)
    {
        try {
            $validator = Validator::make($request->all(), [
                'check_in' => 'nullable|date_format:H:i',
                'check_out' => 'nullable|date_format:H:i|after:check_in',
                'notes' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();

            // Check permissions
            if ($user->hasRole('employee') && $user->id !== $attendance->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient permissions'
                ], 403);
            }

            $updates = [];
            if ($request->has('check_in')) {
                $updates['check_in'] = $attendance->date . ' ' . $request->check_in;
            }
            if ($request->has('check_out')) {
                $updates['check_out'] = $attendance->date . ' ' . $request->check_out;
            }
            if ($request->has('notes')) {
                $updates['notes'] = $request->notes;
            }

            $attendance->update($updates);

            return response()->json([
                'success' => true,
                'message' => 'Attendance record updated successfully',
                'data' => $attendance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attendance record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove attendance record
     */
    public function destroy(Attendance $attendance)
    {
        try {
            $user = $request->user();

            // Check permissions
            if ($user->hasRole('employee') && $user->id !== $attendance->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient permissions'
                ], 403);
            }

            $attendance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attendance record deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attendance record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance report
     */
    public function report(Request $request)
    {
        try {
            $query = Attendance::with(['user', 'user.roles', 'user.organization']);

            // Filter by date range
            if ($request->has('start_date')) {
                $query->where('date', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->where('date', '<=', $request->end_date);
            }

            // Filter by user
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            $attendances = $query->orderBy('date', 'desc')->get();

            // Calculate summary
            $summary = [
                'total_days' => $attendances->count(),
                'present_days' => $attendances->whereNotNull('check_in')->count(),
                'absent_days' => $attendances->whereNull('check_in')->count(),
                'total_hours' => 0
            ];

            foreach ($attendances as $attendance) {
                if ($attendance->check_in && $attendance->check_out) {
                    $start = \Carbon\Carbon::parse($attendance->check_in);
                    $end = \Carbon\Carbon::parse($attendance->check_out);
                    $summary['total_hours'] += $end->diffInHours($start);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Attendance report generated successfully',
                'data' => [
                    'records' => $attendances,
                    'summary' => $summary
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate attendance report: ' . $e->getMessage()
            ], 500);
        }
    }
} 