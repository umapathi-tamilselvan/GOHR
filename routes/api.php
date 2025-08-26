<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\AuditLogController;
use App\Http\Controllers\API\LeaveController;
use App\Http\Controllers\API\LeaveTypeController;
use App\Http\Controllers\API\LeaveBalanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    
    // Profile management
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'changePassword']);
    
    // Dashboard data
    Route::get('/dashboard/super-admin', [DashboardController::class, 'superAdmin']);
    Route::get('/dashboard/hr', [DashboardController::class, 'hr']);
    Route::get('/dashboard/manager', [DashboardController::class, 'manager']);
    Route::get('/dashboard/employee', [DashboardController::class, 'employee']);
    
    // User management (restricted access)
    Route::middleware('role:super-admin|hr')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::get('/users/search', [UserController::class, 'search']);
    });
    
    // Attendance management
    Route::get('/attendance', [AttendanceController::class, 'currentUser']);
    Route::get('/attendance/list', [AttendanceController::class, 'index']);
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
    Route::get('/attendance/report', [AttendanceController::class, 'report']);
    
    // Manual attendance entry (restricted access)
    Route::middleware('role:super-admin|hr|manager')->group(function () {
        Route::post('/attendance/manual', [AttendanceController::class, 'storeManual']);
        Route::put('/attendance/{attendance}', [AttendanceController::class, 'update']);
        Route::delete('/attendance/{attendance}', [AttendanceController::class, 'destroy']);
    });
    
    // Audit log (restricted access)
    Route::middleware('role:super-admin|hr')->group(function () {
        Route::get('/audit-log', [AuditLogController::class, 'index']);
        Route::get('/audit-log/{auditLog}', [AuditLogController::class, 'show']);
    });
    
    // Leave management
    Route::get('/leaves', [LeaveController::class, 'index']);
    Route::post('/leaves', [LeaveController::class, 'store']);
    Route::get('/leaves/{leave}', [LeaveController::class, 'show']);
    Route::put('/leaves/{leave}', [LeaveController::class, 'update']);
    Route::delete('/leaves/{leave}', [LeaveController::class, 'destroy']);
    Route::patch('/leaves/{leave}/approve', [LeaveController::class, 'approve']);
    Route::patch('/leaves/{leave}/reject', [LeaveController::class, 'reject']);
    Route::get('/leaves-calendar', [LeaveController::class, 'calendar']);
    Route::get('/leaves-report', [LeaveController::class, 'report']);
    
    // Leave types (restricted access)
    Route::middleware('role:super-admin|hr')->group(function () {
        Route::get('/leave-types', [LeaveTypeController::class, 'index']);
        Route::post('/leave-types', [LeaveTypeController::class, 'store']);
        Route::get('/leave-types/{leaveType}', [LeaveTypeController::class, 'show']);
        Route::put('/leave-types/{leaveType}', [LeaveTypeController::class, 'update']);
        Route::delete('/leave-types/{leaveType}', [LeaveTypeController::class, 'destroy']);
    });
    
    // Leave balances (restricted access)
    Route::middleware('role:super-admin|hr')->group(function () {
        Route::get('/leave-balances', [LeaveBalanceController::class, 'index']);
        Route::post('/leave-balances', [LeaveBalanceController::class, 'store']);
        Route::get('/leave-balances/{leaveBalance}', [LeaveBalanceController::class, 'show']);
        Route::put('/leave-balances/{leaveBalance}', [LeaveBalanceController::class, 'update']);
        Route::delete('/leave-balances/{leaveBalance}', [LeaveBalanceController::class, 'destroy']);
        Route::post('/leave-balances/initialize-year', [LeaveBalanceController::class, 'initializeYear']);
    });
    
    // Leave balance summary (accessible to all authenticated users)
    Route::get('/leave-balances/summary', [LeaveBalanceController::class, 'userSummary']);
    
    // Organizations and Departments (accessible to all authenticated users)
    Route::get('/organizations', [UserController::class, 'organizations']);
    Route::get('/departments', [UserController::class, 'departments']);
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
}); 