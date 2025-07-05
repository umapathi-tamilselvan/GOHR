<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\LeaveRequestController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('users', UserController::class);

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendances', [AttendanceController::class, 'list'])->name('attendances.list');
    Route::get('/attendances/manage', [AttendanceController::class, 'manage'])->name('attendances.manage');
    Route::post('/attendances/manage', [AttendanceController::class, 'storeManual'])->name('attendances.storeManual');
    Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');

    Route::middleware(['role:Super Admin'])->group(function () {
        Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
        Route::resource('roles', RoleController::class);
        Route::resource('organizations', OrganizationController::class);
        Route::resource('projects', ProjectController::class);
    });

    Route::resource('leave-requests', LeaveRequestController::class);
    Route::post('leave-requests/{leave_request}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve')->middleware('can:approve,leave_request');
});

require __DIR__.'/auth.php';
