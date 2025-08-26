<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs
     */
    public function index(Request $request)
    {
        try {
            $query = AuditLog::with(['user', 'user.roles']);

            // Filter by user
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Filter by action
            if ($request->has('action')) {
                $query->where('action', $request->action);
            }

            // Filter by date range
            if ($request->has('start_date')) {
                $query->where('created_at', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->where('created_at', '<=', $request->end_date);
            }

            $auditLogs = $query->orderBy('created_at', 'desc')
                              ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'message' => 'Audit logs retrieved successfully',
                'data' => $auditLogs->items(),
                'meta' => [
                    'current_page' => $auditLogs->currentPage(),
                    'per_page' => $auditLogs->perPage(),
                    'total' => $auditLogs->total(),
                    'last_page' => $auditLogs->lastPage()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve audit logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified audit log
     */
    public function show(AuditLog $auditLog)
    {
        try {
            $auditLog->load(['user', 'user.roles']);

            return response()->json([
                'success' => true,
                'message' => 'Audit log retrieved successfully',
                'data' => $auditLog
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve audit log: ' . $e->getMessage()
            ], 500);
        }
    }
} 