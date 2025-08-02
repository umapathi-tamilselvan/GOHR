<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectTaskController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveBalanceController;
use App\Http\Controllers\EmployeeController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('users', UserController::class);

    // Attendance routes - full resource routes plus custom routes
    Route::resource('attendances', AttendanceController::class);
    Route::get('attendances-list', [AttendanceController::class, 'list'])->name('attendances.list');
    Route::get('attendances-report', [AttendanceController::class, 'report'])->name('attendances.report');
    Route::get('attendances-manage', [AttendanceController::class, 'manage'])->name('attendances.manage');
    Route::post('attendances-manual', [AttendanceController::class, 'storeManual'])->name('attendances.storeManual');

    Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');

    // Project Management routes
    Route::resource('projects', ProjectController::class);
    Route::post('projects/{project}/assign-manager', [ProjectController::class, 'assignManager'])->name('projects.assign-manager');
    Route::post('projects/{project}/add-member', [ProjectController::class, 'addMember'])->name('projects.add-member');
    Route::delete('projects/{project}/remove-member/{user}', [ProjectController::class, 'removeMember'])->name('projects.remove-member');
    Route::resource('projects.tasks', ProjectTaskController::class);
    Route::patch('projects/{project}/tasks/{task}/status', [ProjectTaskController::class, 'updateStatus'])->name('projects.tasks.update-status');
    Route::patch('projects/{project}/tasks/{task}/assign', [ProjectTaskController::class, 'assign'])->name('projects.tasks.assign');
    Route::get('projects-report', [ProjectController::class, 'report'])->name('projects.report');
    Route::get('projects/{project}/tasks-report', [ProjectTaskController::class, 'report'])->name('projects.tasks.report');

    // Leave Management routes
    Route::resource('leaves', LeaveController::class);
    Route::post('leaves/{leave}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
    Route::post('leaves/{leave}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');
    Route::get('leaves-calendar', [LeaveController::class, 'calendar'])->name('leaves.calendar');
    Route::get('leaves-report', [LeaveController::class, 'report'])->name('leaves.report');

    // Leave Type routes
    Route::resource('leave-types', LeaveTypeController::class);

    // Leave Balance routes
    Route::get('leave-balances', [LeaveBalanceController::class, 'index'])->name('leave-balances.index');
    Route::get('leave-balances/{user}', [LeaveBalanceController::class, 'show'])->name('leave-balances.show');
    Route::post('leave-balances/initialize', [LeaveBalanceController::class, 'initialize'])->name('leave-balances.initialize');
    Route::patch('leave-balances/{leaveBalance}', [LeaveBalanceController::class, 'update'])->name('leave-balances.update');
    Route::post('leave-balances/bulk-update', [LeaveBalanceController::class, 'bulkUpdate'])->name('leave-balances.bulk-update');
    Route::get('leave-balances/export', [LeaveBalanceController::class, 'export'])->name('leave-balances.export');
    
    // API routes for leave balances
    Route::get('api/leave-balances/{user}', [LeaveBalanceController::class, 'getUserBalances'])->name('api.leave-balances.user');

    // Employee Management routes
    Route::resource('employees', EmployeeController::class);
    Route::get('employees-directory', [EmployeeController::class, 'directory'])->name('employees.directory');
    Route::get('employees-onboarding', [EmployeeController::class, 'onboarding'])->name('employees.onboarding');
    Route::post('employees/{employee}/documents', [EmployeeController::class, 'uploadDocument'])->name('employees.upload-document');
    Route::delete('employees/{employee}/documents/{document}', [EmployeeController::class, 'deleteDocument'])->name('employees.delete-document');
    Route::get('employees-report', [EmployeeController::class, 'report'])->name('employees.report');
});

require __DIR__.'/auth.php';
